<?php

use Illuminate\Support\Facades\Route;
use App\API\DeliveryZonaController;

// ── Delivery ──
Route::prefix('delivery')->group(function () {
    Route::get('/zonas', [DeliveryZonaController::class, 'index']);
    Route::get('/calcular', [DeliveryZonaController::class, 'calcular']);
    Route::get('/config', [DeliveryZonaController::class, 'config']);
    Route::middleware(['auth:sanctum', 'superadmin'])->group(function () {
        Route::post('/config/{clave}', [DeliveryZonaController::class, 'updateConfig']);
    });
});
