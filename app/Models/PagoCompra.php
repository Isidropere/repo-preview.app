<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * ============================================================
 * Modelo: PagoCompra
 * ============================================================
 * Registro de un pago procesado. Se crea al completar el
 * checkout exitosamente. Contiene el monto total, tarjeta
 * usada, código de autorización y dirección de envío.
 *
 * Tabla BD: pagos_compra
 * Clave primaria: id_pago_compra (UUID string)
 *
 * Estados (estatus):
 *   pendiente → aprobado → enviado → entregado
 *                        → rechazado / cancelado
 * ============================================================
 */
class PagoCompra extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::creating(function (PagoCompra $model) {
            if (empty($model->id_pago_compra)) {
                $model->id_pago_compra = (string) \Illuminate\Support\Str::uuid();
            }
        });
    }

    protected $table = 'pagos_compra';
    protected $primaryKey = 'id_pago_compra';
    protected $keyType = 'string';
    public $incrementing = false;
    protected $fillable = [
        'id_pago_compra',
        'id_carrito',
        'estatus',
        'id_tarjeta',
        'autorizacion_pago',
        'id_proveedor_pago',
        'transaction_id',    // pnRef (CardNet) o pi_xxx (Stripe) — para anulaciones/reembolsos
        'total',             // Monto total al momento del pago
        'cantidad_items',    // Cantidad de artículos al momento del pago
        'id_direccion',      // Dirección de envío capturada al momento del pago
        'tracking_code',     // Código/sufijo de rastreo del envío
        'tracking_url',      // URL completa de rastreo construida por el admin
        'id_motivo_devolucion',
        'comentario_devolucion',
    ];

    public $timestamps = true;

    // Mapear la columna 'fecha' de la BD como fecha de creación
    const CREATED_AT = 'fecha';
    const UPDATED_AT = null;

    protected $casts = [
        'fecha' => 'datetime',
        'total' => 'decimal:2',
    ];

    public function carrito()
    {
        return $this->belongsTo(Carrito::class, 'id_carrito');
    }

    public function tarjeta()
    {
        return $this->belongsTo(TarjetaPago::class, 'id_tarjeta');
    }

    public function proveedorPago()
    {
        return $this->belongsTo(ProveedorPago::class, 'id_proveedor_pago');
    }

    public function trazabilidad()
    {
        return $this->hasMany(CompraTrazabilidad::class, 'id_pago_compra', 'id_pago_compra')
            ->orderBy('created_at', 'asc');
    }

    /**
     * Comprador de la orden: pagos_compra.id_carrito → carritos.id_user → users.id
     * Usar eager loading: with('carrito.usuario')
     * El accessor delega a carrito->usuario para evitar problemas con hasOneThrough
     * cuando la FK está en pagos_compra (no en carritos).
     */
    public function getCompradorAttribute(): ?User
    {
        return $this->carrito?->usuario;
    }

    public function items()
    {
        return $this->hasMany(ItemIntencionCompra::class, 'id_carrito', 'id_carrito');
    }

    public function pagoItems()
    {
        return $this->hasMany(PagoItem::class, 'id_pago_compra', 'id_pago_compra');
    }

    public function direccion()
    {
        return $this->belongsTo(Direcciones::class, 'id_direccion', 'id_direccion');
    }

    public function motivoDevolucion()
    {
        return $this->belongsTo(MotivoDevolucion::class, 'id_motivo_devolucion');
    }
}
