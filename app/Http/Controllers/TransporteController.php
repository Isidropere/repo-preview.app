<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SolicitudTransporte;
use App\Models\TransporteArticulo;
use App\Models\TransporteCamion;
use Illuminate\Support\Facades\Auth;

class TransporteController extends Controller
{
    /**
     * Muestra el formulario para crear una solicitud de transporte/mudanza.
     */
    public function create()
    {
        $articulos = TransporteArticulo::where('estatus', true)->orderBy('nombre', 'asc')->get();
        
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

        $camiones = TransporteCamion::where('activo', true)->orderBy('medida_pies', 'asc')->get();

        return view('transporte.create', compact('articulos', 'config', 'camiones'));
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
            'telefono' => 'required|string|max:20',
            'correo' => 'required|email|max:255',
            'fecha_servicio' => 'required|date',
            'punto_recogida' => 'nullable|string|max:255',
            'punto_recogida_address' => 'nullable|string|max:500',
            'piso_origen' => 'nullable|string|max:50',
            'punto_entrega' => 'nullable|string|max:255',
            'punto_entrega_address' => 'nullable|string|max:500',
            'piso_destino' => 'nullable|string|max:50',
            'distancia_km' => 'nullable|numeric',
            'precio_estimado_total' => 'nullable|numeric',
            'dimensiones_carga' => 'nullable|string|max:1000',
            'camion_tamano' => 'nullable|string|max:150',
            'cantidad_personas' => 'nullable|integer|min:1',
            'cantidad_productos_transporte' => 'nullable|integer|min:1',
            'peso_carga' => 'nullable|numeric|min:0',
        ]);

        if (Auth::check()) {
            $validated['id_usuario'] = Auth::id();
        }

        $validated['direccion'] = $validated['punto_recogida_address'] ?? 'No especificada';
        $validated['dimensiones_carga'] = $validated['dimensiones_carga'] ?? 'No especificadas';

        // Variables de Configuración
        $precioKmTrans = \App\Models\TransporteConfiguracion::get('precio_km_transporte', 50);
        $precioKmMudz = \App\Models\TransporteConfiguracion::get('precio_km_mudanza', 100);
        $limiteMudz = \App\Models\TransporteConfiguracion::get('limite_articulos_mudanza', 5);
        $latBase = \App\Models\TransporteConfiguracion::get('latitud_base', 18.4861);
        $lngBase = \App\Models\TransporteConfiguracion::get('longitud_base', -69.9312);
        $precioCamionPeq = \App\Models\TransporteConfiguracion::get('precio_camion_pequeno', 1500);
        $precioCamionMed = \App\Models\TransporteConfiguracion::get('precio_camion_mediano', 3000);
        $precioCamionGra = \App\Models\TransporteConfiguracion::get('precio_camion_grande', 5000);
        $precioPersona = \App\Models\TransporteConfiguracion::get('precio_por_persona', 500);
        $precioKmOperacion = \App\Models\TransporteConfiguracion::get('precio_km_operacion', 50);
        $pesoBaseMaximo = \App\Models\TransporteConfiguracion::get('peso_base_maximo', 40);
        $intervaloPesoExtra = \App\Models\TransporteConfiguracion::get('intervalo_peso_extra', 20);
        $precioPesoExtra = \App\Models\TransporteConfiguracion::get('precio_peso_extra', 50);

        // Calcular Distancias
        $distanciaAB = floatval($request->input('distancia_km', 0));
        $distanciaBaseA = 0;

        if (!empty($validated['punto_recogida'])) {
            $coordsA = explode(',', $validated['punto_recogida']);
            if (count($coordsA) == 2) {
                $distanciaBaseA = $this->calcCrow(floatval($latBase), floatval($lngBase), floatval(trim($coordsA[0])), floatval(trim($coordsA[1])));
            }
        }
        $validated['distancia_base_a_origen'] = $distanciaBaseA;

        // Validar conversión automática de Transporte a Mudanza
        if ($validated['tipo_servicio'] === 'transporte') {
            $cantProductos = intval($validated['cantidad_productos_transporte'] ?? 0);
            if ($cantProductos > $limiteMudz) {
                $validated['tipo_servicio'] = 'mudanza';
                // Asumimos un camión mediano y 2 personas por defecto si se convierte forzosamente por backend
                $validated['camion_tamano'] = 'mediano';
                $validated['cantidad_personas'] = 2;
            }
        }

        // Calcular Precio Final
        $precioEstimadoFinal = 0;
        if ($validated['tipo_servicio'] === 'mudanza') {
            $camionPrice = 0;
            if ($validated['camion_tamano'] === 'pequeno') $camionPrice = $precioCamionPeq;
            elseif ($validated['camion_tamano'] === 'mediano') $camionPrice = $precioCamionMed;
            elseif ($validated['camion_tamano'] === 'grande') $camionPrice = $precioCamionGra;
            
            $personasPrice = ($validated['cantidad_personas'] ?? 2) * $precioPersona;
            $costoBaseA = $distanciaBaseA * $precioKmOperacion;
            $costoAB = $distanciaAB * $precioKmMudz;

            $precioEstimadoFinal = $camionPrice + $personasPrice + $costoBaseA + $costoAB;
        } else {
            $precioEstimadoFinal = $distanciaAB * $precioKmTrans;
            $pesoCarga = floatval($validated['peso_carga'] ?? 0);
            
            if ($pesoCarga > $pesoBaseMaximo && $intervaloPesoExtra > 0) {
                $pesoExcedente = $pesoCarga - $pesoBaseMaximo;
                $intervalosAdicionales = ceil($pesoExcedente / $intervaloPesoExtra);
                $cargoExtra = $intervalosAdicionales * $precioPesoExtra;
                $precioEstimadoFinal += $cargoExtra;
            }
        }

        $validated['precio_estimado_total'] = $precioEstimadoFinal;
        $validated['estado'] = 'pendiente';

        $solicitud = SolicitudTransporte::create($validated);

        return redirect()->route('home')->with('success', '¡Tu solicitud de transporte y mudanza ha sido enviada con éxito! Nos pondremos en contacto contigo pronto.');
    }

    private function calcCrow($lat1, $lon1, $lat2, $lon2)
    {
        $R = 6371; // km
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $lat1 = deg2rad($lat1);
        $lat2 = deg2rad($lat2);

        $a = sin($dLat / 2) * sin($dLat / 2) + sin($dLon / 2) * sin($dLon / 2) * cos($lat1) * cos($lat2); 
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a)); 
        $d = $R * $c;
        return $d;
    }
}

