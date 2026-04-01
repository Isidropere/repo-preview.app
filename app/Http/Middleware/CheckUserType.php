<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckUserType
{
    public function handle(Request $request, Closure $next, ...$types)
    {
        $user = $request->user();

        // Usar id_tipo_usuario (columna real en BD), no $user->tipo que no existe
        if (!$user || !in_array($user->id_tipo_usuario, $types)) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        return $next($request);
    }
}
