<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;



class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(\App\Services\Interfaces\ImageProcessorInterface::class, \App\Services\Implementations\ImageProcessor::class);

        // Configurar public_path para MochaHost (public_html) si existe
        if (file_exists($this->app->basePath('public_html'))) {
            $this->app->usePublicPath($this->app->basePath('public_html'));
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Illuminate\Support\Facades\App::setLocale('es');

        // Registrar el view composer para la paginación
        \Illuminate\Support\Facades\View::composer('vendor.pagination.custom', function ($view) {
            $view->with('paginator', $view->paginator);
        });

        // Registrar el view composer para el header
        \Illuminate\Support\Facades\View::composer('partials.header', function ($view) {
            $carrito = null;

            if (auth()->check()) {
                $userId = auth()->id();
                // Cachear el carrito por 5 minutos para evitar queries en cada clic
                $carrito = \Illuminate\Support\Facades\Cache::remember("user_cart_{$userId}", 300, function () use ($userId) {
                    // Obtener todos los carritos del usuario y combinar items
                    $carritos = \App\Models\Carrito::with('itemsIntencionCompra')
                        ->where('id_user', $userId)
                        ->get();

                    // Crear un objeto virtual con todos los items combinados
                    $carritoVirtual = $carritos->first();
                    if ($carritoVirtual) {
                        $todosLosItems = $carritos->flatMap(fn($c) => $c->itemsIntencionCompra);
                        $carritoVirtual->setRelation('itemsIntencionCompra', $todosLosItems);
                    }
                    return $carritoVirtual;
                });
            }

            $view->with('carrito', $carrito);
        });
       
    }
}
