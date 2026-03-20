<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ItemView extends Model
{
    use HasFactory;

    // Nombre de la tabla si no sigue la convención 'item_views'
    protected $table = 'item_views';

    // Laravel asume 'id' como clave primaria por defecto
    protected $primaryKey = 'id';

    // Timestamps automáticos: como solo tienes created_at, desactiva updated_at
    public $timestamps = false;

    protected $fillable = [
        'id_item',
        'ip_address',
        'user_agent',
        'created_at'
    ];

    /**
     * Relación con el modelo Item
     */
    public function item()
    {
        return $this->belongsTo(Item::class, 'id_item', 'id_item');
    }
}
