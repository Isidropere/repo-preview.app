<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Provincia;
use App\Models\Municipio;
use App\Models\ZonaNoContempladaRequest;

class AdminDeliveryZonasController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $provincias = Provincia::with('municipios')->orderBy('provincia')->get();
        $peticionesNoCubiertas = ZonaNoContempladaRequest::with('user')->orderBy('created_at', 'desc')->get();

        return view('admin.delivery.zonas', compact('provincias', 'peticionesNoCubiertas'));
    }

    /**
     * Toggle the active status of a province.
     */
    public function toggleProvincia(Request $request, $id)
    {
        $provincia = Provincia::findOrFail($id);
        $provincia->activo_entrega = !$provincia->activo_entrega;
        $provincia->save();

        return response()->json([
            'success' => true,
            'message' => 'El estado de la provincia se ha actualizado correctamente.',
            'activo' => $provincia->activo_entrega
        ]);
    }

    /**
     * Toggle the active status of a municipality.
     */
    public function toggleMunicipio(Request $request, $id)
    {
        $municipio = Municipio::findOrFail($id);
        $municipio->activo_entrega = !$municipio->activo_entrega;
        $municipio->save();

        return response()->json([
            'success' => true,
            'message' => 'El estado del municipio se ha actualizado correctamente.',
            'activo' => $municipio->activo_entrega
        ]);
    }
}
