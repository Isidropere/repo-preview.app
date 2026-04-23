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
        'emisor_confirmado', 'items_ofrecidos', 'pago_emisor', 'pago_receptor',
    ];

    protected $casts = [
        'monto_oferta'        => 'decimal:2',
        'monto_contra_oferta' => 'decimal:2',
        'fecha_creacion'      => 'datetime',
        'emisor_confirmado'   => 'boolean',
        'items_ofrecidos'     => 'array',
        'pago_emisor'         => 'boolean',
        'pago_receptor'       => 'boolean',
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
}
