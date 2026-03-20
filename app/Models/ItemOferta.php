<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemOferta extends Model
{
    use HasFactory;

    protected $table = 'items_oferta';
    protected $primaryKey = 'id_item_oferta';
    public $timestamps = true;

    protected $fillable = [
        'id_paquete',
        'id_item',
        'fecha'
    ];

    public function paquete()
    {
        return $this->belongsTo(Paquete::class, 'id_paquete', 'id_paquete');
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'id_item', 'id_item');
    }
}
