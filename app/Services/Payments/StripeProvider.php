<?php

namespace App\Services\Payments;

use App\Contracts\PaymentProviderInterface;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Illuminate\Support\Facades\Log;

/**
 * Proveedor de pagos Stripe.
 *
 * Credenciales requeridas en .env:
 *   STRIPE_SECRET=sk_test_...
 *
 * $datosTarjeta debe contener:
 *   - payment_method_id  (string) pm_xxx generado por Stripe.js en el frontend
 */
class StripeProvider implements PaymentProviderInterface
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    /**
     * Cobra usando un PaymentIntent de Stripe.
     *
     * $datosTarjeta['payment_method_id'] = pm_xxx (de Stripe.js)
     */
    public function cobrar(float $monto, string $currency, array $datosTarjeta, array $opciones = []): array
    {
        try {
            $intent = PaymentIntent::create([
                'amount'         => (int) round($monto * 100), // Stripe trabaja en centavos
                'currency'       => strtolower($currency),
                'payment_method' => $datosTarjeta['payment_method_id'],
                'confirm'        => true,
            ]);

            $aprobado = in_array($intent->status, ['succeeded', 'requires_capture']);

            return [
                'success'        => $aprobado,
                'transaction_id' => $intent->id,
                'approval_code'  => $intent->id,
                'status'         => $intent->status,
                'raw'            => $intent->toArray(),
                'error'          => $aprobado ? null : 'Pago no completado: ' . $intent->status,
            ];

        } catch (\Throwable $e) {
            Log::error('[Stripe] Error en cobro', ['error' => $e->getMessage()]);
            return [
                'success'        => false,
                'transaction_id' => null,
                'approval_code'  => null,
                'status'         => 'failed',
                'raw'            => [],
                'error'          => $e->getMessage(),
            ];
        }
    }

    /**
     * Cancela un PaymentIntent (equivalente a anulación).
     * Solo funciona si el intent no ha sido capturado aún.
     */
    public function anular(string $transactionId, float $monto, array $opciones = []): array
    {
        try {
            $intent = PaymentIntent::retrieve($transactionId);
            $intent->cancel();

            return ['success' => true, 'error' => null, 'raw' => $intent->toArray()];

        } catch (\Throwable $e) {
            Log::error('[Stripe] Error en anulación', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage(), 'raw' => []];
        }
    }

    /**
     * Reembolso usando la API de Refunds de Stripe.
     */
    public function reembolsar(string $transactionId, float $monto, array $opciones = []): array
    {
        try {
            $refund = \Stripe\Refund::create([
                'payment_intent' => $transactionId,
                'amount'         => (int) round($monto * 100),
            ]);

            $ok = $refund->status === 'succeeded';

            return [
                'success' => $ok,
                'error'   => $ok ? null : 'Reembolso no completado: ' . $refund->status,
                'raw'     => $refund->toArray(),
            ];

        } catch (\Throwable $e) {
            Log::error('[Stripe] Error en reembolso', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage(), 'raw' => []];
        }
    }

    public function nombre(): string
    {
        return 'Stripe';
    }
}
