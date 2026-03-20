<?php

namespace App\Http\Controllers;

use App\Models\Provincia;
use Illuminate\Http\Request;

class ProvinciaController extends Controller
{
    public function getProvincias()
    {
        $provincias = Provincia::all();

        if ($provincias->isEmpty()) {
            return response()->json([
                'success' => false,
                'data' => [],
                'message' => 'No se encontraron provincias.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $provincias->map(fn($p) => ['id' => $p->id_provincia, 'nombre' => $p->provincia]),
            'message' => '',
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'id_provincia' => 'required|string|max:10|unique:provincias,id_provincia',
            'provincia'    => 'required|string|max:100',
        ]);

        $provincia = Provincia::create($data);

        return response()->json([
            'success' => true,
            'data' => $provincia,
            'message' => 'Provincia creada correctamente.',
        ], 201);
    }

    public function show(string $id)
    {
        return response()->json([
            'success' => true,
            'data' => Provincia::with('municipios')->findOrFail($id),
            'message' => '',
        ]);
    }

    public function update(Request $request, string $id)
    {
        $data = $request->validate([
            'provincia' => 'sometimes|string|max:100',
        ]);

        $provincia = Provincia::findOrFail($id);
        $provincia->update($data);

        return response()->json([
            'success' => true,
            'data' => $provincia,
            'message' => 'Provincia actualizada correctamente.',
        ]);
    }

    public function destroy(string $id)
    {
        Provincia::findOrFail($id)->delete();

        return response()->json([
            'success' => true,
            'data' => null,
            'message' => 'Provincia eliminada correctamente.',
        ]);
    }
}
