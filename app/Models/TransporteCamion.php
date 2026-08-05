<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransporteCamion extends Model
{
    use HasFactory;
    
    protected $table = 'transporte_camiones';

    protected $fillable = [
        'nombre',
        'medida_pies',
        'precio_base',
        'activo',
    ];

    protected $casts = [
        'medida_pies' => 'float',
        'precio_base' => 'float',
        'activo' => 'boolean',
    ];
}
