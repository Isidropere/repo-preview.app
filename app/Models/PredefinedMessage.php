<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PredefinedMessage extends Model
{
    use HasFactory;

    // Nombre de la tabla en la base de datos
    protected $table = 'predefined_messages';

    // Clave primaria
    protected $primaryKey = 'id';

    // Si la clave es auto incremental
    public $incrementing = true;

    // Tipo de clave primaria
    protected $keyType = 'int';

    // Campos que se pueden asignar masivamente
    protected $fillable = [
        'titulo',
        'mensaje',
        'tipo',
        'rol',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    // Si tu tabla tiene timestamps (created_at, updated_at)
    public $timestamps = true;
}
