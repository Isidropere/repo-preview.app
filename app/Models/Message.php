<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    protected $table = 'messages';
    protected $primaryKey = 'id';

    protected $fillable = [
        'id_emisor',
        'id_receptor',
        'id_oferta',
        'id_paquete',
        'mensaje',
        'leido'
    ];

    public function sender()
    {
        return $this->belongsTo(User::class, 'id_emisor', 'id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'id_receptor', 'id');
    }


    public function itemPaquete()
    {
        return $this->hasMany(Itemoferta::class, 'id_paquete', 'id_paquete');
    }
    public function itemOferta()
    {
        return $this->belongsTo(Itemoferta::class, 'id_oferta', 'id_oferta');
    }
    public function scopeUnread($query)
    {
        return $query->where('leido', false);
    }
}
