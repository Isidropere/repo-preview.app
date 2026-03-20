<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;

class Tipos_usuario extends Model
{
    use HasFactory, Notifiable;
    //

    protected $primaryKey = 'id_tipo_usuario';

    protected $fillable = [

        'tipo',

    ];


}
