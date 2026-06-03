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

        return response()->json([
            'hoja_vida' => $this->formatHoja($hoja),
            'tiene_hoja_vida' => true
        ]);
    }

    /** POST /api/hoja-vida — crear o actualizar */
    public function store(Request $request)
    {
        $request->validate([
            'titulo_profesional' => 'required|string|max:150',
            'habilidades'        => 'nullable|string',
            'experiencia'        => 'nullable|string',
            'descripcion'        => 'nullable|string',
            'años_experiencia'   => 'nullable|integer|min:0|max:60',
        ]);

        $user = $request->user();

        $data = [
            'nombres'            => $user->nombres ?? 'Usuario',
            'apellidos'          => $user->apellidos ?? '',
            'titulo_profesional' => $request->input('titulo_profesional'),
            'descripcion_bio'    => $request->input('descripcion') ?? '',
            'habilidades'        => $request->input('habilidades') ?? '',
            'experiencia'        => $request->input('experiencia') ?? '',
            'ubicacion'          => 'Santo Domingo, RD',
        ];

        $hoja = HojaVida::updateOrCreate(
            ['id_user' => $user->id],
            $data
        );

        return response()->json([
            'message'   => 'Hoja de vida guardada correctamente.',
            'hoja_vida' => $this->formatHoja($hoja),
        ]);
    }

    private function formatHoja(HojaVida $hoja): array
    {
        $arr = $hoja->toArray();
        $arr['descripcion'] = $hoja->descripcion_bio;
        $arr['años_experiencia'] = 0; // Dummy or default
        return $arr;
    }
}
