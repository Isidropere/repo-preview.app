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
        'descuento',
        'fecha_servicio',
        'id_color'
    ];

    protected $casts = [
        'es_seleccionado' => 'boolean',
        'cantidad' => 'integer',
        'id_carrito' => 'integer',
        'id_item' => 'integer',
        'id_color' => 'integer',
    ];

    public function carrito()
    {
        return $this->belongsTo(Carrito::class, 'id_carrito', 'id_carrito');
    }

    public function categoria()
    {
        // ItemIntencionCompra no tiene id_categoria_item — acceder via item()
        return $this->item()->with('categoria');
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'id_item', 'id_item');
    }

    public function imagenes()
    {
        return $this->hasMany(ImagenItem::class, 'id_item', 'id_item');
    }

    // Usuario dueño del item (a través del item)
    public function usuario()
    {
        return $this->hasOneThrough(User::class, Item::class, 'id_item', 'id', 'id_item', 'id_user');
    }

    public function inventario()
    {
        // Acceder al inventario a través del item relacionado
        return $this->hasOneThrough(Inventario::class, Item::class, 'id_item', 'id_item', 'id_item', 'id_item');
    }

    public function color()
    {
        return $this->belongsTo(Color::class, 'id_color', 'id_color');
    }

}

