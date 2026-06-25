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
        'impuestos',
        'costo_envio',
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
        'impuestos' => 'decimal:2',
        'costo_envio' => 'decimal:2',
    ];

    /**
     * Obtiene la respuesta de Azul desde los logs de pago.
     */
    public function getAzulResponseAttribute(): ?array
    {
        if (isset($this->azul_response_data)) {
            return $this->azul_response_data;
        }

        $log = \Illuminate\Support\Facades\DB::table('logs_pagos')
            ->where('custom_order_id', $this->id_pago_compra)
            ->where('is_success', true)
            ->whereIn('transaction_type', ['sale_approved', 'sale'])
            ->first();

        if ($log && !empty($log->response_payload)) {
            $payload = json_decode($log->response_payload, true);
            if (is_array($payload)) {
                return $payload;
            }
        }

        return null;
    }

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

    /**
     * Obtiene el primer artículo asociado al pago para compatibilidad con la vista de detalle de ventas.
     */
    public function getItemAttribute()
    {
        return $this->pagoItems->first()?->item;
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

    /**
     * Libera todas las órdenes que han estado pendientes por más de 10 minutos (expiradas) y restaura su stock.
     */
    public static function liberarTodasLasOrdenesPendientesExpiradas(): void
    {
        try {
            $limite = now()->subMinutes(10);
            $ordenesExpiradas = self::where('estatus', 'pendiente')
                ->where('fecha', '<=', $limite)
                ->get();

            foreach ($ordenesExpiradas as $pagoCompra) {
                \Illuminate\Support\Facades\DB::transaction(function () use ($pagoCompra) {
                    $pagoCompra->estatus = 'cancelado';
                    $pagoCompra->save();

                    // Registrar trazabilidad
                    CompraTrazabilidad::create([
                        'id_pago_compra'  => $pagoCompra->id_pago_compra,
                        'estado_anterior' => 'pendiente',
                        'estado_nuevo'    => 'cancelado',
                        'nota'            => 'Liberado automáticamente por expiración del tiempo de pago (10 minutos)',
                        'id_admin'        => null,
                    ]);
                });
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Error al liberar órdenes pendientes expiradas: ' . $e->getMessage());
        }
    }

    /**
     * Libera cualquier orden pendiente asociada a un carrito y restaura su stock.
     */
    public static function liberarOrdenesPendientes($id_carrito): void
    {
        // Primero liberamos las expiradas globales
        self::liberarTodasLasOrdenesPendientesExpiradas();

        $ordenesPendientes = self::where('id_carrito', $id_carrito)
            ->where('estatus', 'pendiente')
            ->get();

        foreach ($ordenesPendientes as $pagoCompra) {
            \Illuminate\Support\Facades\DB::transaction(function () use ($pagoCompra) {
                $pagoCompra->estatus = 'cancelado';
                $pagoCompra->save();

                // Registrar trazabilidad
                CompraTrazabilidad::create([
                    'id_pago_compra'  => $pagoCompra->id_pago_compra,
                    'estado_anterior' => 'pendiente',
                    'estado_nuevo'    => 'cancelado',
                    'nota'            => 'Liberado por inicio de nueva sesión o recarga de checkout',
                    'id_admin'        => null,
                ]);
            });
        }
    }
}

