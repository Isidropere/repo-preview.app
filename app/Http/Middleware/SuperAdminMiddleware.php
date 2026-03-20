<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Middleware: SuperAdminMiddleware
 *
 * Protege rutas exclusivas del Super Admin (estadísticas, mensajes predefinidos).
 * Verifica que el usuario tenga isSuperAdmin = true.
 * Registrado como 'superadmin' en bootstrap/app.php.
 */
class SuperAdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        if (!$user->isSuperAdmin) {
            Log::warning('Acceso denegado a ruta superadmin', [
                'user_id' => $user->id,
                'email'   => $user->email,
                'ip'      => $request->ip(),
                'url'     => $request->fullUrl(),
            ]);

            abort(403, 'Acceso restringido a superadministradores.');
        }

        if (isset($user->estatus) && $user->estatus == 0) {
            auth()->logout();
            return redirect()->route('login')->with('error', 'Tu cuenta ha sido suspendida.');
        }

        return $next($request);
    }
}
