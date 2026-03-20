<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Delivery extends Model
{
    use HasFactory;

    protected $table = 'deliveries';
    protected $primaryKey = 'id_delivery';
    protected $fillable = ['empresa', 'email', 'telefono'];

    public function facturasTransporte()
    {
        return $this->hasMany(FacturaTransporteTransaccion::class, 'id_delivery');
    }
}
