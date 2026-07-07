<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SolicitudTransporte;
use App\Models\TransporteArticulo;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Events\NuevaNotificacion;
use Illuminate\Support\Facades\DB;

class AdminTransporteController extends Controller
{
    /**
     * Muestra el listado de solicitudes de transporte.
     */
    public function index(Request $request)
    {
        $query = SolicitudTransporte::with('articulos')->orderBy('created_at', 'desc');

        // Filtro por Estado
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        // Filtro por Rango de Fechas de Servicio
        if ($request->filled('desde')) {
            $query->whereDate('fecha_servicio', '>=', $request->desde);
        }
        if ($request->filled('hasta')) {
            $query->whereDate('fecha_servicio', '<=', $request->hasta);
        }

        // Búsqueda General (Nombre, Correo, Cédula, Teléfono)
        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
            $query->where(function($q) use ($buscar) {
                $q->where('nombre', 'LIKE', "%{$buscar}%")
                  ->orWhere('apellido', 'LIKE', "%{$buscar}%")
                  ->orWhere('correo', 'LIKE', "%{$buscar}%")
                  ->orWhere('cedula', 'LIKE', "%{$buscar}%")
                  ->orWhere('telefono', 'LIKE', "%{$buscar}%")
                  ->orWhere('id', $buscar);
            });
        }

        $solicitudes = $query->paginate(20)->appends($request->all());
        
        // Obtener el catálogo completo de artículos para la pestaña de gestión del admin
        $articulos = TransporteArticulo::orderBy('nombre', 'asc')->get();

        // Obtener configuraciones globales
        $config = [
            'precio_km_transporte' => \App\Models\TransporteConfiguracion::get('precio_km_transporte', 50),
            'precio_km_mudanza' => \App\Models\TransporteConfiguracion::get('precio_km_mudanza', 100),
            'limite_articulos_mudanza' => \App\Models\TransporteConfiguracion::get('limite_articulos_mudanza', 5),
        ];

        return view('admin.transporte.index', compact('solicitudes', 'articulos', 'config'));
    }

    /**
     * Actualiza las configuraciones globales de transporte.
     */
    public function updateConfig(Request $request)
    {
        $request->validate([
            'precio_km_transporte' => 'required|numeric|min:0',
            'precio_km_mudanza' => 'required|numeric|min:0',
            'limite_articulos_mudanza' => 'required|integer|min:0',
        ]);

        \App\Models\TransporteConfiguracion::set('precio_km_transporte', $request->precio_km_transporte);
        \App\Models\TransporteConfiguracion::set('precio_km_mudanza', $request->precio_km_mudanza);
        \App\Models\TransporteConfiguracion::set('limite_articulos_mudanza', $request->limite_articulos_mudanza);

        return back()->with('success', '¡Configuraciones globales actualizadas con éxito!');
    }

    /**
     * Aprueba la solicitud y notifica al usuario si aplica.
     */
    public function aprobar($id)
    {
        $solicitud = SolicitudTransporte::findOrFail($id);
        $solicitud->estado = 'aprobada';
        $solicitud->save();

        if ($solicitud->id_usuario) {
            $mensaje = "Tu solicitud de transporte y mudanza para el día {$solicitud->fecha_servicio->format('d/m/Y')} ha sido APROBADA.";
            
            DB::table('notificaciones')->insert([
                'id_usuario' => $solicitud->id_usuario,
                'mensaje' => $mensaje,
                'leida' => 0,
                'fecha_envio' => now()
            ]);
            
            event(new NuevaNotificacion($mensaje, $solicitud->id_usuario));

            return back()->with('success', 'Solicitud aprobada y notificación enviada al usuario.');
        } else {
            return back()->with('warning', 'Solicitud aprobada. Recuerde contactar manualmente al cliente (Tel: ' . $solicitud->telefono . ' / Correo: ' . $solicitud->correo . ').');
        }
    }

    /**
     * Rechaza la solicitud y notifica al usuario si aplica.
     */
    public function rechazar($id)
    {
        $solicitud = SolicitudTransporte::findOrFail($id);
        $solicitud->estado = 'rechazada';
        $solicitud->save();

        if ($solicitud->id_usuario) {
            $mensaje = "Tu solicitud de transporte y mudanza para el día {$solicitud->fecha_servicio->format('d/m/Y')} ha sido RECHAZADA.";
            
            DB::table('notificaciones')->insert([
                'id_usuario' => $solicitud->id_usuario,
                'mensaje' => $mensaje,
                'leida' => 0,
                'fecha_envio' => now()
            ]);
            
            event(new NuevaNotificacion($mensaje, $solicitud->id_usuario));

            return back()->with('success', 'Solicitud rechazada y notificación enviada al usuario.');
        } else {
            return back()->with('warning', 'Solicitud rechazada. Recuerde contactar manualmente al cliente (Tel: ' . $solicitud->telefono . ' / Correo: ' . $solicitud->correo . ').');
        }
    }

    /**
     * Genera el PDF de la solicitud.
     */
    public function generarPdf($id)
    {
        $solicitud = SolicitudTransporte::with('articulos')->findOrFail($id);
        $pdf = Pdf::loadView('admin.transporte.pdf', compact('solicitud'));
        
        return $pdf->download('solicitud_transporte_' . $solicitud->id . '.pdf');
    }

    /**
     * Guarda un nuevo artículo en el catálogo.
     */
    public function storeArticulo(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'categoria' => 'required|in:transporte,mudanza,ambos',
            'precio_base' => 'nullable|numeric|min:0',
            'precio_pequeno' => 'nullable|numeric|min:0',
            'precio_mediano' => 'nullable|numeric|min:0',
            'precio_grande' => 'nullable|numeric|min:0',
        ]);

        TransporteArticulo::create([
            'nombre' => $request->nombre,
            'categoria' => $request->categoria,
            'precio_base' => $request->precio_base ?? 0,
            'precio_pequeno' => $request->precio_pequeno ?? 0,
            'precio_mediano' => $request->precio_mediano ?? 0,
            'precio_grande' => $request->precio_grande ?? 0,
            'estatus' => true,
        ]);

        return back()->with('success', '¡Artículo agregado con éxito al catálogo de transporte!');
    }

    /**
     * Actualiza un artículo del catálogo.
     */
    public function updateArticulo(Request $request, $id)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'categoria' => 'required|in:transporte,mudanza,ambos',
            'precio_base' => 'nullable|numeric|min:0',
            'precio_pequeno' => 'nullable|numeric|min:0',
            'precio_mediano' => 'nullable|numeric|min:0',
            'precio_grande' => 'nullable|numeric|min:0',
            'estatus' => 'required|boolean',
        ]);

        $articulo = TransporteArticulo::findOrFail($id);
        $articulo->update([
            'nombre' => $request->nombre,
            'categoria' => $request->categoria,
            'precio_base' => $request->precio_base ?? 0,
            'precio_pequeno' => $request->precio_pequeno ?? 0,
            'precio_mediano' => $request->precio_mediano ?? 0,
            'precio_grande' => $request->precio_grande ?? 0,
            'estatus' => $request->estatus,
        ]);

        return back()->with('success', '¡Artículo actualizado con éxito!');
    }

    /**
     * Elimina un artículo del catálogo.
     */
    public function destroyArticulo($id)
    {
        $articulo = TransporteArticulo::findOrFail($id);
        $articulo->delete();

        return back()->with('success', '¡Artículo eliminado con éxito del catálogo!');
    }
}

