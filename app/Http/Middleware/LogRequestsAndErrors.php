<?php

namespace App\Http\Middleware;

use App\Logging\ErrorLoggerTrait;
use Closure;
use Illuminate\Http\Request;
use Throwable;

class LogRequestsAndErrors
{
    use ErrorLoggerTrait;

    public function handle(Request $request, Closure $next)
    {
        // Log de la petición entrante
        if (config('logging.log_requests')) {
            $this->logRequest($request);
        }

        $response = $next($request);

        // Log de la respuesta
        if (config('logging.log_responses')) {
            $this->logResponse($request, $response);
        }

        return $response;
    }

    public function terminate($request, $response)
    {
        // Aquí puedes agregar lógica post-response si es necesario
    }

    protected function logRequest(Request $request): void
    {
        $data = [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'headers' => $request->headers->all(),
            'input' => $request->except(['password', 'password_confirmation']),
            'user_id' => $request->user()?->id,
        ];

        Log::channel('request_tracking')->info('Request received', $data);
    }

    protected function logResponse(Request $request, $response): void
    {
        $content = $response->getContent();

        try {
            $content = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            // Mantener el contenido como está si no es JSON
        }

        $data = [
            'status' => $response->status(),
            'content' => $content,
            'headers' => $response->headers->all(),
            'user_id' => $request->user()?->id,
        ];

        Log::channel('request_tracking')->info('Response sent', $data);
    }
}
