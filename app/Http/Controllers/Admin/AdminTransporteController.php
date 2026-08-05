<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SolicitudTransporte;
use App\Models\TransporteArticulo;
use App\Models\TransporteCamion;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Events\NuevaNotificacion;
use Illuminate\Support\Facades\DB;
use App\Models\Message;

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
        
        // Paginación y Filtrado del catálogo de artículos para la pestaña de gestión del admin
        $articulosQuery = TransporteArticulo::orderBy('nombre', 'asc');
        
        if ($request->filled('buscar_articulo')) {
            $articulosQuery->where('nombre', 'LIKE', '%' . $request->buscar_articulo . '%');
        }

        $articulos = $articulosQuery->paginate(20, ['*'], 'page_articulos')->appends($request->all());

        // Obtener configuraciones globales
        $config = [
            'precio_km_transporte' => \App\Models\TransporteConfiguracion::get('precio_km_transporte', 50),
            'precio_km_mudanza' => \App\Models\TransporteConfiguracion::get('precio_km_mudanza', 100),
            'limite_articulos_mudanza' => \App\Models\TransporteConfiguracion::get('limite_articulos_mudanza', 5),
            'latitud_base' => \App\Models\TransporteConfiguracion::get('latitud_base', 18.4861),
            'longitud_base' => \App\Models\TransporteConfiguracion::get('longitud_base', -69.9312),
            'precio_camion_pequeno' => \App\Models\TransporteConfiguracion::get('precio_camion_pequeno', 1500),
            'precio_camion_mediano' => \App\Models\TransporteConfiguracion::get('precio_camion_mediano', 3000),
            'precio_camion_grande' => \App\Models\TransporteConfiguracion::get('precio_camion_grande', 5000),
            'precio_por_persona' => \App\Models\TransporteConfiguracion::get('precio_por_persona', 500),
            'precio_km_operacion' => \App\Models\TransporteConfiguracion::get('precio_km_operacion', 50),
            'peso_base_maximo' => \App\Models\TransporteConfiguracion::get('peso_base_maximo', 40),
            'intervalo_peso_extra' => \App\Models\TransporteConfiguracion::get('intervalo_peso_extra', 20),
            'precio_peso_extra' => \App\Models\TransporteConfiguracion::get('precio_peso_extra', 50),
        ];

        // Determinar pestaña activa por defecto
        $activeTab = 'solicitudes';
        if ($request->has('page_articulos') || $request->has('buscar_articulo')) {
            $activeTab = 'catalogo';
        }

        $camiones = TransporteCamion::orderBy('medida_pies', 'asc')->get();

        return view('admin.transporte.index', compact('solicitudes', 'articulos', 'config', 'activeTab', 'camiones'));
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
            'latitud_base' => 'required|numeric',
            'longitud_base' => 'required|numeric',
            'precio_camion_pequeno' => 'required|numeric|min:0',
            'precio_camion_mediano' => 'required|numeric|min:0',
            'precio_camion_grande' => 'required|numeric|min:0',
            'precio_por_persona' => 'required|numeric|min:0',
            'precio_km_operacion' => 'required|numeric|min:0',
            'peso_base_maximo' => 'required|numeric|min:0',
            'intervalo_peso_extra' => 'required|numeric|min:1',
            'precio_peso_extra' => 'required|numeric|min:0',
        ]);

        try {
            \App\Models\TransporteConfiguracion::set('precio_km_transporte', $request->precio_km_transporte);
            \App\Models\TransporteConfiguracion::set('precio_km_mudanza', $request->precio_km_mudanza);
            \App\Models\TransporteConfiguracion::set('limite_articulos_mudanza', $request->limite_articulos_mudanza);
            \App\Models\TransporteConfiguracion::set('latitud_base', $request->latitud_base);
            \App\Models\TransporteConfiguracion::set('longitud_base', $request->longitud_base);
            \App\Models\TransporteConfiguracion::set('precio_camion_pequeno', $request->precio_camion_pequeno);
            \App\Models\TransporteConfiguracion::set('precio_camion_mediano', $request->precio_camion_mediano);
            \App\Models\TransporteConfiguracion::set('precio_camion_grande', $request->precio_camion_grande);
            \App\Models\TransporteConfiguracion::set('precio_por_persona', $request->precio_por_persona);
            \App\Models\TransporteConfiguracion::set('precio_km_operacion', $request->precio_km_operacion);
            \App\Models\TransporteConfiguracion::set('peso_base_maximo', $request->peso_base_maximo);
            \App\Models\TransporteConfiguracion::set('intervalo_peso_extra', $request->intervalo_peso_extra);
            \App\Models\TransporteConfiguracion::set('precio_peso_extra', $request->precio_peso_extra);

            return redirect()->route('admin.erp.transporte.index')->with('success', '¡Configuraciones globales actualizadas con éxito!');
        } catch (\Exception $e) {
            return redirect()->route('admin.erp.transporte.index')->withErrors(['error' => 'Error al guardar la configuración: ' . $e->getMessage()]);
        }
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
            
            Message::create([
                'id_emisor'   => null,
                'id_receptor' => $solicitud->id_usuario,
                'mensaje'     => $mensaje,
                'leido'       => 0,
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
            
            Message::create([
                'id_emisor'   => null,
                'id_receptor' => $solicitud->id_usuario,
                'mensaje'     => $mensaje,
                'leido'       => 0,
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

    /**
     * Agrega un nuevo camión a la configuración
     */
    public function storeCamion(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'medida_pies' => 'required|numeric|min:0',
            'precio_base' => 'required|numeric|min:0',
        ]);

        TransporteCamion::create([
            'nombre' => $request->nombre,
            'medida_pies' => $request->medida_pies,
            'precio_base' => $request->precio_base,
            'activo' => $request->has('activo'),
        ]);

        return back()->with('success', '¡Camión creado con éxito!');
    }

    /**
     * Actualiza un camión existente
     */
    public function updateCamion(Request $request, $id)
    {
        $camion = TransporteCamion::findOrFail($id);
        
        $request->validate([
            'nombre' => 'required|string|max:255',
            'medida_pies' => 'required|numeric|min:0',
            'precio_base' => 'required|numeric|min:0',
        ]);

        $camion->update([
            'nombre' => $request->nombre,
            'medida_pies' => $request->medida_pies,
            'precio_base' => $request->precio_base,
            'activo' => $request->has('activo'),
        ]);

        return back()->with('success', '¡Camión actualizado con éxito!');
    }

    /**
     * Elimina un camión
     */
    public function destroyCamion($id)
    {
        $camion = TransporteCamion::findOrFail($id);
        $camion->delete();

        return back()->with('success', '¡Camión eliminado con éxito!');
    }
}

