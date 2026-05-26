<?php

use Illuminate\Support\Facades\Route;
use App\API\DeliveryZonaController;
use App\Http\Controllers\API\AuthApiController;
use App\Http\Controllers\API\ItemApiController;
use App\Http\Controllers\API\CarritoApiController;
use App\Http\Controllers\API\DireccionApiController;
use App\Http\Controllers\API\NegociacionApiController;
use App\Http\Controllers\API\HojaVidaApiController;
use App\Http\Controllers\API\PagoApiController;
use App\Http\Controllers\TarjetaPagoController;


/*
|--------------------------------------------------------------------------
| API Routes — CambialóRD Mobile App
|--------------------------------------------------------------------------
|
| Base URL (emulador Android): http://10.0.2.2:8000/api
| Base URL (producción):       https://tudominio.com/api
|
| Auth: Laravel Sanctum (Bearer token)
|
|--------------------------------------------------------------------------
*/

// ── Auth ──────────────────────────────────────────────────────────────
Route::prefix('auth')->group(function () {
    Route::post('/login',    [AuthApiController::class, 'login']);
    Route::post('/register', [AuthApiController::class, 'register']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout',             [AuthApiController::class, 'logout']);
        Route::get('/me',                  [AuthApiController::class, 'me']);
        Route::post('/cambiar-contrasena', [AuthApiController::class, 'cambiarContrasena']);
        Route::post('/profile',            [AuthApiController::class, 'updateProfile']);
    });
});

// ── Productos (públicos) ───────────────────────────────────────────────
Route::post('/images', [\App\Http\Controllers\ImageController::class, 'store'])->middleware('auth:sanctum');

// ── Ubicación (públicos) ───────────────────────────────────────────────
Route::get('/ubicacion/provincias',              [DireccionApiController::class, 'provincias']);
Route::get('/ubicacion/municipios/{id_provincia}', [DireccionApiController::class, 'municipios']);

// ── Rutas protegidas ──────────────────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {

    // Carrito
    Route::prefix('carrito')->group(function () {
        Route::get('/',              [CarritoApiController::class, 'index']);
        Route::post('/agregar',      [CarritoApiController::class, 'agregar']);
        Route::delete('/vaciar',     [CarritoApiController::class, 'vaciar']);
        Route::delete('/{id_item}',  [CarritoApiController::class, 'eliminar']);
    });

    // Mis artículos (CRUD)
    Route::get('/mis-items',    [ItemApiController::class, 'userItems']);
    Route::post('/items',       [ItemApiController::class, 'store']);
    Route::delete('/items/{id}', [ItemApiController::class, 'destroy'])->where('id', '[0-9]+');

    // Negociaciones (intercambios)
    Route::prefix('negociaciones')->group(function () {
        Route::get('/',                     [NegociacionApiController::class, 'index']);
        Route::post('/',                    [NegociacionApiController::class, 'store']);
        Route::get('/{id}',                 [NegociacionApiController::class, 'show']);
        Route::post('/{id}/aceptar',        [NegociacionApiController::class, 'aceptar']);
        Route::post('/{id}/rechazar',       [NegociacionApiController::class, 'rechazar']);
        Route::post('/{id}/contraoferta',   [NegociacionApiController::class, 'contraoferta']);
        Route::post('/{id}/cancelar',       [NegociacionApiController::class, 'cancelar']);
        Route::post('/{id}/confirmar-emisor', [NegociacionApiController::class, 'confirmarEmisor']);
        Route::post('/{id}/confirmar-receptor', [NegociacionApiController::class, 'confirmarReceptor']);
        Route::post('/{id}/aceptar-como-emisor', [NegociacionApiController::class, 'aceptarComoEmisor']);
        Route::post('/{id}/modo-entrega',   [NegociacionApiController::class, 'seleccionarModoEntrega']);
        Route::post('/{id}/confirmar-entrega', [NegociacionApiController::class, 'confirmarEntrega']);
        Route::get('/{id}/mensajes',        [NegociacionApiController::class, 'mensajes']);
        Route::post('/{id}/mensajes',       [NegociacionApiController::class, 'enviarMensaje']);
        Route::post('/{id}/pago',           [\App\Http\Controllers\NegociacionController::class, 'procesarPago']);
    });


    // Direcciones
    Route::prefix('direcciones')->group(function () {
        Route::get('/',       [DireccionApiController::class, 'index']);
        Route::post('/',      [DireccionApiController::class, 'store']);
        Route::put('/{id}',   [DireccionApiController::class, 'update']);
        Route::delete('/{id}', [DireccionApiController::class, 'destroy']);
    });

    // Hoja de Vida
    Route::get('/hoja-vida',  [HojaVidaApiController::class, 'show']);
    Route::post('/hoja-vida', [HojaVidaApiController::class, 'store']);

    // Historial
    Route::get('/historial', function () {
        $userId = auth()->id();

        $compras = \App\Models\PagoCompra::whereHas('carrito', fn($q) => $q->where('id_user', $userId))
            ->with(['pagoItems', 'trazabilidad'])
            ->orderByDesc('fecha')
            ->get();

        $ventas = \App\Models\ItemIntencionCompra::whereHas('item', fn($q) => $q->where('id_user', $userId))
            ->with(['item'])
            ->orderByDesc('id_item_intencion_compra')
            ->get();

        $intercambios = \App\Models\Negociacion::where('usuario_emisor_id', $userId)
            ->orWhere('usuario_receptor_id', $userId)
            ->orderByDesc('id_negociacion')
            ->get();

        return response()->json([
            'compras'      => $compras,
            'ventas'       => $ventas,
            'intercambios' => $intercambios,
        ]);
    });

    // Checkout y pagos
    Route::post('/pago/checkout', [PagoApiController::class, 'checkout']);

    // Tarjetas de pago
    Route::prefix('tarjetas')->group(function () {
        Route::get('/',            [TarjetaPagoController::class, 'index']);
        Route::post('/',           [TarjetaPagoController::class, 'store']);
        Route::post('/usar',       [TarjetaPagoController::class, 'usarEstaTarjeta']);
        Route::delete('/{id}',     [TarjetaPagoController::class, 'destroy']);
    });
});


// ── Delivery (público + config protegida) ─────────────────────────────
Route::prefix('delivery')->group(function () {
    Route::get('/zonas',    [DeliveryZonaController::class, 'index']);
    Route::get('/calcular', [DeliveryZonaController::class, 'calcular']);
    Route::get('/config',   [DeliveryZonaController::class, 'config']);

    Route::middleware(['auth:sanctum', 'superadmin'])->group(function () {
        Route::post('/config/{clave}', [DeliveryZonaController::class, 'updateConfig']);
    });
});


