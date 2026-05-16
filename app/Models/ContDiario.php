<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContDiario extends Model
{
    use HasFactory;

    protected $table = 'cont_diario';

    protected $fillable = [
        'fecha',
        'concepto',
        'total_debe',
        'total_haber',
        'referencia_tipo',
        'referencia_id',
        'estado',
        'id_usuario_crea',
    ];

    public function detalles()
    {
        return $this->hasMany(ContDiarioDetalle::class, 'id_diario');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario_crea');
    }
}
