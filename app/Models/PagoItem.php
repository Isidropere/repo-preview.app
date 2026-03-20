<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Snapshot inmutable de los artículos al momento del pago.
 * Se crea en PagoController::procesar() antes de eliminar los items del carrito.
 */
class PagoItem extends Model
{
    protected $table = 'pago_items';

    protected $fillable = [
        'id_pago_compra',
        'id_item',
        'nombre_item',
        'precio_unitario',
        'cantidad',
        'descuento',
        'subtotal',
        'imagen_url',
    ];

    protected $casts = [
        'precio_unitario' => 'decimal:2',
        'descuento'       => 'decimal:2',
        'subtotal'        => 'decimal:2',
    ];

    public function pago()
    {
        return $this->belongsTo(PagoCompra::class, 'id_pago_compra', 'id_pago_compra');
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'id_item', 'id_item');
    }
}
