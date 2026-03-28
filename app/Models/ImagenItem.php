<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImagenItem extends Model
{
    protected $table = 'imagenes_item';
    protected $primaryKey = 'id_imagen';
    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'extension',
        'id_item',
        'orden_visualizacion',
        'ruta',
        'tipo',
        'estado',
        'motivo_rechazo',
    ];

    protected $attributes = [
        'estado' => 'pendiente',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class, 'id_item');
    }
}
