<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'admin'              => \App\Http\Middleware\AdminMiddleware::class,
            'superadmin'         => \App\Http\Middleware\SuperAdminMiddleware::class,
            'role'               => \App\Http\Middleware\CheckUserType::class,
            'throttle.sensitive' => \App\Http\Middleware\ThrottleSensitiveRoutes::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Log custom mismatch session errors
        $exceptions->render(function (\Illuminate\Session\TokenMismatchException $e, $request) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'La sesión expiró, vuelva a iniciar sesión',
                    'redirect' => route('login'),
                ], 419);
            }
            return redirect()->route('login')
                ->with('message', 'Tu sesión expiró. Por favor inicia sesión nuevamente.');
        });

        // Global reportable to log all other exceptions to database
        $exceptions->report(function (Throwable $e) {
            try {
                if (app()->bound(\App\Services\ErrorLogService::class)) {
                    $service = app(\App\Services\ErrorLogService::class);
                    $reference = $service->report($e);
                    
                    // Safely store reference in the request attributes if possible
                    if (app()->bound('request')) {
                        $request = app('request');
                        if (method_exists($request, 'attributes')) {
                            $request->attributes->set('error_reference', $reference);
                        }
                    }
                }
            } catch (Throwable $ignore) {
                // Never let the reporter crash the app
            }
        });

        // Custom renderer for all non-validation/404 exceptions
        $exceptions->render(function (Throwable $e, $request = null) {
            // Ensure $request is not null and is a valid request object
            if (!$request || !method_exists($request, 'expectsJson')) {
                // Fallback attempt to get it from container
                $request = app()->bound('request') ? app('request') : null;
            }

            // If we still don't have a valid request or it expects JSON, let Laravel handle it
            if (!$request || $request->expectsJson() || ($request->is && $request->is('api/*'))) {
                return;
            }

            // Exceptions we don't want to show the custom "Oops" page for
            if ($e instanceof \Illuminate\Validation\ValidationException || 
                $e instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException ||
                $e instanceof \Illuminate\Auth\AuthenticationException ||
                $e instanceof \Illuminate\Session\TokenMismatchException) {
                return;
            }

            // Retrieve reference from request attributes (set in report() above)
            $reference = method_exists($request, 'attributes') 
                ? $request->attributes->get('error_reference') 
                : null;

            // Fallback: report it now if it hasn't been reported
            if (!$reference && !($e instanceof \Symfony\Component\HttpKernel\Exception\HttpException && $e->getStatusCode() < 500)) {
                try {
                    if (app()->bound(\App\Services\ErrorLogService::class)) {
                        $service = app(\App\Services\ErrorLogService::class);
                        $reference = $service->report($e);
                    }
                } catch (Throwable $ignore) {}
            }

            return response()->view('errors.custom', [
                'error_reference' => $reference
            ], 500);
        });
    })->create();
