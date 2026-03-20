<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotaDetalle extends Model
{
    use HasFactory;

    protected $table = 'notas_detalles';
    protected $primaryKey = 'id_nota_detalle';
    protected $fillable = ['nota'];
}
