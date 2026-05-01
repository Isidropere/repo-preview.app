<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SolicitudServicio extends Model
{
    protected $table = 'solicitudes_servicio';
    protected $primaryKey = 'id_solicitud';
    public $timestamps = false;

    protected $fillable = [
        'id_comprador',
        'id_proveedor',
        'id_item',
        'id_carrito',
        'cantidad',
        'monto_total',
        'estado',
        'fecha_creacion',
        'fecha_actualizacion',
    ];

    protected $casts = [
        'monto_total'         => 'decimal:2',
        'fecha_creacion'      => 'datetime',
        'fecha_actualizacion' => 'datetime',
    ];

    public function comprador()
    {
        return $this->belongsTo(User::class, 'id_comprador');
    }

    public function proveedor()
    {
        return $this->belongsTo(User::class, 'id_proveedor');
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'id_item', 'id_item');
    }

    public function carrito()
    {
        return $this->belongsTo(Carrito::class, 'id_carrito', 'id_carrito');
    }
}
