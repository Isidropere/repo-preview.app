<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompraTrazabilidad extends Model
{
    protected $table = 'compra_trazabilidad';

    protected $fillable = [
        'id_pago_compra',
        'estado_anterior',
        'estado_nuevo',
        'nota',
        'id_admin',
    ];

    public function admin()
    {
        return $this->belongsTo(User::class, 'id_admin', 'id');
    }

    public function pago()
    {
        return $this->belongsTo(PagoCompra::class, 'id_pago_compra', 'id_pago_compra');
    }

    /**
     * Accesor para formatear la dirección en las notas de trazabilidad
     */
    public function getNotaAttribute($value)
    {
        if (empty($value)) {
            return $value;
        }

        // Buscar "Dirección: [ID]" o "Dirección ID: [ID]"
        if (preg_match('/Dirección(?:\s+ID)?:\s*(\d+)/i', $value, $matches)) {
            $direccionId = $matches[1];
            $direccion = \App\Models\Direcciones::with(['municipio', 'provincia'])->find($direccionId);
            if ($direccion) {
                $dirTexto = "{$direccion->calle}";
                if ($direccion->N_casa_edificio) $dirTexto .= ", #{$direccion->N_casa_edificio}";
                if ($direccion->municipio?->municipio) $dirTexto .= ", {$direccion->municipio->municipio}";
                if ($direccion->provincia?->provincia) $dirTexto .= ", {$direccion->provincia->provincia}";
                $value = str_replace($matches[0], "Dirección: " . $dirTexto, $value);
            }
        }

        return $value;
    }
}
