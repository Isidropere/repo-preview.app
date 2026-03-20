<?php

namespace App\Http\Controllers;

use App\Models\Municipio;
use Illuminate\Http\Request;

class MunicipioController extends Controller
{
    public function getMunicipio(Request $request)
    {
        $request->validate([
            'id_provincia' => 'required|string|exists:provincias,id_provincia',
        ]);

        $municipios = Municipio::where('id_provincia', $request->query('id_provincia'))->get();

        return response()->json([
            'success' => true,
            'data' => $municipios->map(fn($m) => ['id' => $m->id_municipio, 'nombre' => $m->municipio]),
            'message' => '',
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'id_municipio' => 'required|string|max:10|unique:municipios,id_municipio',
            'municipio'    => 'required|string|max:100',
            'id_provincia' => 'required|string|exists:provincias,id_provincia',
        ]);

        $municipio = Municipio::create($data);

        return response()->json([
            'success' => true,
            'data' => $municipio,
            'message' => 'Municipio creado correctamente.',
        ], 201);
    }

    public function show(string $id)
    {
        return response()->json([
            'success' => true,
            'data' => Municipio::with(['provincia', 'distritosMunicipales'])->findOrFail($id),
            'message' => '',
        ]);
    }

    public function update(Request $request, string $id)
    {
        $data = $request->validate([
            'municipio'    => 'sometimes|string|max:100',
            'id_provincia' => 'sometimes|string|exists:provincias,id_provincia',
        ]);

        $municipio = Municipio::findOrFail($id);
        $municipio->update($data);

        return response()->json([
            'success' => true,
            'data' => $municipio,
            'message' => 'Municipio actualizado correctamente.',
        ]);
    }

    public function destroy(string $id)
    {
        Municipio::findOrFail($id)->delete();

        return response()->json([
            'success' => true,
            'data' => null,
            'message' => 'Municipio eliminado correctamente.',
        ]);
    }
}
