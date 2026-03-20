<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProveedorPago extends Model
{
    use HasFactory;

    protected $table = 'proveedores_pago';
    protected $primaryKey = 'id_proveedor_pago';
    protected $fillable = ['proveedor_pago'];

    public function pagosCompra()
    {
        return $this->hasMany(PagoCompra::class, 'id_proveedor_pago');
    }
}
