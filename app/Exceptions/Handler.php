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
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    use ErrorLoggerTrait;
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            \Illuminate\Support\Facades\Log::error('[Exception] ' . $e->getMessage(), [
                'file'  => $e->getFile(),
                'line'  => $e->getLine(),
                'url'   => request()->fullUrl(),
            ]);
        });
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $exception)
    {
        // 🔴 SESIÓN EXPIRADA → LOGIN
        if ($exception instanceof TokenMismatchException) {

            // Si es una petición AJAX / API
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'La sesión expiró, vuelva a iniciar sesión',
                    'redirect' => route('login')
                ], 419);
            }

            // Petición normal (web)
            return redirect()
                ->route('login')
                ->with('message', 'Tu sesión expiró. Por favor inicia sesión nuevamente.');
        }

        // 🔵 TU CÓDIGO EXISTENTE
        if ($request->wantsJson()) {
            $response = [
                'success' => false,
                'message' => $exception->getMessage()
            ];

            if ($exception instanceof HttpException) {
                $response['message'] = Response::$statusTexts[$exception->getStatusCode()];
                $response['data'] = $exception->getMessage();
                return response()->json($response, $exception->getStatusCode());
            }

            if ($exception instanceof ModelNotFoundException) {
                return response()->json($response, Response::HTTP_NOT_FOUND);
            }

            if ($exception instanceof ValidationException) {
                $response['errors'] = $exception->validator->errors()->getMessages();
                return response()->json($response, Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            if (config('app.debug')) {
                $response['trace'] = $exception->getTrace();
            }

            $this->logError($exception, [
                'handler' => 'global',
                'type' => 'render',
                'request' => $request->all()
            ]);

            return response()->json($response, Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        // 🔵 Para peticiones web: SIEMPRE guardar error en BD (excepto 404, validación, CSRF)
        if (!$request->wantsJson()) {
            $errorRef = null;
            $statusCode = $exception instanceof HttpException ? $exception->getStatusCode() : 500;
            $skipDb = $exception instanceof ValidationException
                   || $exception instanceof TokenMismatchException
                   || $exception instanceof ModelNotFoundException
                   || $statusCode === 404;

            if (!$skipDb) {
                try {
                    $errorRef = \Illuminate\Support\Str::uuid()->toString();
                    \DB::table('application_errors')->insert([
                        'error_reference' => $errorRef,
                        'message'         => $exception->getMessage(),
                        'stack_trace'     => $exception->getTraceAsString(),
                        'url'             => $request->fullUrl(),
                        'method'          => $request->method(),
                        'user_id'         => auth()->id(),
                        'ip_address'      => $request->ip(),
                        'user_agent'      => $request->userAgent(),
                        'input_data'      => json_encode($request->except(['password', 'password_confirmation'])),
                        'created_at'      => now(),
                    ]);
                } catch (\Throwable $dbErr) {
                    \Illuminate\Support\Facades\Log::error('No se pudo guardar error en BD: ' . $dbErr->getMessage());
                }
            }

            // En modo debug, dejar que Laravel muestre su página detallada
            if (config('app.debug')) {
                return parent::render($request, $exception);
            }

            // En producción, mostrar vista personalizada
            $statusCode = $exception instanceof HttpException ? $exception->getStatusCode() : 500;
            return response()->view('errors.custom', [
                'error_reference' => $errorRef,
                'status_code'     => $statusCode,
                'message'         => $statusCode === 404 ? 'Página no encontrada' : 'Algo salió mal',
            ], $statusCode);
        }

        return parent::render($request, $exception);
    }


}
