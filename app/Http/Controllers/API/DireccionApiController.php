<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Direcciones;
use App\Models\Provincia;
use App\Models\Municipio;
use Illuminate\Http\Request;

/**
 * DireccionApiController — Gestión de direcciones del usuario para la app móvil
 */
class DireccionApiController extends Controller
{
    /** GET /api/direcciones */
    public function index(Request $request)
    {
        $direcciones = Direcciones::with(['provincia:id_provincia,provincia', 'municipio:id_municipio,municipio'])
            ->where('id_user', $request->user()->id)
            ->get();

        return response()->json($direcciones);
    }

    /** POST /api/direcciones */
    public function store(Request $request)
    {
        $data = $request->validate([
            'calle'              => 'required|string|max:255',
            'N_casa_edificio'    => 'nullable|string|max:50',
            'apto'               => 'nullable|string|max:50',
            'sector'             => 'nullable|string|max:100',
            'id_provincia'       => 'required|integer|exists:provincias,id_provincia',
            'id_municipio'       => 'required|integer|exists:municipios,id_municipio',
            'telefono_contacto'  => 'nullable|string|max:20',
            'es_predeterminada'  => 'boolean',
        ]);

        $data['id_user'] = $request->user()->id;

        // Si marca como predeterminada, quitar predeterminada a las demás
        if (!empty($data['es_predeterminada'])) {
            Direcciones::where('id_user', $request->user()->id)
                ->update(['es_predeterminada' => 0]);
        }

        // Generar ID manual ya que el modelo no usa auto-increment
        $maxId = Direcciones::max('id_direccion') ?? 0;
        $data['id_direccion'] = $maxId + 1;

        $direccion = Direcciones::create($data);

        return response()->json($direccion->load(['provincia:id_provincia,provincia', 'municipio:id_municipio,municipio']), 201);
    }

    /** PUT /api/direcciones/{id} */
    public function update(Request $request, int $id)
    {
        $direccion = Direcciones::where('id_user', $request->user()->id)
            ->where('id_direccion', $id)
            ->firstOrFail();

        $data = $request->validate([
            'calle'             => 'sometimes|string|max:255',
            'N_casa_edificio'   => 'nullable|string|max:50',
            'apto'              => 'nullable|string|max:50',
            'sector'            => 'nullable|string|max:100',
            'id_provincia'      => 'sometimes|integer|exists:provincias,id_provincia',
            'id_municipio'      => 'sometimes|integer|exists:municipios,id_municipio',
            'telefono_contacto' => 'nullable|string|max:20',
            'es_predeterminada' => 'boolean',
        ]);

        if (!empty($data['es_predeterminada'])) {
            Direcciones::where('id_user', $request->user()->id)
                ->update(['es_predeterminada' => 0]);
        }

        $direccion->update($data);

        return response()->json($direccion->load(['provincia:id_provincia,provincia', 'municipio:id_municipio,municipio']));
    }

    /** DELETE /api/direcciones/{id} */
    public function destroy(Request $request, int $id)
    {
        Direcciones::where('id_user', $request->user()->id)
            ->where('id_direccion', $id)
            ->delete();

        return response()->json(['message' => 'Dirección eliminada.']);
    }

    /** GET /api/ubicacion/provincias */
    public function provincias()
    {
        return response()->json(
            Provincia::select('id_provincia', 'provincia')->orderBy('provincia')->get()
        );
    }

    /** GET /api/ubicacion/municipios/{id_provincia} */
    public function municipios(int $idProvincia)
    {
        return response()->json(
            Municipio::select('id_municipio', 'municipio')
                ->where('id_provincia', $idProvincia)
                ->orderBy('municipio')
                ->get()
        );
    }
}
