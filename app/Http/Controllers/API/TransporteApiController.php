<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\TransporteArticulo;
use App\Models\TransporteConfiguracion;
use App\Models\SolicitudTransporte;
use Illuminate\Http\Request;

class TransporteApiController extends Controller
{
    /**
     * Devuelve el catálogo de artículos activos y la configuración de tarifas.
     */
    public function articulos()
    {
        $articulos = TransporteArticulo::where('estatus', true)->orderBy('nombre')->get();
        
        $configuracion = [
            'precio_km_transporte' => floatval(TransporteConfiguracion::get('precio_km_transporte', 50)),
            'precio_km_mudanza' => floatval(TransporteConfiguracion::get('precio_km_mudanza', 100)),
            'limite_articulos_mudanza' => intval(TransporteConfiguracion::get('limite_articulos_mudanza', 5)),
        ];

        return response()->json([
            'articulos' => $articulos,
            'configuracion' => $configuracion
        ]);
    }

    /**
     * Procesa y guarda una nueva solicitud de transporte y mudanza.
     */
    public function solicitar(Request $request)
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
        ]);

        if ($request->user()) {
            $validated['id_usuario'] = $request->user()->id;
        }

        $validated['direccion'] = $validated['punto_recogida_address'] ?? 'No especificada';
        $validated['dimensiones_carga'] = $validated['dimensiones_carga'] ?? 'No especificadas';
        $validated['estado'] = 'pendiente';

        $solicitud = SolicitudTransporte::create($validated);

        if ($request->has('articulos') && is_array($request->articulos)) {
            $syncData = [];
            $countArticulosTotal = 0;
            $articulosCatalogo = TransporteArticulo::whereIn('id', array_keys($request->articulos))->get()->keyBy('id');

            foreach ($request->articulos as $articuloId => $sizes) {
                $articulo = $articulosCatalogo->get($articuloId);
                if (!$articulo || !is_array($sizes)) continue;

                $itemSubtotal = 0;
                $itemCantidad = 0;
                $dimensionesArr = [];

                foreach ($sizes as $sizeKey => $value) {
                    if ($value == '1' || $value === 'on' || $value === true || $value == 'true') {
                        $qty = max(1, intval($request->input("cantidades.{$articuloId}.{$sizeKey}", 1)));
                        $priceField = "precio_" . ($sizeKey == 'pequeno' ? 'pequeno' : ($sizeKey == 'mediano' ? 'mediano' : 'grande'));
                        $price = $articulo ? floatval($articulo->$priceField) : 0;
                        
                        $itemSubtotal += $price * $qty;
                        $itemCantidad += $qty;
                        
                        $dimensionesArr[$sizeKey] = [
                            'cantidad' => $qty,
                            'precio' => $price
                        ];
                    }
                }

                if ($itemCantidad > 0) {
                    $countArticulosTotal += $itemCantidad;
                    $syncData[$articuloId] = [
                        'cantidad' => $itemCantidad,
                        'dimensiones' => json_encode($dimensionesArr),
                        'peso' => null,
                        'precio_unitario' => $itemSubtotal / $itemCantidad,
                        'subtotal' => $itemSubtotal
                    ];
                }
            }

            // Recalcular el total estimado en el backend para seguridad
            $precioKmTrans = TransporteConfiguracion::get('precio_km_transporte', 50);
            $precioKmMudz = TransporteConfiguracion::get('precio_km_mudanza', 100);
            $limiteMudz = TransporteConfiguracion::get('limite_articulos_mudanza', 5);

            $tipoServicioFinal = $request->tipo_servicio;
            if ($countArticulosTotal > $limiteMudz) {
                $tipoServicioFinal = 'mudanza';
            }

            $precioKmAplicar = ($tipoServicioFinal === 'mudanza') ? $precioKmMudz : $precioKmTrans;
            $distancia = floatval($request->input('distancia_km', 0));
            
            $totalArticulos = collect($syncData)->sum('subtotal');
            $precioEstimadoFinal = $totalArticulos + ($distancia * $precioKmAplicar);

            $solicitud->update([
                'tipo_servicio' => $tipoServicioFinal,
                'precio_estimado_total' => $precioEstimadoFinal
            ]);

            if (!empty($syncData)) {
                $solicitud->articulos()->attach($syncData);
            }
        }

        return response()->json([
            'success' => true,
            'message' => '¡Tu solicitud de transporte y mudanza ha sido enviada con éxito! Nos pondremos en contacto contigo pronto.',
            'solicitud' => $solicitud
        ]);
    }
}
