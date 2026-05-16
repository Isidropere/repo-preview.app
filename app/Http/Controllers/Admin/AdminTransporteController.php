<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SolicitudTransporte;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Events\NuevaNotificacion;
use Illuminate\Support\Facades\DB;

class AdminTransporteController extends Controller
{
    /**
     * Muestra el listado de solicitudes de transporte.
     */
    public function index()
    {
        $solicitudes = SolicitudTransporte::orderBy('created_at', 'desc')->paginate(20);
        return view('admin.transporte.index', compact('solicitudes'));
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
        $solicitud = SolicitudTransporte::findOrFail($id);
        $pdf = Pdf::loadView('admin.transporte.pdf', compact('solicitud'));
        
        return $pdf->download('solicitud_transporte_' . $solicitud->id . '.pdf');
    }
}
