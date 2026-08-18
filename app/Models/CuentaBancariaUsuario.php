<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CuentaBancariaUsuario extends Model
{
    use HasFactory;

    protected $table = 'cuentas_bancarias_usuarios';

    protected $fillable = [
        'id_usuario',
        'banco',
        'tipo_cuenta',
        'numero_cuenta',
        'titular',
        'cedula_titular',
        'es_principal',
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }
}
