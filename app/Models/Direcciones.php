<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Direcciones extends Model
{
    use HasFactory;

    // Configuración básica del modelo
    protected $table = 'direcciones';
    protected $primaryKey = 'id_direccion';
    public $incrementing = false;
    public $timestamps = false; // Desactiva created_at y updated_at

    // Campos asignables masivamente
    protected $fillable = [
       'id_direccion',
        'calle',
        'N_casa_edificio',
        'apto',
        'id_provincia',
        'id_municipio',
        'geolocalizacion',
        'id_user',
        'sector',
        'telefono_contacto',
        'es_predeterminada'

    ];

    // Relaciones
    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_user');
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
        return $this->hasMany(Item::class, 'id_user');
    }

    public function carritos()
    {
        return $this->hasMany(Carrito::class, 'id_user');
    }

    public function tarjetasPago()
    {
        return $this->hasMany(TarjetaPago::class, 'id_user');
    }

  
}
