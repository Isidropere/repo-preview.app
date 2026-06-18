<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AyudaPagina extends Model
{
    use HasFactory;

    protected $table = 'ayuda_paginas';

    protected $fillable = [
        'slug',
        'titulo',
        'descripcion',
    ];

    public function pasos(): HasMany
    {
        return $this->hasMany(AyudaPaso::class, 'ayuda_pagina_id')->orderBy('orden', 'asc');
    }
}
