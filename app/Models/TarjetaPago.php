<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

/**
 * Modelo de tarjeta de pago guardada.
 *
 * El número de tarjeta se almacena encriptado (AES-256-CBC via APP_KEY).
 * Solo se expone last4 en las vistas.
 *
 * Soporta:
 *   - CardNet: no_tarjeta (encriptado), mes_expiracion, año_expiracion
 *   - Stripe:  payment_method_id (pm_xxx)
 */
class TarjetaPago extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $table = 'tarjetas_pagos';
    protected $primaryKey = 'id_tarjeta';
    protected $keyType = 'string';
    public $incrementing = false;

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

    protected $hidden = ['no_tarjeta'];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id_tarjeta)) {
                $model->id_tarjeta = (string) \Illuminate\Support\Str::uuid();
            }
            // Encriptar número de tarjeta al crear
            if (!empty($model->no_tarjeta) && !str_starts_with($model->no_tarjeta, 'eyJ')) {
                $model->no_tarjeta = Crypt::encryptString($model->no_tarjeta);
            }
        });

        static::updating(function ($model) {
            if ($model->isDirty('no_tarjeta') && !empty($model->no_tarjeta) && !str_starts_with($model->no_tarjeta, 'eyJ')) {
                $model->no_tarjeta = Crypt::encryptString($model->no_tarjeta);
            }
        });
    }

    /**
     * Desencripta el número de tarjeta para uso interno (cobros).
     */
    public function getNumeroDesencriptado(): ?string
    {
        if (empty($this->no_tarjeta)) {
            return null;
        }
        try {
            return Crypt::decryptString($this->no_tarjeta);
        } catch (\Throwable $e) {
            // Si no está encriptado (datos legacy), retornar tal cual
            return $this->no_tarjeta;
        }
    }

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

    public function datosAzul(?string $cvv = null): array
    {
        $anio = $this->getAttribute(self::COL_ANIO);
        $mes = (int) $this->mes_expiracion;
        $anioNum = (int) $anio;

        // Validar que la tarjeta no esté expirada
        if ($anioNum > 0 && $mes > 0) {
            if ($anioNum < 100) {
                $anioNum += 2000;
            }
            $expTimestamp = mktime(0, 0, 0, $mes + 1, 1, $anioNum);
            if ($expTimestamp < time()) {
                throw new \RuntimeException('La tarjeta está expirada. Actualiza o usa otra tarjeta.');
            }
        }

        return [
            'card_number'     => $this->getNumeroDesencriptado(),
            'expiration_date' => sprintf('%04d%02d', $anioNum, $mes),
            'cvv'             => $cvv,
        ];
    }

    public function datosDriver(?string $cvv = null): array
    {
        return $this->datosAzul($cvv);
    }
}
