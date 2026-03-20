<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * ============================================================
 * Modelo: Carrito
 * ============================================================
 * Carrito de compras del usuario. Cada usuario tiene un solo
 * carrito (relación 1:1). Contiene items de intención de compra
 * que pueden ser seleccionados/deseleccionados para el pago.
 *
 * Tabla BD: carritos
 * Clave primaria: id_carrito
 * ============================================================
 */
class Carrito extends Model
{
    use HasFactory;

    protected $table = 'carritos';
    protected $primaryKey = 'id_carrito';
    protected $fillable = ['id_user'];
    public $timestamps = false;

    public function direcciones()
    {
        return $this->belongsTo(Direcciones::class, 'id_user');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_user', 'id');
    }

    public function itemsIntencionCompra()
    {
        return $this->hasMany(ItemIntencionCompra::class, 'id_carrito');
    }

    public function pagosCompra()
    {
        return $this->hasMany(PagoCompra::class, 'id_carrito');
    }
}
