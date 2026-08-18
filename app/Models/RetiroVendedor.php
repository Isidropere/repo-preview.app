<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RetiroVendedor extends Model
{
    use HasFactory;

    protected $table = 'retiros_vendedor';

    protected $fillable = [
        'id_usuario',
        'monto',
        'estado',
        'comprobante_url',
        'notas',
        'id_cuenta_bancaria',
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }

    public function cuentaBancaria()
    {
        return $this->belongsTo(CuentaBancariaUsuario::class, 'id_cuenta_bancaria');
    }
}
