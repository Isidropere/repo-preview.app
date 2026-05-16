<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Almacen extends Model
{
    use HasFactory;

    protected $table = 'almacenes';

    protected $fillable = [
        'nombre',
        'ubicacion',
        'estado',
    ];

    public function movimientos()
    {
        return $this->hasMany(InventarioMovimiento::class, 'id_almacen');
    }
}
