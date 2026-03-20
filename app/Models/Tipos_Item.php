<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tipos_Item extends Model
{
    /** @use HasFactory<\Database\Factories\Tipos_ItemFactory> */
    use HasFactory;

    protected $table = 'tipos_items';
    protected $primaryKey = 'id_tipo_item';
    protected $fillable = [
        'tipo_item',
        'creado_por',
    ];
}
