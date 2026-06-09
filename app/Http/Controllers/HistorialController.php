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
            ->orderByDesc('fecha')
            ->get();

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
