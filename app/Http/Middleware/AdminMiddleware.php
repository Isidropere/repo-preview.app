<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Middleware: AdminMiddleware
 *
 * Protege las rutas del panel de administración (/admin/*).
 * Verifica que el usuario esté autenticado y tenga isAdmin = true.
 * Registrado como 'admin' en bootstrap/app.php.
 */
class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // 1. Verificar que el usuario esté autenticado
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        // 2. Verificar que el usuario tenga el flag isAdmin o isSuperAdmin activo
        if (!$user->isAdmin && !$user->isSuperAdmin) {
            // Registrar intento de acceso no autorizado para auditoría
            Log::warning('Intento de acceso no autorizado al panel admin', [
                'user_id' => $user->id,
                'email'   => $user->email,
                'ip'      => $request->ip(),
                'url'     => $request->fullUrl(),
            ]);

            abort(403, 'Acceso no autorizado');
        }

        // 3. Verificar que la cuenta esté activa (no suspendida)
        if (isset($user->estatus) && $user->estatus == 0) {
            auth()->logout();
            return redirect()->route('login')
                ->with('error', 'Tu cuenta ha sido suspendida.');
        }

        return $next($request);
    }
}
