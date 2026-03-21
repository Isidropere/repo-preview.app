<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PagoRegistroTalento extends Model
{
    protected $table = 'pagos_registro_talento';

    protected $fillable = [
        'id_item',
        'id_user',
        'transaction_id',
        'monto_pagado',
        'estatus',
        'notas',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class, 'id_item', 'id_item');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }
}
