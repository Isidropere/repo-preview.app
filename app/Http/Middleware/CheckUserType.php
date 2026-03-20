<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckUserType
{
    public function handle(Request $request, Closure $next, ...$types)
    {
        $user = $request->user();

        if (!$user || !in_array($user->tipo, $types)) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        return $next($request);
    }
}
