<?php

namespace App\Http\Controllers;

use App\Models\Negociacion;
use App\Services\NegociacionService;
use Illuminate\Http\Request;

/**
 * ============================================================
 * NEGOCIACIONES (INTERCAMBIOS)
 * ============================================================
 *
 * ACTORES:
 *   Emisor   → usuario que QUIERE el artículo ajeno y propone el intercambio
 *   Receptor → usuario DUEÑO del artículo solicitado
 *
 * ESTADOS:
 *   Inicial → pendiente → contraoferta → aceptado → completado
 *                       → rechazado / cancelado
 * ============================================================
 */
class NegociacionController extends Controller
{
    public function __construct(
        private NegociacionService $negociacionService,
    ) {}

    public function store(Request $request)
    {
        $validated = $request->validate([
            'item_id'      => 'required|exists:items,id_item',
            'mensaje'      => 'required|string|max:500',
            'paquete_id'   => 'nullable|integer|exists:paquetes,id_paquete',
            'monto_oferta' => 'nullable|numeric|min:0',
        ]);

        $resultado = $this->negociacionService->crear(auth()->id(), $validated);

        $status = $resultado['success'] ? 200 : 422;
        return response()->json([
            'status'  => $resultado['success'] ? 'ok' : 'error',
            'message' => $resultado['message'],
        ], $status);
    }

    public function getNegociaciones($itemId)
    {
        $data = $this->negociacionService->obtenerNegociaciones(auth()->id(), $itemId);
        return response()->json($data);
    }

    public function obtenerMensajes($id_emisor, $id_receptor)
    {
        if (!auth()->id()) {
            return response()->json(['error' => 'Usuario no autenticado'], 401);
        }

        $data = $this->negociacionService->obtenerMensajes(auth()->id(), $id_emisor, $id_receptor);
        return response()->json($data);
    }

    public function aceptar($id)
    {
        $resultado = $this->negociacionService->aceptar(auth()->id(), $id);
        return back()->with($resultado['success'] ? 'success' : 'error', $resultado['message']);
    }

    public function rechazar(Request $request, $id)
    {
        $resultado = $this->negociacionService->rechazar(auth()->id(), $id);
        return back()->with($resultado['success'] ? 'success' : 'error', $resultado['message']);
    }

    public function storeContraoferta(Request $request, $id)
    {
        $validated = $request->validate([
            'monto_contra_oferta' => 'nullable|numeric|min:0',
            'mensaje'             => 'nullable|string|max:500',
        ]);

        $resultado = $this->negociacionService->contraoferta(auth()->id(), $id, $validated);
        return back()->with($resultado['success'] ? 'success' : 'error', $resultado['message']);
    }

    public function verChat($id)
    {
        $negociacion = Negociacion::with(['usuario', 'usuarioReceptor'])->findOrFail($id);
        return view('negociaciones', compact('negociacion'));
    }
}
