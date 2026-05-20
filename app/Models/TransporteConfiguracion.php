<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransporteConfiguracion extends Model
{
    protected $table = 'transporte_configuraciones';

    protected $fillable = [
        'clave',
        'valor',
    ];

    /**
     * Obtiene una configuración por su clave.
     * 
     * @param string $clave
     * @param mixed $default
     * @return string
     */
    public static function get($clave, $default = null)
    {
        $config = self::where('clave', $clave)->first();
        return $config ? $config->valor : $default;
    }

    /**
     * Actualiza o crea una configuración.
     * 
     * @param string $clave
     * @param string $valor
     * @return void
     */
    public static function set($clave, $valor)
    {
        self::updateOrCreate(['clave' => $clave], ['valor' => $valor]);
    }
}
