<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Negociacion extends Model
{
    use HasFactory;

    protected $table      = 'negociaciones';
    protected $primaryKey = 'id_negociacion';
    public    $timestamps = false;

    protected $fillable = [
        'receptor_item_id', 'emisor_paquete_id', 'usuario_emisor_id', 'usuario_receptor_id',
        'mensaje_inicial', 'monto_oferta', 'monto_contra_oferta', 'estado', 'fecha_creacion',
        'emisor_confirmado', 'receptor_confirmado', 'items_ofrecidos', 'pago_emisor', 'pago_receptor',
        'modo_entrega', 'entrega_confirmada', 'id_color', 'tracking_code', 'tracking_url',
    ];

    protected $casts = [
        'monto_oferta'        => 'decimal:2',
        'monto_contra_oferta' => 'decimal:2',
        'fecha_creacion'      => 'datetime',
        'emisor_confirmado'   => 'boolean',
        'receptor_confirmado' => 'boolean',
        'items_ofrecidos'     => 'array',
        'pago_emisor'         => 'boolean',
        'pago_receptor'       => 'boolean',
        'entrega_confirmada'  => 'boolean',
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_emisor_id');
    }

    public function usuarioReceptor()
    {
        return $this->belongsTo(User::class, 'usuario_receptor_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'receptor_item_id');
    }

    public function pagoEnvios()
    {
        return $this->hasMany(PagoEnvioIntercambio::class, 'id_negociacion', 'id_negociacion');
    }

    public function trazabilidad()
    {
        return $this->hasMany(NegociacionTrazabilidad::class, 'id_negociacion', 'id_negociacion')
            ->orderBy('created_at', 'asc');
    }

    public function getItemsOfrecidosAttribute($value)
    {
        if (!$value) {
            return [];
        }
        $decoded = json_decode($value, true);
        if (!is_array($decoded)) {
            return [];
        }
        
        // If it is associative (meaning any key is not an integer from 0 to N-1, or we can check if keys are item IDs):
        if (array_keys($decoded) !== range(0, count($decoded) - 1)) {
            return array_map('intval', array_keys($decoded));
        }
        
        return array_map('intval', $decoded);
    }

    public function getCantidadOfrecida(int $idItem): int
    {
        $raw = $this->attributes['items_ofrecidos'] ?? null;
        if (!$raw) {
            return 1;
        }
        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            return 1;
        }
        
        if (array_keys($decoded) !== range(0, count($decoded) - 1)) {
            return (int) ($decoded[$idItem] ?? 1);
        }
        
        return in_array($idItem, $decoded) ? 1 : 0;
    }
}
