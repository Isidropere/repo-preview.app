<?php

namespace App\Http\Controllers;

use App\Models\CategoriaItem;
use Illuminate\Http\Request;

class CategoriaItemController extends Controller
{
    public function index()
    {
        return response()->json([
            'success' => true,
            'data' => CategoriaItem::with('items')->get(),
            'message' => '',
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'categoria' => 'required|string|max:255|unique:categorias_item,categoria',
        ]);

        $categoria = CategoriaItem::create($validated);

        return response()->json([
            'success' => true,
            'data' => $categoria,
            'message' => 'Categoría creada correctamente.',
        ], 201);
    }

    public function show($id)
    {
        return response()->json([
            'success' => true,
            'data' => CategoriaItem::with('items')->findOrFail($id),
            'message' => '',
        ]);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'categoria' => 'required|string|max:255|unique:categorias_item,categoria,' . $id . ',id_categoria_item',
        ]);

        $categoria = CategoriaItem::findOrFail($id);
        $categoria->update($validated);

        return response()->json([
            'success' => true,
            'data' => $categoria,
            'message' => 'Categoría actualizada correctamente.',
        ]);
    }

    public function destroy($id)
    {
        CategoriaItem::findOrFail($id)->delete();

        return response()->json([
            'success' => true,
            'data' => null,
            'message' => 'Categoría eliminada correctamente.',
        ]);
    }
}
