<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Oferta extends Model
{
    use HasFactory;

    protected $table = 'ofertas';
    protected $primaryKey = 'id_oferta';
    public $timestamps = false;

    protected $fillable = [
        'oferente',
        'beneficiario',
        'fecha',
        'condicion',
        'id_paquete'
    ];

    public function paquete()
    {
        return $this->belongsTo(Paquete::class, 'id_paquete', 'id_paquete');
    }

    public function ItemsOfertados()
    {
        return $this->belongsTo(ItemOferta::class, 'id_paquete', 'id_paquete');
    }
}

