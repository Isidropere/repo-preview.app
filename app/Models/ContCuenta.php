<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContCuenta extends Model
{
    use HasFactory;

    protected $table = 'cont_cuentas';

    protected $fillable = [
        'codigo',
        'nombre',
        'tipo',
        'nivel',
        'id_padre',
        'permite_movimiento',
    ];

    public function padre()
    {
        return $this->belongsTo(ContCuenta::class, 'id_padre');
    }

    public function hijos()
    {
        return $this->hasMany(ContCuenta::class, 'id_padre');
    }
}
