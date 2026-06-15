<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NegociacionTrazabilidad extends Model
{
    protected $table = 'negociacion_trazabilidad';

    protected $fillable = [
        'id_negociacion',
        'estado_anterior',
        'estado_nuevo',
        'nota',
        'id_admin',
    ];

    public function admin()
    {
        return $this->belongsTo(User::class, 'id_admin', 'id');
    }

    public function negociacion()
    {
        return $this->belongsTo(Negociacion::class, 'id_negociacion', 'id_negociacion');
    }
}
