<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SolicitudTransporte extends Model
{
    use HasFactory;

    protected $table = 'solicitudes_transporte';

    protected $fillable = [
        'id_usuario',
        'nombre',
        'apellido',
        'cedula',
        'direccion',
        'telefono',
        'correo',
        'fecha_servicio',
        'ubicacion_geologica',
        'dimensiones_carga',
        'estado',
    ];

    protected $casts = [
        'fecha_servicio' => 'date',
    ];

    /**
     * Relación opcional con el usuario
     */
    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }
}
