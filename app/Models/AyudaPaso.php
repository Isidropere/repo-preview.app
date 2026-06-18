<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AyudaPaso extends Model
{
    use HasFactory;

    protected $table = 'ayuda_pasos';

    protected $fillable = [
        'ayuda_pagina_id',
        'orden',
        'titulo',
        'descripcion',
        'imagen',
    ];

    public function pagina(): BelongsTo
    {
        return $this->belongsTo(AyudaPagina::class, 'ayuda_pagina_id');
    }
}
