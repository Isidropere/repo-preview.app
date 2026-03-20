<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo: OauthProvider
 *
 * Tabla: oauth_providers
 * Guarda la configuración de cada proveedor OAuth (Google, Facebook, Instagram).
 * Permite activar/desactivar proveedores y cambiar credenciales desde la BD.
 *
 * Columnas:
 *   provider      → 'google' | 'facebook' | 'instagram'
 *   client_id     → App ID / Client ID del proveedor
 *   client_secret → App Secret / Client Secret
 *   redirect_uri  → URL de callback registrada en el proveedor
 *   activo        → 1 = habilitado, 0 = deshabilitado
 */
class OauthProvider extends Model
{
    protected $table = 'oauth_providers';

    protected $fillable = [
        'provider',
        'client_id',
        'client_secret',
        'redirect_uri',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    /**
     * Obtener configuración de un proveedor por nombre.
     * Retorna null si no existe o está inactivo.
     */
    public static function getActive(string $provider): ?self
    {
        return static::where('provider', $provider)->where('activo', true)->first();
    }
}
