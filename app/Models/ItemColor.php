<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class ItemColor extends Pivot
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'item_color';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = true;
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'item_id',
        'color_id',
        'stock'
    ];

    /**
     * The attributes that should be cast.
     *
     //* @var array
     */
    //protected $casts = [
    //    'created_at' => 'datetime',
    //    'updated_at' => 'datetime',
    //];

    /**
     * Obtiene el color asociado.
     */
    public function color()
    {
        return $this->belongsTo(Color::class, 'color_id', 'id_color');
    }

    /**
     * Obtiene el item asociado.
     */
    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id', 'id_item');
    }
}
