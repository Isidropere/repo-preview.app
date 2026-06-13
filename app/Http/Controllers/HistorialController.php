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

        $compras = $compras->concat($mappedTalentos)->sortByDesc(function ($item) {
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

        return view('historial.historial', compact('compras', 'ventas', 'negociaciones', 'motivos'));
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

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('historial.factura_pdf', compact('compra'));
        
        return $pdf->download("factura-compra-{$id}.pdf");
    }
}
