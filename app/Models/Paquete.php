<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Paquete extends Model
{
    use HasFactory;

    protected $table = 'paquetes';
    protected $primaryKey = 'id_paquete';
    public $timestamps = false; // La tabla usa 'fecha' como columna de fecha, no created_at/updated_at

    protected $fillable = [
        'nombre_paquete', 
        'estatus',
        'id_user',
        'fecha'
    ];

    public function itemsOferta()
    {
        return $this->hasMany(ItemOferta::class, 'id_paquete', 'id_paquete');
    }

    public function ofertas()
    {
        return $this->hasMany(Oferta::class, 'id_paquete', 'id_paquete');
    }
}
