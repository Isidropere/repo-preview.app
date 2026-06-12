<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Registro del pago de envío de un participante en un intercambio.
 *
 * Tabla: pago_envio_intercambio
 *
 * Estados:
 *   pendiente   → aún no ha pagado
 *   pagado      → cobro con tarjeta aprobado
 *   pagado_pull → se descontó 1 unidad de pull (categoría 29)
 *
 * Tipo de pago:
 *   tarjeta → artículo físico, paga con tarjeta guardada
 *   pull    → artículo de servicio (cat. 29), descuenta 1 pull
 */
class PagoEnvioIntercambio extends Model
{
    protected $table = 'pago_envio_intercambio';

    protected $fillable = [
        'id_negociacion',
        'id_user',
        'monto',
        'tipo_pago',
        'estado',
        'id_tarjeta',
        'transaction_id',
        'approval_code',
        'id_pago_registro_talento',
    ];

    public function negociacion()
    {
        return $this->belongsTo(Negociacion::class, 'id_negociacion', 'id_negociacion');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function tarjeta()
    {
        return $this->belongsTo(TarjetaPago::class, 'id_tarjeta', 'id_tarjeta');
    }

    public function pagoRegistroTalento()
    {
        return $this->belongsTo(PagoRegistroTalento::class, 'id_pago_registro_talento');
    }

    /**
     * Obtiene la respuesta de Azul desde los logs de pago.
     */
    public function getAzulResponseAttribute(): ?array
    {
        $log = \Illuminate\Support\Facades\DB::table('logs_pagos')
            ->where('custom_order_id', 'like', "INT-{$this->id}-%")
            ->where('is_success', true)
            ->first();

        if ($log && !empty($log->response_payload)) {
            $payload = json_decode($log->response_payload, true);
            if (is_array($payload)) {
                return $payload;
            }
        }

        return null;
    }
}
