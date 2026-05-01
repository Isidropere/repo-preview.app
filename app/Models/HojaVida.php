<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HojaVida extends Model
{
    protected $table = 'hojas_vida';

    protected $fillable = [
        'id_user',
        'nombres',
        'apellidos',
        'titulo_profesional',
        'descripcion_bio',
        'habilidades',
        'experiencia',
        'ubicacion',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id');
    }
}
