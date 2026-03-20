<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoriaItem extends Model
{
    use HasFactory;

    protected $table = 'categorias_item';
    protected $primaryKey = 'id_categoria_item';
    public $incrementing = true; // Asegúrate que sea true si es auto-incremental
    protected $keyType = 'integer'; // Tipo de la clave primaria

    protected $fillable = ['categoria'];

    public function items()
    {
        return $this->hasMany(Item::class, 'id_categoria_item');
    }
}
