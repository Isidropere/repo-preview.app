<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProcesarPagoRequest;
use App\Services\CheckoutService;
use Illuminate\Support\Facades\Log;

/**
 * ============================================================
 * PagoController — Controlador de procesamiento de pagos
 * ============================================================
 *
 * Punto de entrada para el cobro con tarjeta. Delega todo
 * el flujo (validación de stock, cobro, registro en BD,
 * reembolso automático en caso de fallo) a CheckoutService.
 *
 * Rutas: POST /carrito/pago
 * Middleware: auth, throttle.sensitive:5,1
 * ============================================================
 */
class PagoController extends Controller
{
    public function __construct(
        private CheckoutService $checkoutService,
    ) {}

    public function procesar(ProcesarPagoRequest $request)
    {
        Log::info('Iniciando proceso de pago', ['user_id' => auth()->id()]);

        $resultado = $this->checkoutService->procesar(
            userId:    auth()->id(),
            idTarjeta: $request->validated('id_tarjeta'),
            cvv:       $request->validated('cvv'),
            clientIp:  $request->ip(),
        );

        if (!$resultado['success']) {
            return redirect()->route('carrito.checkout_index')
                ->with('error', $resultado['message']);
        }

        return redirect()->route('historial')
            ->with('success', $resultado['message'])
            ->with('order_completed_id', $resultado['order_completed_id'] ?? null);
    }
}
