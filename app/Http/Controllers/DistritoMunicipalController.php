<?php

namespace App\Http\Controllers;

use App\Models\DistritoMunicipal;
use Illuminate\Http\Request;

class DistritoMunicipalController extends Controller
{
    public function index()
    {
        return response()->json([
            'success' => true,
            'data' => DistritoMunicipal::with('municipio')->get(),
            'message' => '',
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'id_distmunicipal'   => 'required|string|max:10|unique:distritos_municipales,id_distmunicipal',
            'distrito_municipal' => 'required|string|max:150',
            'id_municipio'       => 'required|string|exists:municipios,id_municipio',
        ]);

        $distrito = DistritoMunicipal::create($data);

        return response()->json([
            'success' => true,
            'data' => $distrito,
            'message' => 'Distrito municipal creado correctamente.',
        ], 201);
    }

    public function show(string $id)
    {
        return response()->json([
            'success' => true,
            'data' => DistritoMunicipal::with('municipio')->findOrFail($id),
            'message' => '',
        ]);
    }

    public function update(Request $request, string $id)
    {
        $data = $request->validate([
            'distrito_municipal' => 'sometimes|string|max:150',
            'id_municipio'       => 'sometimes|string|exists:municipios,id_municipio',
        ]);

        $distrito = DistritoMunicipal::findOrFail($id);
        $distrito->update($data);

        return response()->json([
            'success' => true,
            'data' => $distrito,
            'message' => 'Distrito municipal actualizado correctamente.',
        ]);
    }

    public function destroy(string $id)
    {
        DistritoMunicipal::findOrFail($id)->delete();

        return response()->json([
            'success' => true,
            'data' => null,
            'message' => 'Distrito municipal eliminado correctamente.',
        ]);
    }
}
