<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Helpers\HashIdHelper;

class CategoriaItem extends Model
{
    use HasFactory;

    protected $table = 'categorias_item';
    protected $primaryKey = 'id_categoria_item';
    public $incrementing = true;
    protected $keyType = 'integer';

    protected $fillable = ['categoria', 'aplica_impuesto'];

    protected $casts = [
        'aplica_impuesto' => 'boolean',
    ];

    public function items()
    {
        return $this->hasMany(Item::class, 'id_categoria_item');
    }

    /**
     * Slug: nombre-categoria-HASH
     * Ej: instrumentos-musicales-xK9mP
     */
    public function getSlugAttribute(): string
    {
        if (!$this->id_categoria_item) {
            return '';
        }
        $nombre = Str::slug($this->categoria ?? '');
        $hash   = HashIdHelper::encode((int) $this->id_categoria_item);
        return "{$nombre}-{$hash}";
    }
}
