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

        // Singleton: siempre actualizar la primera fila, o crearla si no existe
        $config = ConfigTarifaCategoria29::first();
        if ($config) {
            $config->update($data);
        } else {
            $config = ConfigTarifaCategoria29::create($data);
        }

        // Invalidar caché para que vigente() devuelva el valor actualizado
        cache()->forget('config_tarifa_cat29');

        return response()->json(['success' => true, 'data' => $config]);
    }
}
