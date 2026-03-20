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
}
