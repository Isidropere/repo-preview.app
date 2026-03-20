<?php

namespace App\Http\Controllers;

use App\Models\Oferta;
use Illuminate\Http\Request;

class OfertaController extends Controller
{
    public function index()
    {
        $userId = auth()->id();
        return response()->json([
            'success' => true,
            'data' => Oferta::with(['oferente', 'beneficiario', 'paquete.itemsOferta.item'])
                ->where(fn($q) => $q->where('id_oferente', $userId)->orWhere('id_beneficiario', $userId))
                ->get(),
            'message' => '',
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_oferente'     => 'required|integer|exists:users,id',
            'id_beneficiario' => 'required|integer|exists:users,id',
            'id_paquete'      => 'required|integer|exists:paquetes,id_paquete',
            'estatus'         => 'nullable|integer|in:0,1,2',
            'fecha'           => 'nullable|date',
        ]);

        $validated['fecha'] = $validated['fecha'] ?? now();
        $oferta = Oferta::create($validated);

        return response()->json([
            'success' => true,
            'data' => $oferta,
            'message' => 'Oferta creada correctamente.',
        ], 201);
    }

    public function show($id)
    {
        $userId = auth()->id();
        $oferta = Oferta::with(['oferente', 'beneficiario', 'paquete.itemsOferta.item'])
            ->where(fn($q) => $q->where('id_oferente', $userId)->orWhere('id_beneficiario', $userId))
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $oferta,
            'message' => '',
        ]);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'estatus' => 'sometimes|integer|in:0,1,2',
        ]);

        $userId = auth()->id();
        $oferta = Oferta::where(fn($q) => $q->where('id_oferente', $userId)->orWhere('id_beneficiario', $userId))
            ->findOrFail($id);
        $oferta->update($validated);

        return response()->json([
            'success' => true,
            'data' => $oferta,
            'message' => 'Oferta actualizada correctamente.',
        ]);
    }

    public function destroy($id)
    {
        $userId = auth()->id();
        Oferta::where('id_oferente', $userId)->findOrFail($id)->delete();

        return response()->json([
            'success' => true,
            'data' => null,
            'message' => 'Oferta eliminada correctamente.',
        ]);
    }
}
