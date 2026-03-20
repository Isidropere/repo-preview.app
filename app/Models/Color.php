<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Color extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'colors';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id_color';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'nombre',
        'codigo_hex'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    //protected $casts = [
    //    'created_at' => 'datetime',
    //    'updated_at' => 'datetime',
    //];

    /**
     * Obtiene los items asociados con este color.
     */
    public function items(): BelongsToMany
    {
        return $this->belongsToMany(Item::class, 'item_color', 'color_id', 'item_id')
            ->using(ItemColor::class)
            ->withPivot('stock')
            ->withTimestamps();
    }
}
