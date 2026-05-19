<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\HojaVida;
use Illuminate\Http\Request;

/**
 * HojaVidaApiController — Perfil profesional (requerido para publicar talentos)
 */
class HojaVidaApiController extends Controller
{
    /** GET /api/hoja-vida */
    public function show(Request $request)
    {
        $hoja = HojaVida::where('id_user', $request->user()->id)->first();

        if (!$hoja) {
            return response()->json(['hoja_vida' => null, 'tiene_hoja_vida' => false]);
        }

        return response()->json(['hoja_vida' => $hoja, 'tiene_hoja_vida' => true]);
    }

    /** POST /api/hoja-vida — crear o actualizar */
    public function store(Request $request)
    {
        $data = $request->validate([
            'titulo_profesional' => 'required|string|max:150',
            'habilidades'        => 'nullable|string',
            'experiencia'        => 'nullable|string',
            'descripcion'        => 'nullable|string',
            'años_experiencia'   => 'nullable|integer|min:0|max:60',
        ]);

        $data['id_user'] = $request->user()->id;

        $hoja = HojaVida::updateOrCreate(
            ['id_user' => $request->user()->id],
            $data
        );

        return response()->json([
            'message'   => 'Hoja de vida guardada correctamente.',
            'hoja_vida' => $hoja,
        ]);
    }
}
