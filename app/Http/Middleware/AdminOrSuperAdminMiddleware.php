<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Middleware: AdminOrSuperAdminMiddleware
 *
 * Protege rutas que solo pueden ser accedidas por Administradores generales o Super Admins.
 * Los usuarios que solo tengan el rol de Contable (y no sean admin/superadmin) serán rechazados con un 403.
 */
class AdminOrSuperAdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        if (!$user->isAdmin && !$user->isSuperAdmin) {
            Log::warning('Acceso denegado a ruta admin_or_superadmin', [
                'user_id' => $user->id,
                'email'   => $user->email,
                'ip'      => $request->ip(),
                'url'     => $request->fullUrl(),
            ]);

            abort(403, 'Acceso restringido a administradores.');
        }

        if (isset($user->estatus) && $user->estatus == 0) {
            auth()->logout();
            return redirect()->route('login')->with('error', 'Tu cuenta ha sido suspendida.');
        }

        return $next($request);
    }
}
