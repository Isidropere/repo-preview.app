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
            (float) $request->validated('valor_articulo', 0)
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
        $data = $request->validate([
            'porcentaje'            => 'sometimes|numeric|min:0|max:100',
            'porcentaje_plataforma' => 'sometimes|numeric|min:0|max:100',
            'porcentaje_seguro'     => 'sometimes|numeric|min:0|max:100',
            'porcentaje_manejo'     => 'sometimes|numeric|min:0|max:100',
        ]);

        $resultado = $this->deliveryService->actualizarConfig($clave, $data);

        if (!$resultado['success']) {
            return response()->json($resultado, 422);
        }

        return response()->json($resultado);
    }
}
