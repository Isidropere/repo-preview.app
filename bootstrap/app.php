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
    })->create();
