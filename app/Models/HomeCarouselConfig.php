<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomeCarouselConfig extends Model
{
    protected $table = 'home_carousel_configs';

    protected $fillable = [
        'clave',
        'valor',
    ];

    /**
     * Obtiene el valor de una configuración por su clave.
     *
     * @param string $clave
     * @param mixed $default
     * @return mixed
     */
    public static function get(string $clave, $default = null)
    {
        $config = self::where('clave', $clave)->first();
        return $config !== null ? $config->valor : $default;
    }

    /**
     * Actualiza o crea el valor de una configuración.
     *
     * @param string $clave
     * @param mixed $valor
     * @return void
     */
    public static function set(string $clave, $valor): void
    {
        self::updateOrCreate(['clave' => $clave], ['valor' => (string) $valor]);
    }
}
