<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Nota extends Model
{
    use HasFactory;

    protected $table = 'notas';
    protected $primaryKey = 'id_nota';
    protected $fillable = ['id_oferta', 'visualizado'];

    public function oferta()
    {
        return $this->belongsTo(Oferta::class, 'id_oferta');
    }
}
