<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use App\Logging\ErrorLoggerTrait;
use Illuminate\Session\TokenMismatchException;

class Handler extends ExceptionHandler
{
    use ErrorLoggerTrait;

    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            \Illuminate\Support\Facades\Log::error('[Exception] ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'url'  => request()->fullUrl(),
            ]);
        });
    }

    public function render($request, Throwable $exception)
    {
        // ══════════════════════════════════════════════════════
        // GUARDAR TODOS LOS ERRORES EN BD (sin excepciones)
        // ══════════════════════════════════════════════════════
        $errorRef = $this->guardarErrorEnBD($request, $exception);

        // ── SESIÓN EXPIRADA → LOGIN ──
        if ($exception instanceof TokenMismatchException) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'La sesión expiró, vuelva a iniciar sesión',
                    'redirect' => route('login'),
                    'error_reference' => $errorRef,
                ], 419);
            }
            return redirect()->route('login')
                ->with('message', 'Tu sesión expiró. Por favor inicia sesión nuevamente.');
        }

        // ── RESPUESTAS JSON ──
        if ($request->wantsJson()) {
            $response = [
                'success' => false,
                'message' => $exception->getMessage(),
                'error_reference' => $errorRef,
            ];

            if ($exception instanceof HttpException) {
                $response['message'] = Response::$statusTexts[$exception->getStatusCode()] ?? $exception->getMessage();
                return response()->json($response, $exception->getStatusCode());
            }
            if ($exception instanceof ModelNotFoundException) {
                $response['message'] = 'Recurso no encontrado.';
                return response()->json($response, 404);
            }
            if ($exception instanceof ValidationException) {
                $response['errors'] = $exception->validator->errors()->getMessages();
                return response()->json($response, 422);
            }
            if (config('app.debug')) {
                $response['trace'] = $exception->getTrace();
            }
            return response()->json($response, 500);
        }

        // ── RESPUESTAS WEB ──
        if (config('app.debug')) {
            return parent::render($request, $exception);
        }

        $statusCode = $exception instanceof HttpException ? $exception->getStatusCode() : 500;
        return response()->view('errors.custom', [
            'error_reference' => $errorRef,
            'status_code'     => $statusCode,
            'message'         => $statusCode === 404 ? 'Página no encontrada' : 'Algo salió mal',
        ], $statusCode);
    }

    /**
     * Guarda CUALQUIER error en la tabla application_errors.
     * Retorna el error_reference (UUID) o null si falla.
     */
    private function guardarErrorEnBD($request, Throwable $exception): ?string
    {
        try {
            $errorRef = \Illuminate\Support\Str::uuid()->toString();
            $statusCode = $exception instanceof HttpException ? $exception->getStatusCode() : 500;

            \DB::table('application_errors')->insert([
                'error_reference' => $errorRef,
                'message'         => substr($exception->getMessage(), 0, 1000),
                'stack_trace'     => substr($exception->getTraceAsString(), 0, 5000),
                'url'             => substr($request->fullUrl(), 0, 500),
                'method'          => $request->method(),
                'user_id'         => auth()->id(),
                'ip_address'      => $request->ip(),
                'user_agent'      => substr($request->userAgent() ?? '', 0, 500),
                'input_data'      => substr(json_encode($request->except(['password', 'password_confirmation', 'cvv', 'no_tarjeta'])), 0, 2000),
                'created_at'      => now(),
            ]);

            return $errorRef;
        } catch (\Throwable $e) {
            // Si no se puede guardar en BD, al menos loguear
            \Illuminate\Support\Facades\Log::error('Handler: no se pudo guardar error en BD: ' . $e->getMessage());
            return null;
        }
    }
}
