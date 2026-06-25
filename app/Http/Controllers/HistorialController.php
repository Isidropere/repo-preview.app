<?php

namespace App\Http\Controllers;

use App\Models\ItemIntencionCompra;
use App\Models\Negociacion;
use App\Models\PagoCompra;
use Illuminate\Http\Request;

class HistorialController extends Controller
{
    public function __construct(
        private \App\Services\DevolucionService $devolucionService
    ) {}

    public function index(Request $request)
    {
        $userId = auth()->id();

        $compras = PagoCompra::whereHas('carrito', fn($q) => $q->where('id_user', $userId))
            ->with(['trazabilidad', 'tarjeta', 'pagoItems.item.imagenes', 'carrito.itemsIntencionCompra'])
            ->get();

        $compraCompletada = null;
        if ($orderId = session('order_completed_id')) {
            if (str_starts_with($orderId, 'TAL-')) {
                $compraCompletada = $this->obtenerCompraVirtualTalento($orderId, $userId);
            } elseif (str_starts_with($orderId, 'ENV-')) {
                $compraCompletada = $this->obtenerCompraVirtualEnvio($orderId, $userId);
            } else {
                $compraCompletada = PagoCompra::where('id_pago_compra', $orderId)
                    ->whereHas('carrito', fn($q) => $q->where('id_user', $userId))
                    ->with([
                        'pagoItems.item.imagenes',
                        'carrito.usuario',
                        'direccion.provincia',
                        'direccion.municipio',
                        'tarjeta',
                        'proveedorPago'
                    ])->first();
            }
        }

        $talentoCol = \App\Models\PagoRegistroTalento::where('id_user', $userId)
            ->where('estatus', 'aprobado')
            ->with(['item.imagenes', 'user'])
            ->get();

        $mappedTalentos = $talentoCol->map(function ($talento) {
            $virtualPago = new PagoCompra();
            $virtualPago->id_pago_compra = 'TAL-' . $talento->id_item . '-' . $talento->id;
            $virtualPago->estatus = 'aprobado';
            $virtualPago->total = (float) $talento->monto_pagado;
            $virtualPago->fecha = $talento->created_at;
            $virtualPago->is_talent_registration = true;
            $virtualPago->talent_name = $talento->item?->item ?? 'Talento-Servicio';
            $virtualPago->talent_id = $talento->id_item;
            $virtualPago->user = $talento->user;

            $carrito = new \App\Models\Carrito();
            $carrito->setRelation('usuario', $talento->user);
            $virtualPago->setRelation('carrito', $carrito);

            $pagoItem = new \App\Models\PagoItem();
            $pagoItem->nombre_item = 'Registro de Talento-Servicio: ' . ($talento->item?->item ?? 'Talento');
            $pagoItem->cantidad = 1;
            $pagoItem->precio_unitario = (float) $talento->monto_pagado;
            $pagoItem->descuento = 0;
            $pagoItem->subtotal = (float) $talento->monto_pagado;
            if ($talento->item) {
                $pagoItem->setRelation('item', $talento->item);
            }
            $virtualPago->setRelation('pagoItems', collect([$pagoItem]));
            $virtualPago->setRelation('trazabilidad', collect());

            // Fetch logs_pagos payload
            $log = \Illuminate\Support\Facades\DB::table('logs_pagos')
                ->where('transaction_type', 'talento_approved')
                ->where('is_success', true)
                ->where(function($q) use ($talento) {
                    $q->where('custom_order_id', 'like', 'TAL-' . $talento->id_item . '-%')
                      ->orWhere('custom_order_id', 'like', '%' . $talento->transaction_id . '%')
                      ->orWhere('response_payload', 'like', '%' . $talento->transaction_id . '%');
                })
                ->first();

            if ($log && !empty($log->response_payload)) {
                $payload = json_decode($log->response_payload, true);
                if (is_array($payload)) {
                    $virtualPago->azul_response_data = $payload;
                }
            } else {
                if (!empty($talento->notas) && preg_match('/Código Autorización:\s*([A-Za-z0-9]+)/i', $talento->notas, $matches)) {
                    $authCode = $matches[1];
                    $virtualPago->azul_response_data = [
                        'CardNumber' => 'xxxx-xxxx-xxxx-xxxx',
                        'DataVaultBrand' => 'Tarjeta',
                        'AuthorizationCode' => $authCode,
                        'RRN' => $talento->transaction_id,
                    ];
                }
            }

            return $virtualPago;
        });

        $enviosCol = \App\Models\PagoEnvioIntercambio::where('id_user', $userId)
            ->whereIn('estado', ['pagado', 'pagado_pull'])
            ->with(['negociacion.item.imagenes', 'usuario', 'tarjeta'])
            ->get();

        $mappedEnvios = $enviosCol->map(function ($envio) {
            $virtualPago = new PagoCompra();
            $virtualPago->id_pago_compra = 'ENV-' . $envio->id;
            $virtualPago->estatus = 'aprobado';
            $virtualPago->total = (float) $envio->monto;
            $virtualPago->fecha = $envio->created_at;
            $virtualPago->is_delivery_payment = true;
            $virtualPago->delivery_negociacion_id = $envio->id_negociacion;
            $virtualPago->user = $envio->usuario;

            $carrito = new \App\Models\Carrito();
            $carrito->setRelation('usuario', $envio->usuario);
            $virtualPago->setRelation('carrito', $carrito);

            $pagoItem = new \App\Models\PagoItem();
            $pagoItem->nombre_item = 'Costo de Envío Intercambio #' . $envio->id_negociacion . ': ' . ($envio->negociacion?->item?->item ?? 'Artículo');
            $pagoItem->cantidad = 1;
            $pagoItem->precio_unitario = (float) $envio->monto;
            $pagoItem->descuento = 0;
            $pagoItem->subtotal = (float) $envio->monto;
            if ($envio->negociacion?->item) {
                $pagoItem->setRelation('item', $envio->negociacion->item);
            }
            $virtualPago->setRelation('pagoItems', collect([$pagoItem]));
            $virtualPago->setRelation('trazabilidad', collect());

            $virtualPago->impuestos = 0.00;
            $virtualPago->costo_envio = (float) $envio->monto;

            // Tarjeta / Azul response
            if ($envio->estado === 'pagado_pull') {
                $virtualPago->autorizacion_pago = 'PULL';
                $virtualPago->transaction_id = 'PULL_' . $envio->id_pago_registro_talento;
            } else {
                $virtualPago->autorizacion_pago = $envio->approval_code;
                $virtualPago->transaction_id = $envio->transaction_id;

                $azulResponse = $envio->azul_response;
                if ($azulResponse) {
                    $virtualPago->azul_response_data = $azulResponse;
                    $tarjeta = new \App\Models\TarjetaPago();
                    $tarjeta->tipo_tarjeta = $azulResponse['DataVaultBrand'] ?? $azulResponse['Brand'] ?? 'Tarjeta';
                    $tarjeta->last4 = isset($azulResponse['CardNumber']) ? substr($azulResponse['CardNumber'], -4) : 'xxxx';
                    $tarjeta->nombre_titular = $envio->usuario->nombres ?? 'Cliente';
                    $virtualPago->setRelation('tarjeta', $tarjeta);
                } else if ($envio->tarjeta) {
                    $virtualPago->setRelation('tarjeta', $envio->tarjeta);
                }
            }

            $proveedor = new \App\Models\ProveedorPago();
            $proveedor->proveedor = $envio->estado === 'pagado_pull' ? 'PULL_POINTS' : 'AZUL';
            $virtualPago->setRelation('proveedorPago', $proveedor);

            return $virtualPago;
        });

        $compras = $compras->concat($mappedTalentos)->concat($mappedEnvios)->sortByDesc(function ($item) {
            return $item->fecha ? $item->fecha->timestamp : 0;
        });

        $ventas = ItemIntencionCompra::whereHas('item', fn($q) => $q->where('id_user', $userId))
            ->with([
                'item.imagenes',
                'item.usuario',
                'carrito.pagosCompra.trazabilidad',
                'carrito.usuario',
            ])
            ->orderByDesc('id_item_intencion_compra')
            ->get();

        $negociaciones = Negociacion::where('usuario_emisor_id', $userId)
            ->orWhere('usuario_receptor_id', $userId)
            ->with([
                'item.imagenes',
                'usuario',
                'usuarioReceptor',
                'pagoEnvios' => fn($q) => $q->where('id_user', $userId)->with('tarjeta')
            ])
            ->orderByDesc('id_negociacion')
            ->get();

        $motivos = \App\Models\MotivoDevolucion::where('activo', true)->get();

        return view('historial.historial', compact('compras', 'ventas', 'negociaciones', 'motivos', 'compraCompletada'));
    }

    public function procesarDevolucion(Request $request, $id)
    {
        try {
            $userId = auth()->id();
            $resultado = $this->devolucionService->procesarDevolucion(
                $id, 
                $userId, 
                $request->input('id_motivo_devolucion'), 
                $request->input('comentario_devolucion')
            );

            if ($resultado['success']) {
                return back()->with('success', $resultado['message']);
            }

            return back()->with('error', 'Ocurrió un error inesperado al procesar la devolución.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function procesarDevolucionApi(Request $request, $id)
    {
        try {
            $userId = auth()->id();
            $resultado = $this->devolucionService->procesarDevolucion(
                $id, 
                $userId, 
                $request->input('id_motivo_devolucion'), 
                $request->input('comentario_devolucion')
            );

            return response()->json([
                'success' => true,
                'message' => $resultado['message'] ?? 'Devolución procesada exitosamente.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function descargarFactura($id)
    {
        $userId = auth()->id();
        if (str_starts_with($id, 'TAL-')) {
            $compra = $this->obtenerCompraVirtualTalento($id, $userId);
            if (!$compra) {
                abort(404);
            }
        } elseif (str_starts_with($id, 'ENV-')) {
            $compra = $this->obtenerCompraVirtualEnvio($id, $userId);
            if (!$compra) {
                abort(404);
            }
        } else {
            $compra = PagoCompra::where('id_pago_compra', $id)
                ->whereHas('carrito', fn($q) => $q->where('id_user', $userId))
                ->with([
                    'pagoItems.item',
                    'carrito.usuario',
                    'direccion.provincia',
                    'direccion.municipio',
                    'tarjeta',
                    'proveedorPago'
                ])->firstOrFail();
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('historial.factura_pdf', compact('compra'));
        
        return $pdf->download("factura-compra-{$id}.pdf");
    }

    private function obtenerCompraVirtualTalento(string $orderId, int $userId): ?PagoCompra
    {
        $parts = explode('-', $orderId);
        $talentoId = $parts[2] ?? null;
        if (!$talentoId) return null;

        $talento = \App\Models\PagoRegistroTalento::where('id', $talentoId)
            ->where('id_user', $userId)
            ->with(['item.imagenes', 'user'])
            ->first();

        if (!$talento) return null;

        $virtualPago = new PagoCompra();
        $virtualPago->id_pago_compra = $orderId;
        $virtualPago->estatus = 'aprobado';
        $virtualPago->total = (float) $talento->monto_pagado;
        $virtualPago->fecha = $talento->created_at;
        $virtualPago->is_talent_registration = true;
        $virtualPago->talent_name = $talento->item?->item ?? 'Talento-Servicio';
        $virtualPago->talent_id = $talento->id_item;
        $virtualPago->impuestos = 0.00;
        $virtualPago->costo_envio = 0.00;

        $carrito = new \App\Models\Carrito();
        $carrito->id_user = $userId;
        $carrito->setRelation('usuario', $talento->user);
        $virtualPago->setRelation('carrito', $carrito);

        $pagoItem = new \App\Models\PagoItem();
        $pagoItem->nombre_item = 'Registro de Talento-Servicio: ' . ($talento->item?->item ?? 'Talento');
        $pagoItem->cantidad = 1;
        $pagoItem->precio_unitario = (float) $talento->monto_pagado;
        $pagoItem->descuento = 0;
        $pagoItem->subtotal = (float) $talento->monto_pagado;
        if ($talento->item) {
            $pagoItem->setRelation('item', $talento->item);
        }
        $virtualPago->setRelation('pagoItems', collect([$pagoItem]));
        $virtualPago->setRelation('trazabilidad', collect());

        // Fetch logs_pagos payload
        $log = \Illuminate\Support\Facades\DB::table('logs_pagos')
            ->where('transaction_type', 'talento_approved')
            ->where('is_success', true)
            ->where(function($q) use ($talento) {
                $q->where('custom_order_id', 'like', 'TAL-' . $talento->id_item . '-%')
                  ->orWhere('custom_order_id', 'like', '%' . $talento->transaction_id . '%')
                  ->orWhere('response_payload', 'like', '%' . $talento->transaction_id . '%');
            })
            ->first();

        $azulResponseData = null;
        $authCode = null;
        if ($log && !empty($log->response_payload)) {
            $payload = json_decode($log->response_payload, true);
            if (is_array($payload)) {
                $azulResponseData = $payload;
                $authCode = $payload['AuthorizationCode'] ?? null;
            }
        } else {
            if (!empty($talento->notas) && preg_match('/Código Autorización:\s*([A-Za-z0-9]+)/i', $talento->notas, $matches)) {
                $authCode = $matches[1];
                $azulResponseData = [
                    'CardNumber' => 'xxxx-xxxx-xxxx-xxxx',
                    'DataVaultBrand' => 'Tarjeta',
                    'AuthorizationCode' => $authCode,
                    'RRN' => $talento->transaction_id,
                ];
            }
        }

        if ($azulResponseData) {
            $virtualPago->azul_response_data = $azulResponseData;
            $virtualPago->autorizacion_pago = $authCode;
            $virtualPago->transaction_id = $talento->transaction_id;

            $tarjeta = new \App\Models\TarjetaPago();
            $tarjeta->tipo_tarjeta = $azulResponseData['DataVaultBrand'] ?? $azulResponseData['Brand'] ?? 'Tarjeta';
            $tarjeta->last4 = isset($azulResponseData['CardNumber']) ? substr($azulResponseData['CardNumber'], -4) : 'xxxx';
            $tarjeta->nombre_titular = $talento->user->nombres ?? 'Cliente';
            $virtualPago->setRelation('tarjeta', $tarjeta);
        }

        $proveedor = new \App\Models\ProveedorPago();
        $proveedor->proveedor = 'AZUL';
        $virtualPago->setRelation('proveedorPago', $proveedor);

        return $virtualPago;
    }

    private function obtenerCompraVirtualEnvio(string $orderId, int $userId): ?PagoCompra
    {
        $parts = explode('-', $orderId);
        $envioId = $parts[1] ?? null;
        if (!$envioId) return null;

        $envio = \App\Models\PagoEnvioIntercambio::where('id', $envioId)
            ->where('id_user', $userId)
            ->with(['negociacion.item.imagenes', 'usuario', 'tarjeta'])
            ->first();

        if (!$envio) return null;

        $virtualPago = new PagoCompra();
        $virtualPago->id_pago_compra = $orderId;
        $virtualPago->estatus = 'aprobado';
        $virtualPago->total = (float) $envio->monto;
        $virtualPago->fecha = $envio->created_at;
        $virtualPago->is_delivery_payment = true;
        $virtualPago->delivery_negociacion_id = $envio->id_negociacion;
        $virtualPago->user = $envio->usuario;

        $carrito = new \App\Models\Carrito();
        $carrito->id_user = $userId;
        $carrito->setRelation('usuario', $envio->usuario);
        $virtualPago->setRelation('carrito', $carrito);

        $pagoItem = new \App\Models\PagoItem();
        $pagoItem->nombre_item = 'Costo de Envío Intercambio #' . $envio->id_negociacion . ': ' . ($envio->negociacion?->item?->item ?? 'Artículo');
        $pagoItem->cantidad = 1;
        $pagoItem->precio_unitario = (float) $envio->monto;
        $pagoItem->descuento = 0;
        $pagoItem->subtotal = (float) $envio->monto;
        if ($envio->negociacion?->item) {
            $pagoItem->setRelation('item', $envio->negociacion->item);
        }
        $virtualPago->setRelation('pagoItems', collect([$pagoItem]));
        $virtualPago->setRelation('trazabilidad', collect());

        $virtualPago->impuestos = 0.00;
        $virtualPago->costo_envio = (float) $envio->monto;

        // Tarjeta / Azul response
        if ($envio->estado === 'pagado_pull') {
            $virtualPago->autorizacion_pago = 'PULL';
            $virtualPago->transaction_id = 'PULL_' . $envio->id_pago_registro_talento;
        } else {
            $virtualPago->autorizacion_pago = $envio->approval_code;
            $virtualPago->transaction_id = $envio->transaction_id;

            $azulResponse = $envio->azul_response;
            if ($azulResponse) {
                $virtualPago->azul_response_data = $azulResponse;
                $tarjeta = new \App\Models\TarjetaPago();
                $tarjeta->tipo_tarjeta = $azulResponse['DataVaultBrand'] ?? $azulResponse['Brand'] ?? 'Tarjeta';
                $tarjeta->last4 = isset($azulResponse['CardNumber']) ? substr($azulResponse['CardNumber'], -4) : 'xxxx';
                $tarjeta->nombre_titular = $envio->usuario->nombres ?? 'Cliente';
                $virtualPago->setRelation('tarjeta', $tarjeta);
            } else if ($envio->tarjeta) {
                $virtualPago->setRelation('tarjeta', $envio->tarjeta);
            }
        }

        $proveedor = new \App\Models\ProveedorPago();
        $proveedor->proveedor = $envio->estado === 'pagado_pull' ? 'PULL_POINTS' : 'AZUL';
        $virtualPago->setRelation('proveedorPago', $proveedor);

        return $virtualPago;
    }
}
