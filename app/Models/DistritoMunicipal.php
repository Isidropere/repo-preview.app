<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DistritoMunicipal extends Model
{
    use HasFactory;

    protected $table = 'distritos_municipales';
    protected $primaryKey = 'id_distmunicipal';
    protected $keyType = 'string';
    public $incrementing = false;
    protected $fillable = ['id_distmunicipal', 'distrito_municipal', 'id_municipio'];

    public function municipio()
    {
        return $this->belongsTo(Municipio::class, 'id_municipio');
    }
}
