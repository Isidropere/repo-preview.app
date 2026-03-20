<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modelo de tarjeta de pago guardada.
 *
 * Soporta multiples proveedores:
 *   - CardNet: usa no_tarjeta, mes_expiracion, anio_expiracion (columna: a\u{00F1}o_expiracion)
 *   - Stripe:  usa payment_method_id (pm_xxx)
 */
class TarjetaPago extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $table = 'tarjetas_pagos';
    protected $primaryKey = 'id_tarjeta';
    protected $keyType = 'string';
    public $incrementing = false;

    // Nombre de la columna con ñ (UTF-8 \u00F1)
    const COL_ANIO = "a\u{00F1}o_expiracion";

    protected $fillable = [
        'no_tarjeta',
        'tipo_tarjeta',
        'banco_tarjeta',
        'mes_expiracion',
        "a\u{00F1}o_expiracion",
        'estatus',
        'payment_method_id',
        'last4',
        'nombre_titular',
        'usar_esta_tarjeta',
        'id_user',
    ];

    // Nunca exponer el numero completo de tarjeta en JSON/API
    protected $hidden = ['no_tarjeta'];

    // ---------------------------------------------------------------
    // Relaciones
    // ---------------------------------------------------------------

    public function pagosCompra()
    {
        return $this->hasMany(PagoCompra::class, 'id_tarjeta', 'id_tarjeta');
    }

    // ---------------------------------------------------------------
    // Helpers para proveedores
    // ---------------------------------------------------------------

    /**
     * Retorna los datos de tarjeta en el formato que espera CardNet.
     * La fecha de expiracion se formatea como MM/YY.
     */
    public function datosCardnet(string $cvv = null): array
    {
        $anio = $this->getAttribute(self::COL_ANIO);
        return [
            'card_number'     => $this->no_tarjeta,
            'expiration_date' => sprintf('%02d/%s', $this->mes_expiracion, substr((string) $anio, -2)),
            'cvv'             => $cvv,
        ];
    }

    /**
     * Retorna los datos de tarjeta en el formato que espera Stripe.
     */
    public function datosStripe(): array
    {
        return [
            'payment_method_id' => $this->payment_method_id,
        ];
    }
}
