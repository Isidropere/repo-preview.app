<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CajaSesion extends Model
{
    use HasFactory;

    protected $table = 'caja_sesiones';

    protected $fillable = [
        'id_usuario_abre',
        'id_usuario_cierra',
        'fecha_apertura',
        'fecha_cierre',
        'monto_inicial',
        'monto_final_esperado',
        'monto_final_real',
        'diferencia',
        'nota',
        'estado',
    ];

    public function transacciones()
    {
        return $this->hasMany(CajaTransaccion::class, 'id_sesion');
    }

    public function usuarioAbre()
    {
        return $this->belongsTo(User::class, 'id_usuario_abre');
    }

    public function usuarioCierra()
    {
        return $this->belongsTo(User::class, 'id_usuario_cierra');
    }
}
