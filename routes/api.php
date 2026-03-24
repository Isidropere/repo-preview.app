<?php

use Illuminate\Support\Facades\Route;
use App\API\DeliveryZonaController;
use App\Http\Controllers\API\AuthApiController;
use App\Http\Controllers\API\ItemApiController;
use App\Http\Controllers\API\CarritoApiController;

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
| Endpoints públicos:
|   POST /api/auth/login
|   POST /api/auth/register
|   GET  /api/items
|   GET  /api/items/{id}
|   GET  /api/items/buscar?q=
|   GET  /api/categorias
|
| Endpoints protegidos (requieren Bearer token):
|   POST /api/auth/logout
|   GET  /api/auth/me
|   GET  /api/carrito
|   POST /api/carrito/agregar
|   DELETE /api/carrito/{id_item}
|
|--------------------------------------------------------------------------
*/

// ── Auth ──────────────────────────────────────────────────────────────
Route::prefix('auth')->group(function () {
    Route::post('/login',    [AuthApiController::class, 'login']);
    Route::post('/register', [AuthApiController::class, 'register']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthApiController::class, 'logout']);
        Route::get('/me',      [AuthApiController::class, 'me']);
    });
});

// ── Productos (públicos) ───────────────────────────────────────────────
Route::get('/items',         [ItemApiController::class, 'index']);
Route::get('/items/buscar',  [ItemApiController::class, 'buscar']);
Route::get('/items/{id}',    [ItemApiController::class, 'show'])->where('id', '[0-9]+');
Route::get('/categorias',    [ItemApiController::class, 'categorias']);

// ── Carrito (protegido) ────────────────────────────────────────────────
Route::middleware('auth:sanctum')->prefix('carrito')->group(function () {
    Route::get('/',           [CarritoApiController::class, 'index']);
    Route::post('/agregar',   [CarritoApiController::class, 'agregar']);
    Route::delete('/{id_item}', [CarritoApiController::class, 'eliminar']);
});

// ── Delivery ──────────────────────────────────────────────────────────
Route::prefix('delivery')->group(function () {
    Route::get('/zonas',    [DeliveryZonaController::class, 'index']);
    Route::get('/calcular', [DeliveryZonaController::class, 'calcular']);
    Route::get('/config',   [DeliveryZonaController::class, 'config']);

    Route::middleware(['auth:sanctum', 'superadmin'])->group(function () {
        Route::post('/config/{clave}', [DeliveryZonaController::class, 'updateConfig']);
    });
});
