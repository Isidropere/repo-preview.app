<?php

namespace App\Http\Controllers;

use App\Models\ItemIntencionCompra;
use Illuminate\Http\Request;

class ItemIntencionCompraController extends Controller
{
    public function index()
    {
        // Solo devuelve intenciones de compra del carrito del usuario autenticado (IDOR fix)
        return response()->json([
            'success' => true,
            'data' => ItemIntencionCompra::with(['carrito', 'item'])
                ->whereHas('carrito', fn($q) => $q->where('id_user', auth()->id()))
                ->get(),
            'message' => '',
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_carrito'       => 'required|integer|exists:carritos,id_carrito',
            'id_item'          => 'required|integer|exists:items,id_item',
            'cantidad'         => 'required|integer|min:1|max:1000000',
            'es_seleccionado'  => 'nullable|boolean',
            'descuento'        => 'nullable|numeric|min:0',
        ]);

        $intencion = ItemIntencionCompra::create($validated);

        return response()->json([
            'success' => true,
            'data' => $intencion,
            'message' => 'Intención de compra creada correctamente.',
        ], 201);
    }

    public function show($id)
    {
        // Verificar que la intención pertenezca al carrito del usuario (IDOR fix)
        $intencion = ItemIntencionCompra::with(['carrito', 'item'])
            ->whereHas('carrito', fn($q) => $q->where('id_user', auth()->id()))
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $intencion,
            'message' => '',
        ]);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'cantidad'        => 'sometimes|integer|min:1|max:1000000',
            'es_seleccionado' => 'nullable|boolean',
            'descuento'       => 'nullable|numeric|min:0',
        ]);

        // Verificar propiedad antes de actualizar (IDOR fix)
        $intencion = ItemIntencionCompra::whereHas('carrito', fn($q) => $q->where('id_user', auth()->id()))
            ->findOrFail($id);
        $intencion->update($validated);

        return response()->json([
            'success' => true,
            'data' => $intencion,
            'message' => 'Intención de compra actualizada correctamente.',
        ]);
    }

    public function destroy($id)
    {
        // Verificar propiedad antes de eliminar (IDOR fix)
        ItemIntencionCompra::whereHas('carrito', fn($q) => $q->where('id_user', auth()->id()))
            ->findOrFail($id)
            ->delete();

        return response()->json([
            'success' => true,
            'data' => null,
            'message' => 'Intención de compra eliminada correctamente.',
        ]);
    }
}
