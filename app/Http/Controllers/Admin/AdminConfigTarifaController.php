<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ConfigTarifaCategoria29;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminConfigTarifaController extends Controller
{
    public function show(): JsonResponse
    {
        return response()->json(ConfigTarifaCategoria29::vigente());
    }

    public function update(Request $request): JsonResponse
    {
        $data = $request->validate([
            'monto_registro'            => 'required|numeric|min:0',
            'descuento_venta_masiva'    => 'required|numeric|between:0,100',
            'cantidad_minima_descuento' => 'required|integer|min:1',
        ]);

        $config = ConfigTarifaCategoria29::updateOrCreate(['id' => 1], $data);

        return response()->json(['success' => true, 'data' => $config]);
    }
}
