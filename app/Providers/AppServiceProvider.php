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
        //
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
                // Obtener todos los carritos del usuario y combinar items
                $carritos = \App\Models\Carrito::with('itemsIntencionCompra')
                    ->where('id_user', auth()->id())
                    ->get();

                // Crear un objeto virtual con todos los items combinados
                $carrito = $carritos->first();
                if ($carrito) {
                    $todosLosItems = $carritos->flatMap(fn($c) => $c->itemsIntencionCompra);
                    $carrito->setRelation('itemsIntencionCompra', $todosLosItems);
                }
            }

            $view->with('carrito', $carrito);
        });
       
    }
}
