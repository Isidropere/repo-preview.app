<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\CheckoutService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Direcciones;

class PagoApiController extends Controller
{
    public function __construct(
        private CheckoutService $checkoutService
    ) {}

    /** POST /api/pago/checkout */
    public function checkout(Request $request)
    {
        $request->validate([
            'id_tarjeta'   => 'required|string|exists:tarjetas_pagos,id_tarjeta',
            'cvv'          => 'nullable|string|max:4',
            'id_direccion' => 'required|integer|exists:direcciones,id_direccion',
        ]);

        $userId = $request->user()->id;
        $idDireccion = $request->input('id_direccion');

        // Establecer la dirección elegida como predeterminada para que el CheckoutService la use
        Direcciones::where('id_user', $userId)->update(['es_predeterminada' => 0]);
        Direcciones::where('id_direccion', $idDireccion)
            ->where('id_user', $userId)
            ->update(['es_predeterminada' => 1]);

        Log::info('Iniciando proceso de pago API', ['user_id' => $userId, 'id_tarjeta' => $request->id_tarjeta]);

        $resultado = $this->checkoutService->procesar(
            userId:    $userId,
            idTarjeta: $request->input('id_tarjeta'),
            cvv:       $request->input('cvv'),
            clientIp:  $request->ip(),
        );

        if (!$resultado['success']) {
            return response()->json([
                'success' => false,
                'message' => $resultado['message']
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => $resultado['message']
        ]);
    }
}
