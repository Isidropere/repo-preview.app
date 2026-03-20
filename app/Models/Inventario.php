<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Inventario extends Model
{
    use HasFactory;

    protected $table = 'inventario_items';

    protected $primaryKey = 'id_inventario';

    public $timestamps = false; // Ya tienes `fecha` como campo personalizado

    protected $fillable = [
        'id_item',
        'cantidad',
        'fecha',
    ];

    // Relación con la tabla items
    public function item()
    {
        return $this->belongsTo(Item::class, 'id_item', 'id_item');
    }
}

