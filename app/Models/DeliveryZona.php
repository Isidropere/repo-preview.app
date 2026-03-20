<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryZona extends Model
{
    protected $table    = 'delivery_zonas';
    protected $fillable = ['zona', 'tipo', 'pueblos', 'precio_empresa', 'precio_persona', 'dias_entrega', 'activo'];
    protected $casts    = ['pueblos' => 'array', 'activo' => 'boolean'];
}
