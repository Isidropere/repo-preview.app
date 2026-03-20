<?php

namespace App\Contracts;

/**
 * Contrato que deben implementar todos los proveedores de pago.
 *
 * Permite cambiar de proveedor (Stripe, CardNet, PayPal, etc.)
 * sin modificar el código del controlador ni del servicio.
 */
interface PaymentProviderInterface
{
    /**
     * Cobra un monto a una tarjeta/método de pago.
     *
     * @param  float   $monto          Monto a cobrar
     * @param  string  $currency       Código de moneda (ej: "usd", "214")
     * @param  array   $datosTarjeta   Datos del método de pago (varía por proveedor)
     * @param  array   $opciones       Opciones adicionales (invoice, ip, etc.)
     * @return array{
     *   success: bool,
     *   transaction_id: string|null,
     *   approval_code: string|null,
     *   status: string,
     *   raw: array,
     *   error: string|null
     * }
     */
    public function cobrar(float $monto, string $currency, array $datosTarjeta, array $opciones = []): array;

    /**
     * Anula/reversa una transacción previamente autorizada.
     *
     * @param  string  $transactionId  ID de la transacción original
     * @param  float   $monto          Monto a anular
     * @param  array   $opciones       Datos adicionales requeridos por el proveedor
     * @return array{success: bool, error: string|null, raw: array}
     */
    public function anular(string $transactionId, float $monto, array $opciones = []): array;

    /**
     * Realiza un reembolso (parcial o total) de una transacción.
     *
     * @param  string  $transactionId  ID de la transacción original
     * @param  float   $monto          Monto a reembolsar
     * @param  array   $opciones       Datos adicionales requeridos por el proveedor
     * @return array{success: bool, error: string|null, raw: array}
     */
    public function reembolsar(string $transactionId, float $monto, array $opciones = []): array;

    /**
     * Nombre legible del proveedor (para logs y UI).
     */
    public function nombre(): string;
}
