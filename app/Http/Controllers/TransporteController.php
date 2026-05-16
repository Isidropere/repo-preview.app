<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SolicitudTransporte;
use Illuminate\Support\Facades\Auth;

class TransporteController extends Controller
{
    /**
     * Muestra el formulario para crear una solicitud de transporte/mudanza.
     */
    public function create()
    {
        return view('transporte.create');
    }

    /**
     * Guarda la solicitud de transporte/mudanza.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'apellido' => 'required|string|max:255',
            'cedula' => 'required|string|max:20',
            'direccion' => 'required|string|max:500',
            'telefono' => 'required|string|max:20',
            'correo' => 'required|email|max:255',
            'fecha_servicio' => 'required|date',
            'ubicacion_geologica' => 'nullable|string|max:255',
            'dimensiones_carga' => 'required|string|max:1000',
        ]);

        if (Auth::check()) {
            $validated['id_usuario'] = Auth::id();
        }

        $validated['estado'] = 'pendiente';

        SolicitudTransporte::create($validated);

        return redirect()->route('home')->with('success', '¡Tu solicitud de transporte y mudanza ha sido enviada con éxito! Nos pondremos en contacto contigo pronto.');
    }
}
