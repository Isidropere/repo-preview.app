<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConfigTarifaCategoria29 extends Model
{
    protected $table = 'config_tarifa_categoria29';

    protected $fillable = [
        'monto_registro',
        'descuento_venta_masiva',
        'cantidad_minima_descuento',
    ];

    protected $casts = [
        'monto_registro'            => 'decimal:2',
        'descuento_venta_masiva'    => 'decimal:2',
        'cantidad_minima_descuento' => 'integer',
    ];

    /**
     * Retorna el único registro o un objeto con defaults si la tabla está vacía.
     */
    public static function vigente(): self
    {
        return cache()->remember('config_tarifa_cat29', 3600, function () {
            return static::first() ?? new static([
                'monto_registro'            => 0,
                'descuento_venta_masiva'    => 0,
                'cantidad_minima_descuento' => 1,
            ]);
        });
    }
}
