<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacturaTransporteTransaccion extends Model
{
    use HasFactory;

    protected $table = 'facturas_transporte_transaccion';
    protected $primaryKey = 'id_factura';
    protected $fillable = [
        'id_delivery',
        'valor',
        'id_oferta',
        'id_user',
        'pagada'
    ];

    public function delivery()
    {
        return $this->belongsTo(Delivery::class, 'id_delivery');
    }

    public function oferta()
    {
        return $this->belongsTo(Oferta::class, 'id_oferta');
    }

    public function direcciones()
    {
        return $this->belongsTo(Direcciones::class, 'id_user');
    }
}
