<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HomeCarousel extends Model
{
    use HasFactory;

    protected $table = 'home_carousels';

    protected $fillable = [
        'imagen',
        'titulo',
        'subtitulo',
        'enlace',
        'orden',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'orden'  => 'integer',
    ];

    protected $appends = [
        'imagen_url',
    ];

    /**
     * Scope para obtener solo diapositivas activas ordenadas.
     */
    public function scopeActivos($query)
    {
        return $query->where('activo', true)->orderBy('orden', 'asc')->orderBy('id', 'asc');
    }

    /**
     * Accesor para obtener la URL completa de la imagen.
     */
    public function getImagenUrlAttribute(): string
    {
        if (empty($this->imagen)) {
            return asset('imgs/1.jpg');
        }

        if (str_starts_with($this->imagen, 'http://') || str_starts_with($this->imagen, 'https://')) {
            return $this->imagen;
        }

        if (str_starts_with($this->imagen, '/')) {
            return url($this->imagen);
        }

        return asset('storage/' . $this->imagen);
    }
}
