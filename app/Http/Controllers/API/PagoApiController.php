<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\CheckoutService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Direcciones;

class PagoApiController extends Controller
{
    public function __construct(
        private CheckoutService $checkoutService
    ) {}

    /** POST /api/pago/checkout */
    public function checkout(Request $request)
    {
        $userId = $request->user()->id;

        // 1. Obtener carrito y sus items seleccionados
        $carrito = \App\Models\Carrito::with('itemsIntencionCompra.item.inventarios')
            ->where('id_user', $userId)
            ->first();

        if (!$carrito) {
            return response()->json(['success' => false, 'message' => 'No se encontró tu carrito.'], 404);
        }

        $itemsSeleccionados = $carrito->itemsIntencionCompra->where('es_seleccionado', true);
        if ($itemsSeleccionados->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'No hay ítems seleccionados para pagar.'], 422);
        }

        // 2. Validar que no compre a sí mismo
        $itemsPropios = $itemsSeleccionados->filter(fn($i) => $i->item->id_user === $userId);
        if ($itemsPropios->isNotEmpty()) {
            return response()->json(['success' => false, 'message' => 'No puedes comprar tus propios artículos.'], 422);
        }

        $esServicio = $carrito->tipo === 'servicio';

        // 3. Validar dirección de envío (solo para productos)
        $idDireccion = $request->input('id_direccion');
        $direccion = null;
        if (!$esServicio) {
            if (!$idDireccion) {
                // intentamos obtener la predeterminada
                $direccion = \App\Models\Direcciones::with('municipio')->where('id_user', $userId)
                    ->where('es_predeterminada', 1)
                    ->first();
            } else {
                $direccion = \App\Models\Direcciones::with('municipio')->where('id_user', $userId)
                    ->where('id_direccion', $idDireccion)
                    ->first();
            }

            if (!$direccion) {
                return response()->json(['success' => false, 'message' => 'Debes seleccionar una dirección de envío.'], 422);
            }
        }

        // 4. Verificar stock disponible
        foreach ($itemsSeleccionados as $item) {
            if ($item->item->estatus != 1) {
                return response()->json(['success' => false, 'message' => "El artículo \"{$item->item->item}\" ya no está disponible."], 422);
            }

            if (!$esServicio) {
                $inventario = $item->item->inventarios;
                if (!$inventario || $inventario->cantidad < $item->cantidad) {
                    $disponible = $inventario->cantidad ?? 0;
                    return response()->json(['success' => false, 'message' => "Stock insuficiente para: {$item->item->item}. Disponible: {$disponible}"], 422);
                }
            }
        }

        // 5. Calcular monto total (subtotal de artículos)
        $montoTotal = $itemsSeleccionados->sum(
            fn($i) => ($i->item->valor * $i->cantidad) - $i->descuento
        );

        if ($montoTotal <= 0) {
            return response()->json(['success' => false, 'message' => 'El monto total debe ser mayor a cero.'], 422);
        }

        // Calcular costo de envío si aplica (solo para productos físicos)
        if (!$esServicio && $direccion) {
            $deliveryService = app(\App\Services\DeliveryService::class);
            $pueblo = $direccion->municipio->municipio ?? '';
            $resultadoDelivery = $deliveryService->calcular($pueblo, 'persona', $montoTotal);
            
            if (!$resultadoDelivery['success'] && ($resultadoDelivery['error_code'] ?? null) === 'MISSING_DELIVERY_TARIFF') {
                return response()->json([
                    'success' => false, 
                    'message' => 'El sistema espera por una definición para el cálculo de Análisis de costos de envío. Por favor, espera a que el administrador defina el costo de envío.'
                ], 422);
            }
            
            $costoEnvio = $resultadoDelivery['success'] ? (float) ($resultadoDelivery['costo_envio_total'] ?? 0) : 0;
            $montoTotal += $costoEnvio;
        }

        try {
            \App\Models\PagoCompra::liberarOrdenesPendientes($carrito->id_carrito);

            $pagoCompra = \Illuminate\Support\Facades\DB::transaction(function () use ($itemsSeleccionados, $carrito, $montoTotal, $direccion) {
                $carritoLocked = \App\Models\Carrito::where('id_carrito', $carrito->id_carrito)->lockForUpdate()->first();
                $yaExiste = \App\Models\PagoCompra::where('id_carrito', $carritoLocked->id_carrito)
                    ->whereIn('estatus', ['aprobado', 'pendiente'])
                    ->where('fecha', '>=', now()->subMinutes(2))
                    ->exists();

                if ($yaExiste) {
                    throw new \RuntimeException('duplicate_order');
                }

                $pagoCompra = \App\Models\PagoCompra::create([
                    'id_pago_compra'    => \Illuminate\Support\Str::uuid()->toString(),
                    'id_carrito'        => $carrito->id_carrito,
                    'estatus'           => 'pendiente',
                    'id_tarjeta'        => 'REDIRECT_AZUL_MOVIL',
                    'autorizacion_pago' => null,
                    'id_proveedor_pago' => 1, // AZUL
                    'transaction_id'    => null,
                    'total'             => $montoTotal,
                    'cantidad_items'    => $itemsSeleccionados->count(),
                    'id_direccion'      => $direccion?->id_direccion,
                    'fecha'             => now(),
                ]);

                \App\Models\CompraTrazabilidad::create([
                    'id_pago_compra'  => $pagoCompra->id_pago_compra,
                    'estado_anterior' => null,
                    'estado_nuevo'    => 'pendiente',
                    'nota'            => 'Pago redireccionado móvil iniciado.',
                    'id_admin'        => null,
                ]);

                foreach ($itemsSeleccionados as $itemIntencion) {
                    $itemModel = $itemIntencion->item;
                    $inventario = $itemModel->inventarios;

                    $imagen = $itemModel->imagenes()->first();
                    $imagenUrl = null;
                    if ($imagen) {
                        $ruta = trim($imagen->ruta ?? '', '/');
                        $directPath = $ruta . '/' . $imagen->nombre;
                        $imagenUrl = file_exists(public_path($directPath)) ? $directPath : (file_exists(public_path('storage/' . $directPath)) ? 'storage/' . $directPath : $directPath);
                    }

                    \App\Models\PagoItem::create([
                        'id_pago_compra'  => $pagoCompra->id_pago_compra,
                        'id_item'         => $itemModel->id_item,
                        'nombre_item'     => $itemModel->item,
                        'precio_unitario' => $itemModel->valor,
                        'cantidad'        => $itemIntencion->cantidad,
                        'descuento'       => $itemIntencion->descuento ?? 0,
                        'subtotal'        => ($itemModel->valor * $itemIntencion->cantidad) - ($itemIntencion->descuento ?? 0),
                        'imagen_url'      => $imagenUrl,
                    ]);
                }

                return $pagoCompra;
            });

            $redirectUrl = route('pago.redirect.iniciar-movil', ['id_pago_compra' => $pagoCompra->id_pago_compra]);

            return response()->json([
                'success'      => true,
                'message'      => 'Orden creada. Redirigiendo al pago...',
                'redirect_url' => $redirectUrl,
            ]);

        } catch (\Throwable $e) {
            if (isset($pagoCompra)) {
                try {
                    \Illuminate\Support\Facades\DB::transaction(function () use ($pagoCompra, $e) {
                        $pagoCompra->estatus = 'cancelado';
                        $pagoCompra->save();

                        \App\Models\CompraTrazabilidad::create([
                            'id_pago_compra'  => $pagoCompra->id_pago_compra,
                            'estado_anterior' => 'pendiente',
                            'estado_nuevo'    => 'cancelado',
                            'nota'            => 'Cancelado por fallo en checkout API: ' . $e->getMessage(),
                            'id_admin'        => null,
                        ]);
                    });
                } catch (\Throwable $ex) {
                    Log::error('Error al actualizar estatus a cancelado en catch de checkout API: ' . $ex->getMessage());
                }
            }

            if ($e instanceof \RuntimeException && $e->getMessage() === 'duplicate_order') {
                return response()->json(['success' => false, 'message' => 'Ya hay un pago en procesamiento para esta orden.'], 422);
            }
            return response()->json(['success' => false, 'message' => 'Error al procesar la orden: ' . $e->getMessage()], 500);
        }
    }
}
