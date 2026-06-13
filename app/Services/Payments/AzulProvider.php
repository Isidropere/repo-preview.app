<?php

namespace App\Services\Payments;

use App\Contracts\PaymentProviderInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

/**
 * Proveedor de pagos Azul (Banco Popular Dominicano)
 *
 * Documentación: https://www.azul.com.do
 * Protocolo: WebServices JSON + TLS 1.2
 */
class AzulProvider implements PaymentProviderInterface
{
    private string $primaryUrl;
    private ?string $secondaryUrl = null;
    private string $store;
    private string $auth1;
    private string $auth2;
    private string $channel;
    private string $authKey;
    private string $paymentPageUrl;

    public function __construct()
    {
        $env = config('services.azul.env', 'QA');

        if ($env === 'production') {
            $this->primaryUrl     = 'https://pagos.azul.com.do/webservices/JSON/Default.aspx';
            $this->secondaryUrl   = 'https://contpagos.azul.com.do/webservices/JSON/Default.aspx';
            $this->paymentPageUrl = 'https://pagos.azul.com.do/PaymentPage/Default.aspx';
        } else {
            $this->primaryUrl     = 'https://pruebas.azul.com.do/webservices/JSON/Default.aspx';
            $this->secondaryUrl   = null;
            $this->paymentPageUrl = 'https://pruebas.azul.com.do/PaymentPage/Default.aspx';
        }

        $this->store   = config('services.azul.store');
        $this->auth1   = config('services.azul.auth1');
        $this->auth2   = config('services.azul.auth2');
        $this->channel = config('services.azul.channel', 'EC');
        $this->authKey = config('services.azul.auth_key', '');
    }

    /**
     * Genera los campos del formulario oculto para redirigir al cliente a la Página de Pago de AZUL.
     * Calcula la firma digital AuthHash utilizando UTF-16LE y HMAC-SHA512.
     */
    public function generarCamposFormulario(float $monto, string $orderNumber, array $opciones = []): array
    {
        $amountCents = (int) round($monto * 100);
        $itbisCents = (int) round(($opciones['tax'] ?? 0) * 100);

        $fields = [
            'MerchantId'          => $this->store,
            'MerchantName'        => $opciones['merchant_name'] ?? 'Cámbialo RD',
            'MerchantType'        => 'ECommerce',
            'CurrencyCode'        => '$',
            'OrderNumber'         => $orderNumber,
            'Amount'              => (string) $amountCents,
            'ITBIS'               => $itbisCents > 0 ? (string) $itbisCents : '000',
            'ApprovedUrl'         => $opciones['approved_url'] ?? route('pago.redirect.aprobado'),
            'DeclinedUrl'         => $opciones['declined_url'] ?? route('pago.redirect.declinado'),
            'CancelUrl'           => $opciones['cancel_url'] ?? route('pago.redirect.cancelado'),
            'UseCustomField1'     => '0',
            'CustomField1Label'   => '',
            'CustomField1Value'   => '',
            'UseCustomField2'     => '0',
            'CustomField2Label'   => '',
            'CustomField2Value'   => '',
        ];

        // Concatenar todos los valores para el hash en el orden preciso del documento técnico
        $concatStr = $fields['MerchantId'] .
                     $fields['MerchantName'] .
                     $fields['MerchantType'] .
                     $fields['CurrencyCode'] .
                     $fields['OrderNumber'] .
                     $fields['Amount'] .
                     $fields['ITBIS'] .
                     $fields['ApprovedUrl'] .
                     $fields['DeclinedUrl'] .
                     $fields['CancelUrl'] .
                     $fields['UseCustomField1'] .
                     $fields['CustomField1Label'] .
                     $fields['CustomField1Value'] .
                     $fields['UseCustomField2'] .
                     $fields['CustomField2Label'] .
                     $fields['CustomField2Value'] .
                     $this->authKey;

        // Convertir a bytes UTF-16LE (Unicode en C#) antes de hashear
        $utf16Str = mb_convert_encoding($concatStr, 'UTF-16LE', 'UTF-8');
        
        // Calcular HMAC SHA512
        $authHash = hash_hmac('sha512', $utf16Str, $this->authKey);

        $fields['AuthHash'] = $authHash;

        return [
            'url'    => $this->paymentPageUrl,
            'fields' => $fields,
        ];
    }

    /**
     * Valida la firma AuthHash recibida de AZUL en la respuesta de redirección o IPN.
     */
    public function validarFirmaRespuesta(array $params): bool
    {
        $receivedHash = $params['AuthHash'] ?? $params['authHash'] ?? '';
        if (empty($receivedHash)) {
            return false;
        }

        // Concatenar parámetros de respuesta en el orden preciso del documento técnico (Pág 8)
        $concatStr = ($params['OrderNumber'] ?? $params['orderNumber'] ?? '') .
                     ($params['Amount'] ?? $params['amount'] ?? '') .
                     ($params['AuthorizationCode'] ?? $params['authorizationCode'] ?? '') .
                     ($params['DateTime'] ?? $params['dateTime'] ?? '') .
                     ($params['ResponseCode'] ?? $params['responseCode'] ?? '') .
                     ($params['IsoCode'] ?? $params['ISOCode'] ?? $params['isoCode'] ?? '') .
                     ($params['ResponseMessage'] ?? $params['responseMessage'] ?? '') .
                     ($params['ErrorDescription'] ?? $params['errorDescription'] ?? '') .
                     ($params['RRN'] ?? $params['rrn'] ?? '') .
                     $this->authKey;

        $utf16Str = mb_convert_encoding($concatStr, 'UTF-16LE', 'UTF-8');
        $expectedHash = hash_hmac('sha512', $utf16Str, $this->authKey);

        return hash_equals(strtolower($expectedHash), strtolower($receivedHash));
    }

    public function cobrar(float $monto, string $currency, array $datosTarjeta, array $opciones = []): array
    {
        $amountCents = (int) round($monto * 100);
        $itbisCents  = (int) round(($opciones['tax'] ?? 0) * 100);

        $payload = [
            'Channel'              => $this->channel,
            'Store'                => $this->store,
            'CardNumber'           => $datosTarjeta['card_number'],
            'Expiration'           => $datosTarjeta['expiration_date'], // Formato YYYYMM
            'CVC'                  => $datosTarjeta['cvv'] ?? '',
            'PosInputMode'         => 'E-Commerce',
            'TrxType'              => 'Sale',
            'Amount'               => (string) $amountCents,
            'Itbis'                => $itbisCents > 0 ? (string) $itbisCents : '000',
            'CurrencyPosCode'      => '$',
            'Payments'             => '1',
            'Plan'                 => '0',
            'AcquirerRefData'      => '1',
            'RRN'                  => null,
            'CustomerServicePhone' => '',
            'OrderNumber'          => substr($opciones['invoice_number'] ?? uniqid(), 0, 15),
            'ECommerceUrl'         => request()->getHost() ?: 'localhost',
            'CustomOrderId'        => $opciones['invoice_number'] ?? uniqid(),
            'DataVaultToken'       => '',
            'ForceNo3DS'           => '1',
            'SaveToDataVault'      => '0',
        ];

        $headers = [
            'Content-Type' => 'application/json',
            'Accept'       => 'application/json',
            'Auth1'        => $this->auth1,
            'Auth2'        => $this->auth2,
        ];

        try {
            $response = $this->sendRequest($this->primaryUrl, $payload, $headers);
            $data = $response->json();

            Log::info('[Azul] Respuesta cobro', ['data' => $data]);

            // IsoCode 00 = aprobada, ResponseCode debe ser ISO8583
            $aprobada = ($data['IsoCode'] ?? '') === '00'
                     && ($data['ResponseCode'] ?? '') === 'ISO8583';

            $errorMsg = $aprobada ? null : ($data['ErrorDescription'] ?? $data['ResponseMessage'] ?? 'Error desconocido');

            $this->logTransaccion('sale', $monto, $payload, $data, $aprobada, $errorMsg, $opciones['invoice_number'] ?? null);

            return [
                'success'        => $aprobada,
                'transaction_id' => $data['AzulOrderId'] ?? null,
                'approval_code'  => $data['AuthorizationCode'] ?? null,
                'status'         => $aprobada ? 'approved' : ($data['ResponseMessage'] ?? 'failed'),
                'raw'            => $data,
                'error'          => $errorMsg,
            ];

        } catch (\Throwable $e) {
            Log::error('[Azul] Excepción en cobro', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $this->logTransaccion('sale', $monto, $payload, [], false, $e->getMessage(), $opciones['invoice_number'] ?? null);

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

    public function anular(string $transactionId, float $monto, array $opciones = []): array
    {
        $payload = [
            'Channel'     => $this->channel,
            'Store'       => $this->store,
            'AzulOrderId' => $transactionId,
        ];

        $headers = [
            'Content-Type' => 'application/json',
            'Accept'       => 'application/json',
            'Auth1'        => $this->auth1,
            'Auth2'        => $this->auth2,
        ];

        try {
            // Se debe enviar a la URL con query param de Void
            $primaryVoidUrl = $this->primaryUrl . '?ProcessVoid';
            $secondaryVoidUrl = $this->secondaryUrl ? ($this->secondaryUrl . '?ProcessVoid') : null;

            $response = $this->sendRequest($primaryVoidUrl, $payload, $headers, $secondaryVoidUrl);
            $data = $response->json();

            $ok = ($data['IsoCode'] ?? '') === '00';
            $errorMsg = $ok ? null : ($data['ErrorDescription'] ?? $data['ResponseMessage'] ?? 'Error');

            $this->logTransaccion('void', $monto, $payload, $data, $ok, $errorMsg, $opciones['invoice_number'] ?? null);

            return [
                'success' => $ok,
                'error'   => $errorMsg,
                'raw'     => $data,
            ];

        } catch (\Throwable $e) {
            Log::error('[Azul] Excepción en anulación', ['error' => $e->getMessage()]);

            $this->logTransaccion('void', $monto, $payload, [], false, $e->getMessage(), $opciones['invoice_number'] ?? null);

            return [
                'success' => false,
                'error'   => $e->getMessage(),
                'raw'     => [],
            ];
        }
    }

    public function reembolsar(string $transactionId, float $monto, array $opciones = []): array
    {
        $amountCents = (int) round($monto * 100);

        $payload = [
            'Channel'              => $this->channel,
            'Store'                => $this->store,
            'PosInputMode'         => 'E-Commerce',
            'TrxType'              => 'Refund',
            'Amount'               => (string) $amountCents,
            'Itbis'                => '000',
            'CurrencyPosCode'      => '$',
            'Payments'             => '1',
            'Plan'                 => '0',
            'OriginalDate'         => $opciones['original_date'] ?? date('Ymd'), // YYYYMMDD
            'AzulOrderId'          => $transactionId,
            'CustomOrderId'        => $opciones['invoice_number'] ?? ('REF' . uniqid()),
        ];

        $headers = [
            'Content-Type' => 'application/json',
            'Accept'       => 'application/json',
            'Auth1'        => $this->auth1,
            'Auth2'        => $this->auth2,
        ];

        try {
            $response = $this->sendRequest($this->primaryUrl, $payload, $headers);
            $data = $response->json();

            $ok = ($data['IsoCode'] ?? '') === '00';
            $errorMsg = $ok ? null : ($data['ErrorDescription'] ?? $data['ResponseMessage'] ?? 'Error');

            $this->logTransaccion('refund', $monto, $payload, $data, $ok, $errorMsg, $opciones['invoice_number'] ?? null);

            return [
                'success' => $ok,
                'error'   => $errorMsg,
                'raw'     => $data,
            ];

        } catch (\Throwable $e) {
            Log::error('[Azul] Excepción en reembolso', ['error' => $e->getMessage()]);

            $this->logTransaccion('refund', $monto, $payload, [], false, $e->getMessage(), $opciones['invoice_number'] ?? null);

            return [
                'success' => false,
                'error'   => $e->getMessage(),
                'raw'     => [],
            ];
        }
    }

    public function nombre(): string
    {
        return 'Azul';
    }

    // ---------------------------------------------------------------
    // Métodos privados
    // ---------------------------------------------------------------

    /**
     * Envía la petición HTTP a la URL principal, con fallback a la secundaria si falla.
     */
    private function sendRequest(string $url, array $payload, array $headers, ?string $fallbackUrl = null): \Illuminate\Http\Client\Response
    {
        try {
            return Http::timeout(60)->withHeaders($headers)->post($url, $payload);
        } catch (\Throwable $e) {
            Log::warning('[Azul] Error en URL principal, intentando URL alternativa', [
                'url'   => $url,
                'error' => $e->getMessage(),
            ]);

            $altUrl = $fallbackUrl ?: ($this->secondaryUrl ? str_replace($this->primaryUrl, $this->secondaryUrl, $url) : null);

            if ($altUrl && $altUrl !== $url) {
                return Http::timeout(60)->withHeaders($headers)->post($altUrl, $payload);
            }

            throw $e;
        }
    }

    /**
     * Registra la transacción en la tabla logs_pagos para auditoría técnica.
     */
    private function logTransaccion(
        string $type,
        float $amount,
        array $requestData,
        array $responseData,
        bool $success,
        ?string $errorMsg,
        ?string $orderId
    ): void {
        try {
            DB::table('logs_pagos')->insert([
                'id_user'          => auth()->id(),
                'custom_order_id'  => $orderId,
                'provider'         => 'azul',
                'transaction_type' => $type,
                'amount'           => $amount,
                'request_payload'  => json_encode($this->obfuscatePayload($requestData)),
                'response_payload' => json_encode($responseData),
                'is_success'       => $success,
                'error_message'    => $errorMsg,
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);
        } catch (\Throwable $e) {
            Log::error('[Azul] Error guardando log de transaccion en DB', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Oculta datos sensibles de la tarjeta (PCI-DSS) antes de guardarlos en log de base de datos.
     */
    private function obfuscatePayload(array $payload): array
    {
        if (isset($payload['CardNumber'])) {
            $len = strlen($payload['CardNumber']);
            $payload['CardNumber'] = substr($payload['CardNumber'], 0, 6) . str_repeat('*', max(0, $len - 10)) . substr($payload['CardNumber'], -4);
        }
        if (isset($payload['CVC'])) {
            $payload['CVC'] = '***';
        }
        return $payload;
    }
}
