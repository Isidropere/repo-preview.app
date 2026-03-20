<?php

namespace App\Http\Controllers;

use App\Models\Rating;
use Illuminate\Http\Request;

class RatingController extends Controller
{
    public function index()
    {
        // Solo devuelve ratings del usuario autenticado (evita IDOR)
        return response()->json([
            'success' => true,
            'data' => Rating::with(['usuario', 'direcciones'])
                ->where('id_user', auth()->id())
                ->paginate(20),
            'message' => '',
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_user'      => 'required|integer|exists:users,id',
            'id_direccion' => 'nullable|integer|exists:direcciones,id_direccion',
            'puntuacion'   => 'required|integer|min:1|max:5',
            'comentario'   => 'nullable|string|max:500',
        ]);

        $rating = Rating::create($validated);

        return response()->json([
            'success' => true,
            'data' => $rating,
            'message' => 'Rating creado correctamente.',
        ], 201);
    }

    public function show($id)
    {
        $rating = Rating::with(['usuario', 'direcciones'])
            ->where('id_user', auth()->id())
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $rating,
            'message' => '',
        ]);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'puntuacion' => 'sometimes|integer|min:1|max:5',
            'comentario' => 'nullable|string|max:500',
        ]);

        // Verificar propiedad antes de actualizar (IDOR fix)
        $rating = Rating::where('id_user', auth()->id())->findOrFail($id);
        $rating->update($validated);

        return response()->json([
            'success' => true,
            'data' => $rating,
            'message' => 'Rating actualizado correctamente.',
        ]);
    }

    public function destroy($id)
    {
        // Verificar propiedad antes de eliminar (IDOR fix)
        Rating::where('id_user', auth()->id())->findOrFail($id)->delete();

        return response()->json([
            'success' => true,
            'data' => null,
            'message' => 'Rating eliminado correctamente.',
        ]);
    }
}
