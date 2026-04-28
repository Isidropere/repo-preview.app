<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rating extends Model
{
    use HasFactory;

    protected $table = 'ratings';
    protected $primaryKey = 'id_rating';
    public $timestamps = false;

    protected $fillable = [
        'rating',
        'id_usuario',      // quien califica
        'id_miembro',      // quien recibe la calificación
    ];

    protected $casts = [
        'rating' => 'decimal:2',
        'fecha'  => 'datetime',
    ];

    /** Usuario que emite la calificación */
    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }

    /** Usuario que recibe la calificación */
    public function usuarioCalificado()
    {
        return $this->belongsTo(User::class, 'id_miembro');
    }
}
