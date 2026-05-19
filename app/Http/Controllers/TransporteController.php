<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SolicitudTransporte;
use App\Models\TransporteArticulo;
use Illuminate\Support\Facades\Auth;

class TransporteController extends Controller
{
    /**
     * Muestra el formulario para crear una solicitud de transporte/mudanza.
     */
    public function create()
    {
        $articulos = TransporteArticulo::where('estatus', true)->orderBy('nombre', 'asc')->get();
        return view('transporte.create', compact('articulos'));
    }

    /**
     * Guarda la solicitud de transporte/mudanza.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'tipo_servicio' => 'required|in:transporte,mudanza',
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

        $solicitud = SolicitudTransporte::create($validated);

        // Procesar y asociar artículos del checklist
        if ($request->has('articulos') && is_array($request->articulos)) {
            $syncData = [];
            foreach ($request->articulos as $articuloId => $value) {
                // Si el checkbox está marcado
                if ($value == '1' || $value === 'on' || $value === true) {
                    $cantidad = $request->input("cantidades.{$articuloId}", 1);
                    $syncData[$articuloId] = ['cantidad' => max(1, intval($cantidad))];
                }
            }
            if (!empty($syncData)) {
                $solicitud->articulos()->attach($syncData);
            }
        }

        return redirect()->route('home')->with('success', '¡Tu solicitud de transporte y mudanza ha sido enviada con éxito! Nos pondremos en contacto contigo pronto.');
    }
}

