<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Cache\RateLimiter;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Rate limiter para rutas sensibles (pagos, negociaciones).
 * Limita a 10 peticiones por minuto por usuario/IP.
 * Bloquea temporalmente y loguea intentos excesivos.
 */
class ThrottleSensitiveRoutes
{
    protected RateLimiter $limiter;

    public function __construct(RateLimiter $limiter)
    {
        $this->limiter = $limiter;
    }

    public function handle(Request $request, Closure $next, int $maxAttempts = 10, int $decayMinutes = 1)
    {
        // Clave única por usuario autenticado o por IP si no está autenticado
        $key = 'sensitive:' . ($request->user()?->id ?? $request->ip());

        if ($this->limiter->tooManyAttempts($key, $maxAttempts)) {
            Log::warning('Rate limit excedido en ruta sensible', [
                'user_id' => $request->user()?->id,
                'ip'      => $request->ip(),
                'url'     => $request->fullUrl(),
            ]);

            $seconds = $this->limiter->availableIn($key);

            return response()->json([
                'message' => "Demasiadas solicitudes. Intenta de nuevo en {$seconds} segundos.",
            ], Response::HTTP_TOO_MANY_REQUESTS);
        }

        $this->limiter->hit($key, $decayMinutes * 60);

        return $next($request);
    }
}
