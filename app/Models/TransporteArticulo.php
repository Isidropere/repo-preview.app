<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransporteArticulo extends Model
{
    use HasFactory;

    protected $table = 'transporte_articulos';

    protected $fillable = [
        'nombre',
        'categoria',
        'precio_base',
        'estatus',
    ];

    protected $casts = [
        'estatus' => 'boolean',
    ];

    /**
     * Relación con las solicitudes de transporte que han seleccionado este artículo
     */
    public function solicitudes()
    {
        return $this->belongsToMany(SolicitudTransporte::class, 'solicitud_transporte_articulo', 'articulo_id', 'solicitud_transporte_id')
                    ->withPivot('cantidad')
                    ->withTimestamps();
    }
}
