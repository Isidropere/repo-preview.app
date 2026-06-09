<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Helpers\HashIdHelper;
use Illuminate\Support\Str;
use Laravel\Scout\Searchable;

/**
 * ============================================================
 * Modelo: Item (Artículo / Producto / Talento)
 * ============================================================
 * Representa un artículo publicado en la plataforma.
 *
 * Tabla BD: items
 * Clave primaria: id_item
 *
 * Tipos de transacción (tipo_trans):
 *   1 = Venta
 *   2 = Intercambio
 *   3 = Venta + Intercambio (ambos)
 *
 * Condición (condicion):
 *   1 = Nuevo, 2 = Usado, 3 = Reacondicionado, 4 = Como nuevo
 *
 * Estatus: 1 = Activo, 0 = Inactivo
 *
 * Categoría 29 = Talentos (servicios, excluidos de delivery)
 * ============================================================
 */
class Item extends Model
{
    use HasFactory, Searchable;

    protected static function booted()
    {
        static::saved(function ($item) {
            \Illuminate\Support\Facades\Cache::flush();
        });

        static::deleted(function ($item) {
            \Illuminate\Support\Facades\Cache::flush();
        });
    }
 
    protected $table = 'items';
    protected $primaryKey = 'id_item';
    public $incrementing = true;
    protected $appends = ['slug'];
    protected $fillable = [
        'id_item',
        'item',
        'id_categoria_item',
        'peso_lbs',
        'alto_cm',
        'ancho_cm',
        'profundo_cm',
        'estatus',
        'id_user',
        'fecha',
        'tipo_trans',
        'condicion',
        'id_tipo_item',
        'valor',
        'descuento',
        'presentacion',
        'tiene_video'
    ];

    public function categoria()
    {
        return $this->belongsTo(CategoriaItem::class, 'id_categoria_item');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    /**
     * Dirección predeterminada del dueño del item (via id_user).
     * Usamos hasOneThrough-like: item → usuario → dirección predeterminada.
     * Para eager loading en listados usamos hasManyThrough simplificado.
     */
    public function direcciones()
    {
        return $this->hasMany(Direcciones::class, 'id_user', 'id_user');
    }

    public function direccionPredeterminada()
    {
        return $this->hasOne(Direcciones::class, 'id_user', 'id_user')
            ->where('es_predeterminada', 1)
            ->with(['municipio:id_municipio,municipio', 'provincia:id_provincia,provincia']);
    }

    public function itemsIntencionCompra()
    {
        return $this->hasMany(ItemIntencionCompra::class, 'id_item');
    }

    public function itemsOferta()
    {
        return $this->hasMany(ItemOferta::class, 'id_item');
    }

    // app/Models/Item.php
    public function imagenes()
    {
        return $this->hasMany(ImagenItem::class, 'id_item')->where('estado', 'aprobado');
    }

    public function todasLasImagenes()
    {
        return $this->hasMany(ImagenItem::class, 'id_item');
    }
    public function views()
    {
        return $this->hasMany(ItemView::class, 'id_item');
    }

    // En User.php
    public function sentMessages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function receivedMessages()
    {
        return $this->hasMany(Message::class, 'receiver_id');
    }

    // En Item.php
    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    // obtener los inventario

    public function inventarios()
    {
        return $this->hasOne(Inventario::class, 'id_item', 'id_item');
    }

    /**
     * Verifica si hay stock disponible para la cantidad solicitada.
     */
    public function tieneStock(int $cantidad = 1): bool
    {
        return ($this->inventarios?->cantidad ?? 0) >= $cantidad;
    }

    /**
     * Retorna el stock disponible.
     */
    public function getStockAttribute(): int
    {
        return $this->inventarios?->cantidad ?? 0;
    }

    /**
     * Verifica si el item está activo y con stock.
     */
    public function estaDisponible(int $cantidad = 1): bool
    {
        return $this->estatus == 1 && $this->tieneStock($cantidad);
    }


    /**
     * Obtiene los colores asociados con este item.
     */
    public function colors(): BelongsToMany
    {
        return $this->belongsToMany(Color::class, 'item_color', 'item_id', 'color_id')
            ->using(ItemColor::class)
            ->withPivot('stock');
    }

    public $timestamps = true;
    const CREATED_AT = 'fecha';
    const UPDATED_AT = null;

    protected $casts = [
        'valor'     => 'decimal:2',
        'descuento' => 'decimal:2',
        'peso_lbs'  => 'decimal:2',
        'alto_cm'   => 'decimal:2',
        'ancho_cm'  => 'decimal:2',
        'profundo_cm' => 'decimal:2',
    ];

    /**
     * URL slug: nombre-del-item-HASH
     * Ej: iphone-14-pro-max-xK9mP
     */
    public function getSlugAttribute(): string
    {
        if (!$this->id_item) {
            return '';
        }
        $nombre = Str::slug($this->item ?? '');
        $hash   = HashIdHelper::encode((int) $this->id_item);
        return "{$nombre}-{$hash}";
    }

    public function pagoRegistro()
    {
        return $this->hasOne(PagoRegistroTalento::class, 'id_item', 'id_item');
    }

    /**
     * Get the indexable data array for the model.
     *
     * @return array<string, mixed>
     */
    public function toSearchableArray(): array
    {
        return [
            'id_item'           => (int) $this->id_item,
            'item'              => $this->item,
            'presentacion'      => $this->presentacion,
            'categoria'         => $this->categoria?->categoria,
            'id_categoria_item' => (int) $this->id_categoria_item,
            'estatus'           => (int) $this->estatus,
            'tipo_trans'        => (int) $this->tipo_trans,
            'id_user'           => (int) $this->id_user,
            'valor'             => (float) $this->valor,
        ];
    }

    public function getScoutKey(): mixed
    {
        return $this->id_item;
    }

    public function getScoutKeyName(): mixed
    {
        return 'id_item';
    }
}
