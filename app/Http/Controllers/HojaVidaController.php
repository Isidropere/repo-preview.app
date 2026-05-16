<?php

namespace App\Http\Controllers;

use App\Models\HojaVida;
use Illuminate\Http\Request;

class HojaVidaController extends Controller
{
    public function form()
    {
        $hojaVida = HojaVida::where('id_user', auth()->id())->first();

        // Pre-llenar con datos del usuario si no existe
        if (!$hojaVida) {
            $user = auth()->user();
            $hojaVida = new HojaVida([
                'nombres'  => $user->nombres,
                'apellidos' => $user->apellidos,
            ]);
        }

        $esEdicion = $hojaVida->exists;

        // Preservar redirect_after si viene en la sesión
        $redirectAfter = session('redirect_after');
        if ($redirectAfter) {
            session()->keep(['redirect_after']);
        }

        return view('hoja-vida.form', compact('hojaVida', 'esEdicion', 'redirectAfter'));
    }

    public function save(Request $request)
    {
        $validated = $request->validate([
            'nombres'             => 'required|string|max:100',
            'apellidos'           => 'required|string|max:100',
            'titulo_profesional'  => 'required|string|max:150',
            'descripcion_bio'     => 'required|string|max:2000',
            'habilidades'         => 'required|string|max:2000',
            'experiencia'         => 'required|string|max:2000',
            'ubicacion'           => 'required|string|max:200',
        ], [
            'nombres.required'            => 'El nombre es obligatorio.',
            'apellidos.required'          => 'Los apellidos son obligatorios.',
            'titulo_profesional.required' => 'El titulo profesional es obligatorio.',
            'descripcion_bio.required'    => 'La descripcion es obligatoria.',
            'habilidades.required'        => 'Las habilidades son obligatorias.',
            'experiencia.required'        => 'La experiencia es obligatoria.',
            'ubicacion.required'          => 'La ubicacion es obligatoria.',
        ]);

        HojaVida::updateOrCreate(
            ['id_user' => auth()->id()],
            $validated
        );

        $redirectRoute = $request->input('redirect_after') ?? session('redirect_after');

        if ($redirectRoute && \Illuminate\Support\Facades\Route::has($redirectRoute)) {
            return redirect()->route($redirectRoute)
                ->with('success', 'Hoja de vida guardada. ¡Ahora puedes publicar tu talento!');
        }

        return redirect()->route('hoja-vida.form')->with('success', 'Hoja de vida guardada exitosamente.');
    }
}
