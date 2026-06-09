<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Middleware: SuperAdminOrContableMiddleware
 *
 * Protege las rutas de la gestión empresarial (ERP).
 * Verifica que el usuario tenga isSuperAdmin = true o isContable = true.
 */
class SuperAdminOrContableMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        if (!$user->isSuperAdmin && !$user->isContable) {
            Log::warning('Acceso denegado a ruta ERP (superadmin_or_contable)', [
                'user_id' => $user->id,
                'email'   => $user->email,
                'ip'      => $request->ip(),
                'url'     => $request->fullUrl(),
            ]);

            abort(403, 'Acceso restringido a contadores y superadministradores.');
        }

        if (isset($user->estatus) && $user->estatus == 0) {
            auth()->logout();
            return redirect()->route('login')->with('error', 'Tu cuenta ha sido suspendida.');
        }

        return $next($request);
    }
}
