<?php

namespace App\Services;

use App\Models\Carrito;
use App\Models\CompraTrazabilidad;
use App\Models\Direcciones;
use App\Models\PagoCompra;
use App\Models\PagoItem;
use App\Models\TarjetaPago;
use App\Services\PagoService;
use App\Services\SolicitudServicioService;
use App\Services\ERPService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * ============================================================
 * CheckoutService — Flujo completo de checkout y pago
 * ============================================================
 *
 * Orquesta todo el proceso de pago:
 * 1. Carga carrito con items seleccionados
 * 2. Valida dirección predeterminada
 * 3. Verifica stock de cada item
 * 4. Calcula monto total (valor × cantidad − descuento)
 * 5. Valida tarjeta del usuario
 * 6. Ejecuta cobro vía PagoService (CardNet/Stripe)
 * 7. Registra PagoCompra, PagoItems, trazabilidad y descuenta inventario
 * 8. Si falla el registro en BD post-cobro, intenta reembolso automático
 *
 * Moneda: RD$ (código ISO 214)
 * ============================================================
 */
class CheckoutService
{
    public function __construct(
        private PagoService $pagoService,
        private SolicitudServicioService $solicitudService,
        private ERPService $erpService,
    ) {}

    /**
     * Ejecuta el flujo completo de checkout.
     *
     * @return array{success: bool, message: string, approval_code: string|null}
     */
    public function procesar(int $userId, string $idTarjeta, ?string $cvv, string $clientIp): array
    {
        // 1. Cargar carrito con items seleccionados
        $carrito = $this->obtenerCarrito($userId);
        $itemsSeleccionados = $carrito->itemsIntencionCompra->where('es_seleccionado', true);

        if ($itemsSeleccionados->isEmpty()) {
            return $this->error('No hay ítems seleccionados para pagar.');
        }

        // 2. Validar que no se compre a sí mismo
        $itemsPropios = $itemsSeleccionados->filter(fn($i) => $i->item->id_user === $userId);
        if ($itemsPropios->isNotEmpty()) {
            return $this->error('No puedes comprar tus propios artículos.');
        }

        // 3. Determinar si es carrito de servicios (no requiere envío)
        $esServicio = $carrito->tipo === 'servicio';

        // 3b. Para servicios: verificar si necesitan solicitud de aprobación
        if ($esServicio) {
            // Verificar si TODOS los items tienen solicitud aprobada
            $todosAprobados = true;
            foreach ($itemsSeleccionados as $itemIntencion) {
                if (!$this->solicitudService->tieneAprobacion($userId, $itemIntencion->item->id_item)) {
                    $todosAprobados = false;
                    break;
                }
            }

            if (!$todosAprobados) {
                // Crear solicitudes y retornar sin cobrar
                return $this->solicitudService->crearDesdeCarrito($userId, $carrito);
            }
            // Si todos aprobados, continuar al pago normal
        }

        // 4. Validar dirección predeterminada (solo para productos)
        $direccion = null;
        if (!$esServicio) {
            $direccion = $this->obtenerDireccionPredeterminada($userId);
            if (!$direccion) {
                return $this->error('Debes registrar una dirección de envío antes de realizar un pago. Ve a tu perfil → Direcciones.');
            }
        }

        // 4. Verificar stock (con precios actualizados)
        $errorStock = $this->verificarStock($itemsSeleccionados);
        if ($errorStock) {
            return $this->error($errorStock);
        }

        // 5. Calcular monto total (usa precios actuales del item, no del carrito)
        $montoTotal = $this->calcularTotal($itemsSeleccionados);
        if ($montoTotal <= 0) {
            return $this->error('El monto total no puede ser cero o negativo.');
        }

        // Calcular costo de envío si aplica (solo para productos físicos)
        if (!$esServicio && $direccion) {
            $maxPeso = 0;
            $maxAlto = 0;
            $maxAncho = 0;
            $maxProfundo = 0;
            foreach ($itemsSeleccionados as $i) {
                if ($i->item) {
                    $maxPeso = max($maxPeso, (float) ($i->item->peso_lbs ?? 0));
                    $maxAlto = max($maxAlto, (float) ($i->item->alto_cm ?? 0));
                    $maxAncho = max($maxAncho, (float) ($i->item->ancho_cm ?? 0));
                    $maxProfundo = max($maxProfundo, (float) ($i->item->profundo_cm ?? 0));
                }
            }

            $deliveryService = app(\App\Services\DeliveryService::class);
            $pueblo = $direccion->municipio->municipio ?? '';
            $resultadoDelivery = $deliveryService->calcular($pueblo, 'persona', $montoTotal, $maxPeso, $maxAlto, $maxAncho, $maxProfundo);
            
            if (!$resultadoDelivery['success'] && ($resultadoDelivery['error_code'] ?? null) === 'MISSING_DELIVERY_TARIFF') {
                return $this->error('El sistema espera por una definición para el cálculo de Análisis de costos de envío. Por favor, espera a que el administrador defina el costo de envío.');
            }
            
            $costoEnvio = $resultadoDelivery['success'] ? (float) ($resultadoDelivery['costo_envio_total'] ?? 0) : 0;
            $montoTotal += $costoEnvio;
        }

        // 6. Validar tarjeta del usuario
        $tarjeta = $this->obtenerTarjetaUsuario($idTarjeta, $userId);
        if (!$tarjeta) {
            return $this->error('Tarjeta no válida o no pertenece a tu cuenta.');
        }

        // 6. Preparar datos de cobro
        $datosTarjeta = $this->prepararDatosTarjeta($tarjeta, $cvv);
        $opciones = $this->prepararOpciones($carrito, $clientIp);

        // 7. Cobrar
        $resultadoPago = $this->pagoService->cobrarTarjeta($montoTotal, '214', $datosTarjeta, $opciones);

        Log::info('Resultado del cobro', [
            'success' => $resultadoPago['success'],
            'status'  => $resultadoPago['status'] ?? null,
        ]);

        if (!$resultadoPago['success']) {
            Log::error('Pago rechazado', ['error' => $resultadoPago['error']]);
            return $this->error('Pago rechazado: ' . ($resultadoPago['error'] ?? 'Error desconocido'));
        }

        // 8. Registrar en BD (con reembolso automático si falla)
        return $this->registrarCompra(
            $itemsSeleccionados, $carrito, $tarjeta,
            $resultadoPago, $montoTotal, $direccion, $userId
        );
    }

    // ---------------------------------------------------------------
    // Métodos privados — lógica de negocio
    // ---------------------------------------------------------------

    private function obtenerCarrito(int $userId): Carrito
    {
        return Carrito::with('itemsIntencionCompra.item.inventarios')
            ->where('id_user', $userId)
            ->firstOrFail();
    }

    private function obtenerDireccionPredeterminada(int $userId): ?Direcciones
    {
        return Direcciones::with('municipio')->where('id_user', $userId)
            ->where('es_predeterminada', 1)
            ->first();
    }

    private function verificarStock(Collection $items): ?string
    {
        foreach ($items as $item) {
            // Verificar que el item siga activo
            if ($item->item->estatus != 1) {
                return "El artículo \"{$item->item->item}\" ya no está disponible.";
            }

            // Servicios (categoría 29) no requieren verificación de stock
            if ((int) ($item->item->id_categoria_item ?? 0) === 29) {
                continue;
            }

            $inventario = $item->item->inventarios;
            if (!$inventario || $inventario->cantidad < $item->cantidad) {
                $disponible = $inventario->cantidad ?? 0;
                return "Stock insuficiente para: {$item->item->item}. Disponible: {$disponible}";
            }
        }
        return null;
    }

    private function calcularTotal(Collection $items): float
    {
        return $items->sum(
            fn($i) => ($i->item->valor * $i->cantidad) - $i->descuento
        );
    }

    private function obtenerTarjetaUsuario(string $idTarjeta, int $userId): ?TarjetaPago
    {
        $tarjeta = TarjetaPago::where('id_tarjeta', $idTarjeta)
            ->where('id_user', $userId)
            ->first();

        if (!$tarjeta) {
            Log::warning('Tarjeta no encontrada o no pertenece al usuario', [
                'id_tarjeta' => $idTarjeta,
                'user_id'    => $userId,
            ]);
        }

        return $tarjeta;
    }

    private function prepararDatosTarjeta(TarjetaPago $tarjeta, ?string $cvv): array
    {
        return $tarjeta->datosDriver($cvv);
    }

    private function prepararOpciones(Carrito $carrito, string $clientIp): array
    {
        $invoiceNumber = substr((string) $carrito->id_carrito, 0, 15);
        return [
            'invoice_number'   => $invoiceNumber,
            'reference_number' => $invoiceNumber,
            'client_ip'        => $clientIp,
        ];
    }

    /**
     * Registra la compra en BD. Si falla, intenta reembolso automático.
     */
    private function registrarCompra(
        Collection $itemsSeleccionados,
        Carrito $carrito,
        TarjetaPago $tarjeta,
        array $resultadoPago,
        float $montoTotal,
        ?Direcciones $direccion,
        int $userId,
    ): array {
        try {
            $pagoCompra = DB::transaction(function () use ($itemsSeleccionados, $carrito, $tarjeta, $resultadoPago, $montoTotal, $direccion) {
                // Bloqueo pesimista: evita pedidos duplicados por doble submit (ventana de 2 minutos)
                $carritoLocked = Carrito::where('id_carrito', $carrito->id_carrito)->lockForUpdate()->first();
                $yaExiste = PagoCompra::where('id_carrito', $carritoLocked->id_carrito)
                    ->where('estatus', 'aprobado')
                    ->where('fecha', '>=', now()->subMinutes(2))
                    ->exists();
                if ($yaExiste) {
                    throw new \RuntimeException('duplicate_order');
                }

                $pagoCompra = PagoCompra::create([
                    'id_pago_compra'    => Str::uuid()->toString(),
                    'id_carrito'        => $carrito->id_carrito,
                    'estatus'           => 'aprobado',
                    'id_tarjeta'        => $tarjeta->id_tarjeta,
                    'autorizacion_pago' => $resultadoPago['approval_code'] ?? null,
                    'id_proveedor_pago' => 1,
                    'transaction_id'    => $resultadoPago['transaction_id'] ?? null,
                    'total'             => $montoTotal,
                    'cantidad_items'    => $itemsSeleccionados->count(),
                    'id_direccion'      => $direccion?->id_direccion,
                ]);

                CompraTrazabilidad::create([
                    'id_pago_compra'  => $pagoCompra->id_pago_compra,
                    'estado_anterior' => null,
                    'estado_nuevo'    => 'aprobado',
                    'nota'            => 'Pago procesado correctamente. Autorización: ' . ($resultadoPago['approval_code'] ?? 'N/A') .
                                        ' | Dirección ID: ' . ($direccion?->id_direccion ?? 'N/A (servicio)'),
                    'id_admin'        => null,
                ]);

                foreach ($itemsSeleccionados as $itemIntencion) {
                    $this->registrarPagoItem($pagoCompra, $itemIntencion);
                }

                // --- AUTOMATIZACIÓN ERP ---
                $this->erpService->procesarVentaAprobada($pagoCompra);

                return $pagoCompra;
            });

            Log::info('Pago completado', ['user_id' => $userId, 'carrito' => $carrito->id_carrito]);

            // Marcar solicitudes de servicio como pagadas (si aplica)
            if ($carrito->tipo === 'servicio') {
                foreach ($itemsSeleccionados as $itemIntencion) {
                    $solicitud = $this->solicitudService->obtenerAprobada($userId, $itemIntencion->item->id_item);
                    if ($solicitud) {
                        $this->solicitudService->marcarPagada($solicitud->id_solicitud);
                    }
                }
            }

            // Enviar recibo por correo al cliente (Requisito AZUL)
            try {
                $user = \App\Models\User::find($userId);
                if ($user && $user->email) {
                    $fecha = now()->format('d/m/Y H:i A');
                    $itemsText = "";
                    foreach ($itemsSeleccionados as $itemSel) {
                        $nombre = $itemSel->item->item ?? 'Artículo';
                        $cant = $itemSel->cantidad;
                        $sub = number_format(($itemSel->item->valor * $itemSel->cantidad) - ($itemSel->descuento ?? 0), 2);
                        $itemsText .= "- {$nombre} x{$cant}: RD\$ {$sub}\n";
                    }
                    
                    $totalFormatted = number_format($montoTotal, 2);
                    
                    $dirTexto = "N/A (Servicio)";
                    if ($direccion) {
                        $dirTexto = "{$direccion->calle}";
                        if ($direccion->N_casa_edificio) $dirTexto .= ", #{$direccion->N_casa_edificio}";
                        if ($direccion->municipio?->municipio) $dirTexto .= ", {$direccion->municipio->municipio}";
                        if ($direccion->provincia?->provincia) $dirTexto .= ", {$direccion->provincia->provincia}";
                        $dirTexto .= ", República Dominicana";
                    }

                    $subtotalItems = $this->calcularTotal($itemsSeleccionados);
                    $costoEnvio = $montoTotal - $subtotalItems;

                    $breakdownText = "Subtotal de la Compra: RD\$ " . number_format($subtotalItems, 2) . "\n";
                    if ($costoEnvio > 0 && isset($resultadoDelivery) && ($resultadoDelivery['success'] ?? false)) {
                        $desglose = $resultadoDelivery['desglose'] ?? [];
                        $flete = $desglose['costo_flete'] ?? 0;
                        $plataforma = $desglose['costo_plataforma'] ?? 0;
                        $seguro = $desglose['costo_seguro'] ?? 0;
                        $manejo = $desglose['costo_manejo'] ?? 0;
                        $recargo = $desglose['recargo_sobredimensionado'] ?? 0;
                        
                        $breakdownText .= "\nDetalles de Envío y Gestión:\n";
                        $breakdownText .= "  - Costo de Envío Base (Flete): RD\$ " . number_format($flete, 2) . "\n";
                        $breakdownText .= "  - Cargo de Gestión de Plataforma: RD\$ " . number_format($plataforma, 2) . "\n";
                        $breakdownText .= "  - Seguro de Envío: RD\$ " . number_format($seguro, 2) . "\n";
                        $breakdownText .= "  - Costo de Manejo: RD\$ " . number_format($manejo, 2) . "\n";
                        if ($recargo > 0) {
                            $breakdownText .= "  - Recargo por Sobredimensión/Sobrepeso: RD\$ " . number_format($recargo, 2) . " (Artículo supera límites estándar)\n";
                        }
                        $breakdownText .= "Costo Total de Envío: RD\$ " . number_format($costoEnvio, 2) . "\n";
                    } elseif ($costoEnvio > 0) {
                        $breakdownText .= "Costo de Envío: RD\$ " . number_format($costoEnvio, 2) . "\n";
                    }

                    $emailContent = "Hola, {$user->nombres} {$user->apellidos}:\n\n" .
                        "¡Gracias por tu compra en Cámbialo RD! A continuación, te presentamos el detalle de tu recibo:\n\n" .
                        "Número de Orden: {$pagoCompra->id_pago_compra}\n" .
                        "Fecha: {$fecha}\n" .
                        "Estatus de Transacción: Aprobado\n" .
                        "Código de Autorización: " . ($resultadoPago['approval_code'] ?? 'N/A') . "\n\n" .
                        "Detalle de la Compra:\n" .
                        $itemsText . "\n" .
                        $breakdownText . "\n" .
                        "Total Procesado: RD\$ {$totalFormatted} (DOP)\n\n" .
                        "Dirección de Entrega: {$dirTexto}\n\n" .
                        "----------------------------------------\n" .
                        "Cámbialo RD\n" .
                        "Dirección permanente: Napoleón Bonaparte, Manzana T, Edificio 21, Res. Pablo Mella Morales II, Santo Domingo, República Dominicana\n" .
                        "Soporte al Cliente: Teléfono: (829) 963-4839 | Email: cambialord.com@gmail.com\n" .
                        "http://cambialord.com.do\n\n" .
                        "Nota de seguridad: Cámbialo RD no almacena la información completa de tu tarjeta de crédito o débito ni tu CVV. Toda la información de pago es transmitida de forma segura y encriptada (cifrado TLS 1.2) a través del procesador de pagos AZUL.";

                    \Illuminate\Support\Facades\Mail::raw($emailContent, function ($message) use ($user) {
                        $message->to($user->email)
                                ->subject('Recibo de compra - Cámbialo RD');
                    });
                }
            } catch (\Throwable $e) {
                Log::error('Error al enviar el recibo de compra por email', ['error' => $e->getMessage()]);
            }

            return $this->exito('¡Pago procesado correctamente! Tu pedido está en camino.');

        } catch (\RuntimeException $e) {
            if ($e->getMessage() === 'duplicate_order') {
                Log::warning('Pedido duplicado bloqueado', ['user_id' => $userId, 'carrito' => $carrito->id_carrito]);
                return $this->intentarReembolso($resultadoPago, $montoTotal, $userId);
            }
            Log::error('Error post-cobro al guardar en BD', ['error' => $e->getMessage()]);
            return $this->intentarReembolso($resultadoPago, $montoTotal, $userId);
        } catch (\Throwable $e) {
            Log::error('Error post-cobro al guardar en BD', ['error' => $e->getMessage()]);
            return $this->intentarReembolso($resultadoPago, $montoTotal, $userId);
        }
    }

    /**
     * Registra un item del pago, descuenta inventario y limpia el carrito.
     */
    private function registrarPagoItem(PagoCompra $pagoCompra, $itemIntencion): void
    {
        $itemModel  = $itemIntencion->item;
        $inventario = $itemModel->inventarios;

        $imagen    = $itemModel->imagenes()->first();
        $imagenUrl = null;
        if ($imagen) {
            $ruta = trim($imagen->ruta ?? '', '/');
            $directPath = $ruta . '/' . $imagen->nombre;
            // Guardar la ruta que realmente existe en el servidor
            if (file_exists(public_path($directPath))) {
                $imagenUrl = $directPath;
            } elseif (file_exists(public_path('storage/' . $directPath))) {
                $imagenUrl = 'storage/' . $directPath;
            } else {
                $imagenUrl = $directPath;
            }
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

        // Descontar inventario (solo si existe registro de inventario)
        if ($inventario) {
            $inventario->cantidad -= $itemIntencion->cantidad;
            $inventario->save();
        }

        $itemIntencion->delete();
    }

    /**
     * Intenta reembolso automático tras fallo de BD post-cobro.
     */
    private function intentarReembolso(array $resultadoPago, float $montoTotal, int $userId): array
    {
        $txId         = $resultadoPago['transaction_id'] ?? null;
        $approvalCode = $resultadoPago['approval_code'] ?? 'N/A';

        if (!$txId) {
            Log::critical("COBRO_SIN_TRANSACTION_ID: no se puede reembolsar | approval_code: {$approvalCode} | user_id: {$userId}");
            return $this->error("Tu pago fue procesado pero ocurrió un error al registrarlo. Contacta soporte con tu código: {$approvalCode}");
        }

        try {
            $reembolso = $this->pagoService->reembolsar($txId, $montoTotal);

            if ($reembolso['success'] ?? false) {
                Log::info('Reembolso automático exitoso tras fallo de BD', ['transaction_id' => $txId]);
                return $this->error('Ocurrió un error al registrar tu pago. El cargo fue revertido automáticamente. Intenta de nuevo.');
            }

            Log::critical("REEMBOLSO_FALLIDO_TRAS_ERROR_BD: " . ($reembolso['error'] ?? 'sin detalle') . " | transaction_id: {$txId} | approval_code: {$approvalCode} | user_id: {$userId}");
        } catch (\Throwable $e) {
            Log::critical("EXCEPCION_EN_REEMBOLSO: {$e->getMessage()} | transaction_id: {$txId} | approval_code: {$approvalCode} | user_id: {$userId}");
        }

        return $this->error("Tu pago fue procesado pero ocurrió un error al registrarlo. Contacta soporte con tu código: {$approvalCode}");
    }

    // ---------------------------------------------------------------
    // Helpers de respuesta
    // ---------------------------------------------------------------

    private function exito(string $message): array
    {
        return ['success' => true, 'message' => $message];
    }

    private function error(string $message): array
    {
        return ['success' => false, 'message' => $message];
    }
}
