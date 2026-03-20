<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Provincia extends Model
{
    use HasFactory;

    protected $table = 'provincias';
    protected $primaryKey = 'id_provincia';
    protected $keyType = 'string';
    public $incrementing = false;
    protected $fillable = ['id_provincia', 'provincia'];

    public function municipios()
    {
        return $this->hasMany(Municipio::class, 'id_provincia');
    }
}
