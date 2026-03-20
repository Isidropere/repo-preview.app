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
            //
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

        return parent::render($request, $exception);
    }


}
