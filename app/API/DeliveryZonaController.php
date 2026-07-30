<?php

namespace App\API;

use App\Http\Requests\CalcularDeliveryRequest;
use App\Services\DeliveryService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class DeliveryZonaController extends Controller
{
    public function __construct(
        private DeliveryService $deliveryService,
    ) {}

    /**
     * GET /api/delivery/zonas
     */
    public function index()
    {
        return response()->json($this->deliveryService->listarZonas());
    }

    /**
     * GET /api/delivery/calcular?pueblo=Bonao&tipo_destinatario=empresa&valor_articulo=5000
     */
    public function calcular(CalcularDeliveryRequest $request)
    {
        $resultado = $this->deliveryService->calcular(
            $request->validated('pueblo'),
            $request->validated('tipo_destinatario', 'persona'),
            (float) $request->validated('valor_articulo', 0),
            (float) $request->input('peso_lbs', 0),
            (float) $request->input('alto_cm', 0),
            (float) $request->input('ancho_cm', 0),
            (float) $request->input('profundo_cm', 0)
        );

        if (!$resultado['success']) {
            return response()->json($resultado, 404);
        }

        return response()->json(array_merge($resultado, [
            'pueblo_buscado' => $request->validated('pueblo'),
        ]));
    }

    /**
     * GET /api/delivery/config
     */
    public function config()
    {
        return response()->json($this->deliveryService->obtenerConfig());
    }

    /**
     * POST /api/delivery/config/{clave}
     */
    public function updateConfig(Request $request, string $clave)
    {
        if ($clave === 'sobredimensionado') {
            $data = $request->validate([
                'min_peso_lbs'     => 'sometimes|numeric|min:0',
                'min_alto_cm'      => 'sometimes|numeric|min:0',
                'min_ancho_cm'     => 'sometimes|numeric|min:0',
                'min_profundo_cm'  => 'sometimes|numeric|min:0',
                'intervalo_peso_lbs'=> 'sometimes|numeric|min:0',
                'recargo_monto'    => 'sometimes|numeric|min:0',
            ]);
        } else {
            $data = $request->validate([
                'porcentaje'            => 'sometimes|numeric|min:0|max:100',
                'porcentaje_plataforma' => 'sometimes|numeric|min:0|max:100',
                'porcentaje_seguro'     => 'sometimes|numeric|min:0|max:100',
                'porcentaje_manejo'     => 'sometimes|numeric|min:0|max:100',
            ]);
        }

        $resultado = $this->deliveryService->actualizarConfig($clave, $data);

        if (!$resultado['success']) {
            return response()->json($resultado, 422);
        }

        return response()->json($resultado);
    }
}
