<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CuentaBancoEmpresa extends Model
{
    protected $table = 'cuentas_banco_empresa';

    protected $fillable = [
        'banco',
        'numero_cuenta',
        'tipo_cuenta',
        'titular',
        'descripcion',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function scopeActivas($query)
    {
        return $query->where('activo', true);
    }
}
