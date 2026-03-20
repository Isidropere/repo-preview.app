<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemIntencionCompra extends Model
{
    use HasFactory;

    protected $table = 'items_intencion_compra';
    // PK renombrada de id_item_itencion_compra → id_item_intencion_compra (migración 200006)
    protected $primaryKey = 'id_item_intencion_compra';
    public $timestamps = false;

    protected $fillable = [
        'id_carrito',
        'id_item',
        'cantidad',
        'es_seleccionado',
        'descuento'
    ];

    public function carrito()
    {
        return $this->belongsTo(Carrito::class, 'id_carrito', 'id_carrito');
    }

    public function categoria()
    {
        return $this->belongsTo(CategoriaItem::class, 'id_categoria_item', 'id_categoria_item');
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'id_item', 'id_item');
    }

    public function imagenes()
    {
        return $this->hasMany(ImagenItem::class, 'id_item', 'id_item');
    }
    public function Usuarios()
    {
        return $this->hasMany(user::class, 'id', 'id_user');
    }

    public function Inventarios()
    {
        return $this->hasMany(Inventario::class, 'id_item', 'id_item');
    }

}

