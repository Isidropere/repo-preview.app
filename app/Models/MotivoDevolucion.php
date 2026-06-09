<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MotivoDevolucion extends Model
{
    use HasFactory;

    protected $table = 'motivos_devolucion';

    protected $fillable = [
        'motivo',
        'activo',
    ];
}
