<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContDiarioDetalle extends Model
{
    use HasFactory;

    protected $table = 'cont_diario_detalles';

    protected $fillable = [
        'id_diario',
        'id_cuenta',
        'debe',
        'haber',
        'nota',
    ];

    public function diario()
    {
        return $this->belongsTo(ContDiario::class, 'id_diario');
    }

    public function cuenta()
    {
        return $this->belongsTo(ContCuenta::class, 'id_cuenta');
    }
}
