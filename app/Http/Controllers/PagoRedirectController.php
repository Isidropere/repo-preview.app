<?php

namespace App\Http\Controllers;

use App\Models\Carrito;
use App\Models\CompraTrazabilidad;
use App\Models\Direcciones;
use App\Models\PagoCompra;
use App\Models\PagoItem;
use App\Models\User;
use App\Services\Payments\AzulProvider;
use App\Services\SolicitudServicioService;
use App\Services\ERPService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class PagoRedirectController extends Controller
{
    public function __construct(
        private AzulProvider $azulProvider,
        private SolicitudServicioService $solicitudService,
        private ERPService $erpService,
    ) {}

    /**
     * Inicia el flujo de pago reservando el stock y generando la redirección.
     */
    public function iniciarPago(Request $request)
    {
        $userId = auth()->id();
        Log::info('[Azul Redirect] Iniciando pago', ['user_id' => $userId]);

        // 1. Obtener carrito y sus items seleccionados
        $carrito = Carrito::with('itemsIntencionCompra.item.inventarios')
            ->where('id_user', $userId)
            ->first();

        if (!$carrito) {
            return redirect()->route('carrito.checkout_index')->with('error', 'No se encontró tu carrito.');
        }

        $itemsSeleccionados = $carrito->itemsIntencionCompra->where('es_seleccionado', true);
        if ($itemsSeleccionados->isEmpty()) {
            return redirect()->route('carrito.checkout_index')->with('error', 'No hay ítems seleccionados para pagar.');
        }

        // 2. Validar que no compre a sí mismo
        $itemsPropios = $itemsSeleccionados->filter(fn($i) => $i->item->id_user === $userId);
        if ($itemsPropios->isNotEmpty()) {
            return redirect()->route('carrito.checkout_index')->with('error', 'No puedes comprar tus propios artículos.');
        }

        $esServicio = $carrito->tipo === 'servicio';

        // 3. Validar dirección de envío (solo para productos)
        $direccion = null;
        if (!$esServicio) {
            $direccion = Direcciones::with('municipio')->where('id_user', $userId)
                ->where('es_predeterminada', 1)
                ->first();
            if (!$direccion) {
                return redirect()->route('carrito.checkout_index')
                    ->with('error', 'Debes registrar una dirección de envío antes de realizar un pago. Ve a tu perfil → Direcciones.');
            }
        }

        // 4. Verificar stock disponible
        foreach ($itemsSeleccionados as $item) {
            if ($item->item->estatus != 1) {
                return redirect()->route('carrito.checkout_index')
                    ->with('error', "El artículo \"{$item->item->item}\" ya no está disponible.");
            }

            if (!$esServicio) {
                $inventario = $item->item->inventarios;
                if (!$inventario || $inventario->cantidad < $item->cantidad) {
                    $disponible = $inventario->cantidad ?? 0;
                    return redirect()->route('carrito.checkout_index')
                        ->with('error', "Stock insuficiente para: {$item->item->item}. Disponible: {$disponible}");
                }
            }
        }

        // 5. Calcular monto total (subtotal de artículos)
        $montoTotal = $itemsSeleccionados->sum(
            fn($i) => ($i->item->valor * $i->cantidad) - $i->descuento
        );

        if ($montoTotal <= 0) {
            return redirect()->route('carrito.checkout_index')->with('error', 'El monto total debe ser mayor a cero.');
        }

        // Calcular costo de envío si aplica (solo para productos físicos)
        if (!$esServicio && $direccion) {
            $deliveryService = app(\App\Services\DeliveryService::class);
            $pueblo = $direccion->municipio->municipio ?? '';
            $resultadoDelivery = $deliveryService->calcular($pueblo, 'persona', $montoTotal);
            
            // Si el delivery dio error de tarifa no definida, redirigir informando al usuario
            if (!$resultadoDelivery['success'] && ($resultadoDelivery['error_code'] ?? null) === 'MISSING_DELIVERY_TARIFF') {
                return redirect()->route('carrito.checkout_index')
                    ->with('error', 'El sistema espera por una definición para el cálculo de Análisis de costos de envío. Por favor, espera a que el administrador defina el costo de envío.');
            }
            
            $costoEnvio = $resultadoDelivery['success'] ? (float) ($resultadoDelivery['costo_envio_total'] ?? 0) : 0;
            $montoTotal += $costoEnvio;
        }

        // 6. Transacción en base de datos para reservar stock y crear orden 'pendiente'
        try {
            PagoCompra::liberarOrdenesPendientes($carrito->id_carrito);

            $pagoCompra = DB::transaction(function () use ($itemsSeleccionados, $carrito, $montoTotal, $direccion, $userId) {
                // Evitar compras duplicadas simultáneas
                $carritoLocked = Carrito::where('id_carrito', $carrito->id_carrito)->lockForUpdate()->first();
                $yaExiste = PagoCompra::where('id_carrito', $carritoLocked->id_carrito)
                    ->whereIn('estatus', ['aprobado', 'pendiente'])
                    ->where('fecha', '>=', now()->subMinutes(2))
                    ->exists();

                if ($yaExiste) {
                    throw new \RuntimeException('duplicate_order');
                }

                // Crear PagoCompra en estado 'pendiente'
                $pagoCompra = PagoCompra::create([
                    'id_pago_compra'    => Str::uuid()->toString(),
                    'id_carrito'        => $carrito->id_carrito,
                    'estatus'           => 'pendiente',
                    'id_tarjeta'        => 'REDIRECT_AZUL', // Marcador para redirecciones
                    'autorizacion_pago' => null,
                    'id_proveedor_pago' => 1, // AZUL
                    'transaction_id'    => null,
                    'total'             => $montoTotal,
                    'cantidad_items'    => $itemsSeleccionados->count(),
                    'id_direccion'      => $direccion?->id_direccion,
                    'fecha'             => now(),
                ]);

                // Registrar trazabilidad inicial
                CompraTrazabilidad::create([
                    'id_pago_compra'  => $pagoCompra->id_pago_compra,
                    'estado_anterior' => null,
                    'estado_nuevo'    => 'pendiente',
                    'nota'            => 'Pago redireccionado iniciado. Dirección: ' . ($direccion?->id_direccion ?? 'N/A'),
                    'id_admin'        => null,
                ]);

                // Registrar PagoItems y reservar stock
                foreach ($itemsSeleccionados as $itemIntencion) {
                    $itemModel = $itemIntencion->item;
                    $inventario = $itemModel->inventarios;

                    // Obtener imagen del item
                    $imagen = $itemModel->imagenes()->first();
                    $imagenUrl = null;
                    if ($imagen) {
                        $ruta = trim($imagen->ruta ?? '', '/');
                        $directPath = $ruta . '/' . $imagen->nombre;
                        $imagenUrl = file_exists(public_path($directPath)) ? $directPath : (file_exists(public_path('storage/' . $directPath)) ? 'storage/' . $directPath : $directPath);
                    }

                    PagoItem::create([
                        'id_pago_compra'  => $pagoCompra->id_pago_compra,
                        'id_item'         => $itemModel->id_item,
                        'nombre_item'     => $itemModel->item,
                        'precio_unitario' => $itemModel->valor,
                        'cantidad'        => $itemIntencion->cantidad,
                        'descuento'       => $itemIntencion->descuento ?? 0,
                        'subtotal'        => ($itemModel->valor * $itemIntencion->cantidad) - ($itemIntencion->descuento ?? 0),
                        'imagen_url'      => $imagenUrl,
                    ]);

                    // Reservar inventario descontándolo temporalmente
                    if ($inventario) {
                        $inventario->cantidad -= $itemIntencion->cantidad;
                        $inventario->save();
                    }
                }

                return $pagoCompra;
            });

            // 7. Generar los campos y el AuthHash para AZUL
            $azulData = $this->azulProvider->generarCamposFormulario($montoTotal, $pagoCompra->id_pago_compra);

            // Registrar log local del request
            DB::table('logs_pagos')->insert([
                'id_user'          => $userId,
                'custom_order_id'  => $pagoCompra->id_pago_compra,
                'provider'         => 'azul_redirect',
                'transaction_type' => 'sale_init',
                'amount'           => $montoTotal,
                'request_payload'  => json_encode($azulData['fields']),
                'response_payload' => json_encode([]),
                'is_success'       => true,
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);

            // 8. Cargar la vista de redirección con el formulario auto-ejutable
            return view('pago.redirect', [
                'url'    => $azulData['url'],
                'fields' => $azulData['fields']
            ]);

        } catch (\RuntimeException $e) {
            if ($e->getMessage() === 'duplicate_order') {
                return redirect()->route('carrito.checkout_index')->with('error', 'Ya hay un pago en procesamiento para esta orden. Por favor espera.');
            }
            Log::error('[Azul Redirect] Excepción al iniciar pago', ['error' => $e->getMessage()]);
            if (isset($pagoCompra)) {
                $this->procesarCancelacionODeclinacion($pagoCompra, 'cancelado', 'Excepción en redirección web: ' . $e->getMessage());
            }
            return redirect()->route('carrito.checkout_index')->with('error', 'Error al procesar tu orden: ' . $e->getMessage());
        } catch (\Throwable $e) {
            Log::error('[Azul Redirect] Excepción al iniciar pago', ['error' => $e->getMessage()]);
            if (isset($pagoCompra)) {
                $this->procesarCancelacionODeclinacion($pagoCompra, 'cancelado', 'Excepción en redirección web: ' . $e->getMessage());
            }
            return redirect()->route('carrito.checkout_index')->with('error', 'Error al procesar tu orden: ' . $e->getMessage());
        }
    }

    /**
     * Inicia el flujo de redirección a AZUL para compras originadas en la app móvil.
     */
    public function iniciarPagoMovil(string $id_pago_compra)
    {
        Log::info('[Azul Redirect Móvil] Procesando inicio de pago móvil', ['id_pago_compra' => $id_pago_compra]);

        $pagoCompra = PagoCompra::where('id_pago_compra', $id_pago_compra)->first();

        if (!$pagoCompra) {
            Log::error('[Azul Redirect Móvil] Orden de compra no encontrada', ['id_pago_compra' => $id_pago_compra]);
            return response('Orden no encontrada.', 404);
        }

        if ($pagoCompra->estatus !== 'pendiente') {
            Log::warning('[Azul Redirect Móvil] La orden no está en estado pendiente', [
                'id_pago_compra' => $id_pago_compra,
                'estatus' => $pagoCompra->estatus
            ]);
            return response('La transacción ya fue procesada o no está pendiente.', 400);
        }

        try {
            // Generar los campos y el AuthHash para AZUL
            $azulData = $this->azulProvider->generarCamposFormulario($pagoCompra->total, $pagoCompra->id_pago_compra);

            // Registrar log local del request
            DB::table('logs_pagos')->insert([
                'id_user'          => $pagoCompra->carrito?->id_user ?? auth()->id() ?? 0,
                'custom_order_id'  => $pagoCompra->id_pago_compra,
                'provider'         => 'azul_redirect_movil',
                'transaction_type' => 'sale_init_movil',
                'amount'           => $pagoCompra->total,
                'request_payload'  => json_encode($azulData['fields']),
                'response_payload' => json_encode([]),
                'is_success'       => true,
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);

            return view('pago.redirect', [
                'url'    => $azulData['url'],
                'fields' => $azulData['fields']
            ]);
        } catch (\Throwable $e) {
            Log::error('[Azul Redirect Móvil] Error al iniciar pago móvil. Liberando stock.', ['error' => $e->getMessage()]);
            
            $this->procesarCancelacionODeclinacion($pagoCompra, 'cancelado', 'Fallo al iniciar redirección móvil: ' . $e->getMessage());

            return response('Error al iniciar la redirección de pago: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Callback invocado al aprobarse la transacción.
     */
    public function pagoAprobado(Request $request)
    {
        Log::info('[Azul Redirect] Aprobado callback recibido', $request->all());

        // 1. Validar firma del hash
        if (!$this->azulProvider->validarFirmaRespuesta($request->all())) {
            Log::critical('[Azul Redirect] Firma AuthHash inválida en pagoAprobado');
            return redirect()->route('carrito.checkout_index')->with('error', 'Firma de seguridad inválida de la pasarela de pagos.');
        }

        $orderNumber = $request->input('OrderNumber');
        $pagoCompra = PagoCompra::where('id_pago_compra', $orderNumber)->first();

        if (!$pagoCompra) {
            Log::critical('[Azul Redirect] Orden no encontrada en pagoAprobado', ['order_id' => $orderNumber]);
            return redirect()->route('carrito.checkout_index')->with('error', 'No se encontró la orden de compra asociada.');
        }

        // Si ya fue aprobada por IPN u otra redirección, omitir duplicado
        if ($pagoCompra->estatus === 'aprobado') {
            return redirect()->route('historial')->with('success', '¡Pago procesado correctamente! Tu pedido está en camino.');
        }

        // 2. Confirmar transacción de compra
        try {
            $this->procesarAprobacionLocal($pagoCompra, $request->all());
            return redirect()->route('historial')->with('success', '¡Pago procesado correctamente! Tu pedido está en camino.');
        } catch (\Throwable $e) {
            Log::error('[Azul Redirect] Error al asentar compra aprobada', ['error' => $e->getMessage()]);
            return redirect()->route('historial')->with('success', '¡Compra procesada! Tu pago fue acreditado, pero hubo un error menor al registrar tu recibo. Contacta soporte.');
        }
    }

    /**
     * Callback invocado al declinarse la transacción.
     */
    public function pagoDeclinado(Request $request)
    {
        Log::warning('[Azul Redirect] Declinado callback recibido', $request->all());

        // Validar firma
        if (!$this->azulProvider->validarFirmaRespuesta($request->all())) {
            Log::critical('[Azul Redirect] Firma AuthHash inválida en pagoDeclinado');
            return redirect()->route('carrito.checkout_index')->with('error', 'Firma de seguridad inválida.');
        }

        $orderNumber = $request->input('OrderNumber');
        $pagoCompra = PagoCompra::where('id_pago_compra', $orderNumber)->first();

        if ($pagoCompra && $pagoCompra->estatus === 'pendiente') {
            $this->procesarCancelacionODeclinacion($pagoCompra, 'declinado', $request->input('ErrorDescription') ?? 'Transacción declinada por el banco');
        }

        $errorMsg = $request->input('ErrorDescription') ?: ($request->input('ResponseMessage') ?: 'Transacción rechazada.');
        return redirect()->route('carrito.checkout_index')->with('error', 'Pago rechazado: ' . $errorMsg);
    }

    /**
     * Callback invocado al cancelarse el flujo por el usuario.
     */
    public function pagoCancelado(Request $request)
    {
        Log::info('[Azul Redirect] Cancelado callback recibido', $request->all());

        $orderNumber = $request->input('OrderNumber');
        $pagoCompra = PagoCompra::where('id_pago_compra', $orderNumber)->first();

        if ($pagoCompra && $pagoCompra->estatus === 'pendiente') {
            $this->procesarCancelacionODeclinacion($pagoCompra, 'cancelado', 'Cancelado por el usuario en la pasarela.');
        }

        return redirect()->route('carrito.checkout_index')->with('warning', 'Pago cancelado por el usuario.');
    }

    /**
     * Webhook asíncrono IPN (Instant Payment Notification).
     */
    public function ipnWebhook(Request $request)
    {
        Log::info('[Azul Redirect] IPN webhook recibido', $request->all());

        if (!$this->azulProvider->validarFirmaRespuesta($request->all())) {
            Log::critical('[Azul Redirect] Firma AuthHash inválida en IPN webhook');
            return response()->json(['error' => 'Firma inválida'], 400);
        }

        $orderNumber = $request->input('OrderNumber');
        $pagoCompra = PagoCompra::where('id_pago_compra', $orderNumber)->first();

        if (!$pagoCompra) {
            return response()->json(['error' => 'Orden no encontrada'], 404);
        }

        // Si ya está aprobada, responder OK
        if ($pagoCompra->estatus === 'aprobado') {
            return response()->json(['success' => true]);
        }

        // Validar si el resultado de AZUL es aprobado
        $isoCode = $request->input('IsoCode') ?? $request->input('ISOCode');
        if ($isoCode === '00') {
            try {
                $this->procesarAprobacionLocal($pagoCompra, $request->all());
                return response()->json(['success' => true]);
            } catch (\Throwable $e) {
                Log::error('[Azul Redirect] Error IPN asentando aprobación', ['error' => $e->getMessage()]);
                return response()->json(['error' => $e->getMessage()], 500);
            }
        } else {
            // De lo contrario, liberar stock y marcar declinado
            $this->procesarCancelacionODeclinacion($pagoCompra, 'declinado', $request->input('ErrorDescription') ?? 'Declinado vía IPN');
            return response()->json(['success' => true]);
        }
    }

    // -------------------------------------------------------------------------
    // Métodos Auxiliares de Negocio
    // -------------------------------------------------------------------------

    /**
     * Procesa la aprobación de la orden, limpia el carrito y asienta en base de datos.
     */
    private function procesarAprobacionLocal(PagoCompra $pagoCompra, array $azulParams)
    {
        DB::transaction(function () use ($pagoCompra, $azulParams) {
            // Actualizar datos de pago
            $pagoCompra->estatus = 'aprobado';
            $pagoCompra->autorizacion_pago = $azulParams['AuthorizationCode'] ?? null;
            $pagoCompra->transaction_id = $azulParams['AzulOrderId'] ?? null;
            $pagoCompra->save();

            // Trazabilidad
            CompraTrazabilidad::create([
                'id_pago_compra'  => $pagoCompra->id_pago_compra,
                'estado_anterior' => 'pendiente',
                'estado_nuevo'    => 'aprobado',
                'nota'            => 'Pago confirmado vía pasarela. Auth: ' . ($azulParams['AuthorizationCode'] ?? 'N/A'),
                'id_admin'        => null,
            ]);

            // Eliminar items seleccionados del carrito del usuario (ya que la compra finalizó con éxito)
            $carrito = Carrito::find($pagoCompra->id_carrito);
            if ($carrito) {
                $itemsAcomprar = PagoItem::where('id_pago_compra', $pagoCompra->id_pago_compra)->pluck('id_item')->toArray();
                DB::table('items_intencion_compra')
                    ->where('id_carrito', $carrito->id_carrito)
                    ->whereIn('id_item', $itemsAcomprar)
                    ->delete();

                // Marcar solicitudes de servicio como pagadas (si aplica)
                if ($carrito->tipo === 'servicio') {
                    foreach ($itemsAcomprar as $idItem) {
                        $solicitud = $this->solicitudService->obtenerAprobada($carrito->id_user, $idItem);
                        if ($solicitud) {
                            $this->solicitudService->marcarPagada($solicitud->id_solicitud);
                        }
                    }
                }
            }

            // Asentar en el ERP de ventas
            $this->erpService->procesarVentaAprobada($pagoCompra);
        });

        // Registrar en logs_pagos
        DB::table('logs_pagos')->insert([
            'id_user'          => User::find(Carrito::find($pagoCompra->id_carrito)?->id_user)?->id ?? null,
            'custom_order_id'  => $pagoCompra->id_pago_compra,
            'provider'         => 'azul_redirect',
            'transaction_type' => 'sale_approved',
            'amount'           => $pagoCompra->total,
            'request_payload'  => json_encode([]),
            'response_payload' => json_encode($azulParams),
            'is_success'       => true,
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);

        // Enviar recibo por correo al cliente
        $this->enviarReciboCorreo($pagoCompra, $azulParams);
    }

    /**
     * Libera stock y cambia estado si la transacción falla o es cancelada.
     */
    private function procesarCancelacionODeclinacion(PagoCompra $pagoCompra, string $nuevoEstatus, string $motivo)
    {
        DB::transaction(function () use ($pagoCompra, $nuevoEstatus, $motivo) {
            $pagoCompra->estatus = $nuevoEstatus;
            $pagoCompra->save();

            // Registrar trazabilidad
            CompraTrazabilidad::create([
                'id_pago_compra'  => $pagoCompra->id_pago_compra,
                'estado_anterior' => 'pendiente',
                'estado_nuevo'    => $nuevoEstatus,
                'nota'            => $motivo,
                'id_admin'        => null,
            ]);

            // Devolver el inventario reservado
            $pagoItems = PagoItem::where('id_pago_compra', $pagoCompra->id_pago_compra)->get();
            foreach ($pagoItems as $pagoItem) {
                $itemModel = \App\Models\Item::find($pagoItem->id_item);
                if ($itemModel) {
                    $inventario = $itemModel->inventarios;
                    if ($inventario) {
                        $inventario->cantidad += $pagoItem->cantidad;
                        $inventario->save();
                    }
                }
            }
        });

        // Registrar en logs_pagos
        DB::table('logs_pagos')->insert([
            'id_user'          => User::find(Carrito::find($pagoCompra->id_carrito)?->id_user)?->id ?? null,
            'custom_order_id'  => $pagoCompra->id_pago_compra,
            'provider'         => 'azul_redirect',
            'transaction_type' => 'sale_failed',
            'amount'           => $pagoCompra->total,
            'request_payload'  => json_encode([]),
            'response_payload' => json_encode(['status' => $nuevoEstatus, 'reason' => $motivo]),
            'is_success'       => false,
            'error_message'    => $motivo,
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);
    }

    /**
     * Envia el recibo de compra por email.
     */
    private function enviarReciboCorreo(PagoCompra $pagoCompra, array $azulParams)
    {
        try {
            $carrito = Carrito::find($pagoCompra->id_carrito);
            if (!$carrito) return;

            $user = User::find($carrito->id_user);
            if (!$user || !$user->email) return;

            $pagoItems = PagoItem::where('id_pago_compra', $pagoCompra->id_pago_compra)->get();
            $itemsText = "";
            foreach ($pagoItems as $itemSel) {
                $sub = number_format($itemSel->subtotal, 2);
                $itemsText .= "- {$itemSel->nombre_item} x{$itemSel->cantidad}: RD\$ {$sub}\n";
            }

            $direccion = null;
            if ($pagoCompra->id_direccion) {
                $direccion = Direcciones::find($pagoCompra->id_direccion);
            }

            $dirTexto = "N/A (Servicio)";
            if ($direccion) {
                $dirTexto = "{$direccion->calle}";
                if ($direccion->N_casa_edificio) $dirTexto .= ", #{$direccion->N_casa_edificio}";
                if ($direccion->municipio?->municipio) $dirTexto .= ", {$direccion->municipio->municipio}";
                if ($direccion->provincia?->provincia) $dirTexto .= ", {$direccion->provincia->provincia}";
                $dirTexto .= ", República Dominicana";
            }

            $fecha = now()->format('d/m/Y H:i A');
            $totalFormatted = number_format($pagoCompra->total, 2);

            $emailContent = "Hola, {$user->nombres} {$user->apellidos}:\n\n" .
                "¡Gracias por tu compra en Cámbialo RD! A continuación, te presentamos el detalle de tu recibo:\n\n" .
                "Número de Orden: {$pagoCompra->id_pago_compra}\n" .
                "Fecha: {$fecha}\n" .
                "Estatus de Transacción: Aprobado\n" .
                "Código de Autorización: " . ($azulParams['AuthorizationCode'] ?? 'N/A') . "\n\n" .
                "Detalle de la Compra:\n" .
                $itemsText . "\n" .
                "Total Procesado: RD\$ {$totalFormatted} (DOP)\n\n" .
                "Dirección de Entrega: {$dirTexto}\n\n" .
                "----------------------------------------\n" .
                "Cámbialo RD\n" .
                "Dirección permanente: Napoleón Bonaparte, Manzana T, Edificio 21, Res. Pablo Mella Morales II, Santo Domingo, República Dominicana\n" .
                "Soporte al Cliente: Teléfono: (829) 963-4839 | Email: cambialord.com@gmail.com\n" .
                "http://cambialord.com.do\n\n" .
                "Nota de seguridad: Cámbialo RD no almacena la información completa de tu tarjeta de crédito o débito ni tu CVV. Toda la información de pago es transmitida de forma segura y encriptada (cifrado TLS 1.2) a través del procesador de pagos AZUL.";

            Mail::raw($emailContent, function ($message) use ($user) {
                $message->to($user->email)
                        ->subject('Recibo de compra - Cámbialo RD');
            });
        } catch (\Throwable $e) {
            Log::error('[Azul Redirect] Error al enviar recibo por email', ['error' => $e->getMessage()]);
        }
    }
}
