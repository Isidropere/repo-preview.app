<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Miembro extends Model
{
    use HasFactory;

    protected $table = 'miembros';
    protected $primaryKey = 'id_miembro';
    public $incrementing = false;
    protected $fillable = [
        'id_miembro',
        'nombres',
        'apellidos',
        'email',
        'telefono',
        'id_plan',
        'calle',
        'casa_numero',
        'apto',
        'edificio',
        'id_provincia',
        'id_municipio',
        'geolocalizacion'
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_miembro', 'id_usuario');
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class, 'id_plan');
    }

    public function provincia()
    {
        return $this->belongsTo(Provincia::class, 'id_provincia');
    }

    public function municipio()
    {
        return $this->belongsTo(Municipio::class, 'id_municipio');
    }

    public function items()
    {
        return $this->hasMany(Item::class, 'id_miembro');
    }

    public function carritos()
    {
        return $this->hasMany(Carrito::class, 'id_miembro');
    }

    public function tarjetasPago()
    {
        return $this->hasMany(TarjetaPago::class, 'id_miembro');
    }
}
