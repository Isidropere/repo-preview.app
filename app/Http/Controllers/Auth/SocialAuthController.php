<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\OauthProvider;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

/**
 * SocialAuthController
 *
 * Maneja OAuth para Google, Facebook e Instagram.
 * Las credenciales se leen primero desde la tabla oauth_providers (BD),
 * y si no están configuradas ahí, cae al .env como fallback.
 *
 * Rutas:
 *   GET /auth/{provider}          → redirect()
 *   GET /auth/{provider}/callback → callback()
 *
 * Proveedores soportados: google | facebook | instagram
 */
class SocialAuthController extends Controller
{
    private const ALLOWED = ['google', 'facebook', 'instagram'];

    /**
     * Redirige al proveedor OAuth.
     */
    public function redirect(string $provider)
    {
        if (!in_array($provider, self::ALLOWED)) {
            abort(404);
        }

        $this->configureProvider($provider);

        return Socialite::driver($provider)->redirect();
    }

    /**
     * Maneja el callback del proveedor OAuth.
     */
    public function callback(string $provider)
    {
        if (!in_array($provider, self::ALLOWED)) {
            abort(404);
        }

        try {
            $this->configureProvider($provider);
            $socialUser = Socialite::driver($provider)->user();
        } catch (\Throwable $e) {
            Log::error("OAuth callback error [{$provider}]", ['error' => $e->getMessage()]);
            return redirect()->route('login')->withErrors([
                'credentials' => "No se pudo autenticar con {$provider}. Intenta de nuevo.",
            ]);
        }

        $idField = $provider . '_id'; // google_id | facebook_id | instagram_id

        // Buscar usuario existente por provider_id o por email
        $user = User::where($idField, $socialUser->getId())->first()
            ?? User::where('email', $socialUser->getEmail())->first();

        if ($user) {
            // Vincular el provider_id si aún no lo tiene
            if (!$user->$idField) {
                $user->$idField = $socialUser->getId();
                $user->save();
            }
        } else {
            // Crear nuevo usuario
            $nameParts = explode(' ', $socialUser->getName() ?? '', 2);
            $nombres   = $nameParts[0] ?? 'Usuario';
            $apellidos = $nameParts[1] ?? '';

            $baseUsername = strtolower(preg_replace('/\s+/', '', ($socialUser->getName() ?? 'user')));
            $username = $baseUsername;
            if (User::where('nombre_usuario', $username)->exists()) {
                $username = $baseUsername . '_' . Str::random(4);
            }

            $user = User::create([
                'nombres'           => $nombres,
                'apellidos'         => $apellidos,
                'email'             => $socialUser->getEmail() ?? $socialUser->getId() . '@' . $provider . '.oauth',
                'telefono'          => '',
                $idField            => $socialUser->getId(),
                'nombre_usuario'    => $username,
                'password'          => bcrypt(Str::random(24)),
                'estatus'           => 1,
                'id_tipo_usuario'   => 1,
                'email_verified_at' => now(), // OAuth = email ya verificado
            ]);
        }

        Auth::login($user, true);

        return redirect()->intended(route('home'));
    }

    /**
     * Configura Socialite con credenciales desde la BD (si existen y están activas),
     * o usa el .env como fallback.
     */
    private function configureProvider(string $provider): void
    {
        $config = OauthProvider::getActive($provider);

        if ($config && $config->client_id && $config->client_secret) {
            // Sobreescribir config en runtime con valores de la BD
            config([
                "services.{$provider}.client_id"     => $config->client_id,
                "services.{$provider}.client_secret" => $config->client_secret,
                "services.{$provider}.redirect"      => $config->redirect_uri,
            ]);
        }
        // Si no hay config en BD, Socialite usa lo que ya está en config/services.php (.env)
    }
}
