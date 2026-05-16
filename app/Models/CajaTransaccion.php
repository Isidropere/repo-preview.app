<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CajaTransaccion extends Model
{
    use HasFactory;

    protected $table = 'caja_transacciones';

    protected $fillable = [
        'id_sesion',
        'tipo',
        'monto',
        'concepto',
        'referencia_tipo',
        'referencia_id',
    ];

    public function sesion()
    {
        return $this->belongsTo(CajaSesion::class, 'id_sesion');
    }
}
