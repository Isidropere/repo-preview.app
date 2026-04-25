<?php

namespace App\Services\Payments;

use App\Contracts\PaymentProviderInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Proveedor de pagos CardNet / Ztrans
 *
 * Documentación: https://ecommerce.cardnet.com.do/api/payment
 * Protocolo: REST + TLS 1.2
 *
 * Flujo de cobro:
 *   1. POST /idenpotency-keys  → obtener idempotency-key
 *   2. POST /transactions/sales → procesar venta
 *
 * Credenciales requeridas en .env:
 *   CARDNET_MERCHANT_ID=349041263
 *   CARDNET_TERMINAL_ID=77777777
 *   CARDNET_TOKEN=454500350001
 *   CARDNET_ENV=QA          (QA | production)
 */
class CardnetProvider implements PaymentProviderInterface
{
    /** URL base según ambiente */
    private string $baseUrl;

    /** Número de afiliado del comercio (15 chars) */
    private string $merchantId;

    /** Terminal ID del comercio (8 chars) */
    private string $terminalId;

    /** Token generado por la aplicación cliente */
    private string $token;

    /** Ambiente: ECommerce, MOTO, etc. */
    private string $environment;

    public function __construct()
    {
        $env = config('services.cardnet.env', 'QA');

        // URL base según ambiente
        $this->baseUrl = $env === 'production'
            ? 'https://ecommerce.cardnet.com.do/api/payment'
            : 'https://labservicios.cardnet.com.do/api/payment';

        $this->merchantId  = config('services.cardnet.merchant_id');
        $this->terminalId  = config('services.cardnet.terminal_id');
        $this->token       = config('services.cardnet.token');
        $this->environment = config('services.cardnet.environment', 'ECommerce');
    }

    // ---------------------------------------------------------------
    // Interfaz pública
    // ---------------------------------------------------------------

    /**
     * Cobra un monto usando la API de CardNet.
     *
     * $datosTarjeta debe contener:
     *   - card_number      (string) número de tarjeta
     *   - expiration_date  (string) MM/YY
     *   - cvv              (string) 3-4 dígitos
     *
     * $opciones puede contener:
     *   - invoice_number   (string) referencia interna (máx 15 chars)
     *   - reference_number (string) referencia del pago
     *   - client_ip        (string) IP del cliente
     *   - tax              (float)  impuesto
     *   - tip              (float)  propina
     */
    public function cobrar(float $monto, string $currency, array $datosTarjeta, array $opciones = []): array
    {
        try {
            // Paso 1: obtener idempotency-key
            $ikey = $this->obtenerIdempotencyKey();

            // Paso 2: procesar venta
            $payload = [
                'idempotency-key'  => $ikey,
                'merchant-id'      => $this->merchantId,
                'terminal-id'      => $this->terminalId,
                'token'            => $this->token,
                'card-number'      => $datosTarjeta['card_number'],
                'expiration-date'  => $datosTarjeta['expiration_date'],
                'cvv'              => $datosTarjeta['cvv'] ?? null,
                'amount'           => $monto,
                'currency'         => $currency,
                'environment'      => $this->environment,
                'session-id'       => $opciones['session_id'] ?? session()->getId() ?? uniqid(),
                'invoice-number'   => substr($opciones['invoice_number'] ?? uniqid(), 0, 15),
                'reference-number' => $opciones['reference_number'] ?? uniqid(),
                'client-ip'        => $opciones['client_ip'] ?? request()->ip(),
                'tax'              => $opciones['tax'] ?? 0,
                'tip'              => $opciones['tip'] ?? 0,
            ];

            $response = Http::timeout(60)->retry(2, 1000)->withHeaders([
                'Content-Type' => 'application/json',
                'Accept'       => 'application/json',
            ])->post("{$this->baseUrl}/transactions/sales", $payload);

            $data = $response->json();

            Log::info('[CardNet] Respuesta sale', ['data' => $data]);

            // Código 00 = aprobada
            $aprobada = ($data['response-code'] ?? '') === '00'
                     && ($data['internal-response-code'] ?? '') === '0000';

            return [
                'success'        => $aprobada,
                'transaction_id' => $data['pnRef'] ?? null,
                'approval_code'  => $data['approval-code'] ?? null,
                'status'         => $aprobada ? 'approved' : ($data['response-code-desc'] ?? 'failed'),
                'raw'            => $data,
                'error'          => $aprobada ? null : ($data['response-code-desc'] ?? 'Error desconocido'),
            ];

        } catch (\Throwable $e) {
            Log::error('[CardNet] Error en cobro', [
                'error'   => $e->getMessage(),
                'class'   => get_class($e),
                'baseUrl' => $this->baseUrl,
            ]);
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Anula una transacción (solo antes del cierre diario a las 7PM).
     *
     * $opciones debe contener:
     *   - pn_ref           (string) pnRef de la transacción original
     *   - invoice_number   (string)
     *   - reference_number (string)
     *   - client_ip        (string)
     */
    public function anular(string $transactionId, float $monto, array $opciones = []): array
    {
        try {
            $ikey = $this->obtenerIdempotencyKey();

            $payload = [
                'idempotency-key'  => $ikey,
                'merchant-id'      => $this->merchantId,
                'terminal-id'      => $this->terminalId,
                'token'            => $this->token,
                'environment'      => $this->environment,
                'amount'           => $monto,
                'currency'         => $opciones['currency'] ?? '214',
                'pnRef'            => $opciones['pn_ref'] ?? $transactionId,
                'invoice-number'   => substr($opciones['invoice_number'] ?? uniqid(), 0, 15),
                'reference-number' => $opciones['reference_number'] ?? uniqid(),
                'client-ip'        => $opciones['client_ip'] ?? request()->ip(),
            ];

            $response = Http::timeout(30)->withHeaders([
                'Content-Type' => 'application/json',
                'Accept'       => 'application/json',
            ])->post("{$this->baseUrl}/transactions/voids", $payload);

            $data = $response->json();
            $ok   = ($data['response-code'] ?? '') === '00';

            return [
                'success' => $ok,
                'error'   => $ok ? null : ($data['response-code-desc'] ?? 'Error'),
                'raw'     => $data,
            ];

        } catch (\Throwable $e) {
            Log::error('[CardNet] Error en anulación', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage(), 'raw' => []];
        }
    }

    /**
     * Reembolso parcial o total de una transacción.
     *
     * $opciones debe contener:
     *   - tx_token         (string) txToken de la transacción original
     *   - invoice_number   (string)
     *   - reference_number (string)
     *   - client_ip        (string)
     */
    public function reembolsar(string $transactionId, float $monto, array $opciones = []): array
    {
        try {
            $ikey = $this->obtenerIdempotencyKey();

            $payload = [
                'operation'        => 'refund',
                'idempotency-key'  => $ikey,
                'merchant-id'      => $this->merchantId,
                'terminal-id'      => $this->terminalId,
                'token'            => $this->token,
                'amount'           => $monto,
                'currency'         => $opciones['currency'] ?? '214',
                'invoice-number'   => substr($opciones['invoice_number'] ?? uniqid(), 0, 15),
                'reference-number' => $opciones['reference_number'] ?? uniqid(),
                'client-ip'        => $opciones['client_ip'] ?? request()->ip(),
                'txToken'          => $opciones['tx_token'] ?? $transactionId,
            ];

            $response = Http::timeout(30)->withHeaders([
                'Content-Type' => 'application/json',
                'Accept'       => 'application/json',
            ])->post("{$this->baseUrl}/transactions/refund", $payload);

            $data = $response->json();
            $ok   = ($data['response-code'] ?? '') === '00';

            return [
                'success' => $ok,
                'error'   => $ok ? null : ($data['response-code-desc'] ?? 'Error'),
                'raw'     => $data,
            ];

        } catch (\Throwable $e) {
            Log::error('[CardNet] Error en reembolso', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage(), 'raw' => []];
        }
    }

    public function nombre(): string
    {
        return 'CardNet';
    }

    // ---------------------------------------------------------------
    // Métodos privados
    // ---------------------------------------------------------------

    /**
     * Obtiene un idempotency-key fresco de la API de CardNet.
     * Debe llamarse antes de cada transacción.
     *
     * @throws \RuntimeException si la API no responde
     */
    private function obtenerIdempotencyKey(): string
    {
        try {
            $response = Http::timeout(60)->retry(2, 1000)->withHeaders([
                'Accept' => 'text/plain',
            ])->post("{$this->baseUrl}/idenpotency-keys");
        } catch (\Throwable $e) {
            Log::error('[CardNet] Error obteniendo idempotency-key', [
                'error'   => $e->getMessage(),
                'baseUrl' => $this->baseUrl,
            ]);
            throw new \RuntimeException('No se pudo conectar con CardNet: ' . $e->getMessage());
        }

        if (!$response->successful()) {
            throw new \RuntimeException('No se pudo obtener idempotency-key de CardNet: ' . $response->status());
        }

        $body = $response->body();
        return str_replace('ikey:', '', trim($body));
    }

    /** Estructura de error estándar */
    private function errorResponse(string $mensaje): array
    {
        return [
            'success'        => false,
            'transaction_id' => null,
            'approval_code'  => null,
            'status'         => 'failed',
            'raw'            => [],
            'error'          => $mensaje,
        ];
    }
}
