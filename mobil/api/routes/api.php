<?php

/**
 * ============================================================
 * Rutas API — Backend móvil CambialóRD
 * ============================================================
 * Este archivo define todos los endpoints REST que consume
 * la app React Native. Se divide en dos grupos:
 *
 *   1. Rutas públicas  → accesibles sin token
 *   2. Rutas protegidas → requieren Bearer token (Sanctum)
 *
 * El prefijo /api es añadido automáticamente por Laravel
 * al registrar este archivo en bootstrap/app.php.
 * ============================================================
 */

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ItemController;
use App\Http\Controllers\Api\CarritoController;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\NegociacionController;

// ──────────────────────────────────────────────────────────
// RUTAS PÚBLICAS — No requieren autenticación
// ──────────────────────────────────────────────────────────

// Autenticación
Route::post('/login', [AuthController::class, 'login']);       // Iniciar sesión → devuelve token
Route::post('/register', [AuthController::class, 'register']); // Registrar nuevo usuario → devuelve token

// Catálogo de productos (visible sin cuenta)
Route::get('/items', [ItemController::class, 'index']);        // Listar productos con filtros y paginación
Route::get('/items/{id}', [ItemController::class, 'show']);    // Detalle de un producto + imágenes
Route::get('/categorias', [ItemController::class, 'categorias']); // Listar categorías activas

// ──────────────────────────────────────────────────────────
// RUTAS PROTEGIDAS — Requieren header: Authorization: Bearer {token}
// ──────────────────────────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {

    // ── Sesión ──
    Route::post('/logout', [AuthController::class, 'logout']); // Revocar token actual
    Route::get('/user', [AuthController::class, 'user']);      // Datos del usuario autenticado

    // ── Perfil ──
    Route::get('/profile', [UserController::class, 'profile']);                // Ver perfil + direcciones
    Route::put('/profile', [UserController::class, 'updateProfile']);          // Actualizar datos personales
    Route::put('/profile/password', [UserController::class, 'changePassword']); // Cambiar contraseña
    Route::get('/mis-productos', [UserController::class, 'misProductos']);     // Productos publicados por el usuario

    // ── Carrito ──
    Route::get('/carrito', [CarritoController::class, 'index']);                    // Ver carrito con totales
    Route::post('/carrito', [CarritoController::class, 'store']);                   // Agregar item al carrito
    Route::delete('/carrito/vaciar', [CarritoController::class, 'vaciar']);         // Vaciar todo el carrito
    Route::delete('/carrito/{itemId}', [CarritoController::class, 'destroy']);      // Eliminar un item específico
    Route::put('/carrito/{id}/cantidad', [CarritoController::class, 'updateCantidad']); // Cambiar cantidad
    Route::put('/carrito/{id}/seleccion', [CarritoController::class, 'toggleSeleccion']); // Marcar/desmarcar para pago

    // ── Mensajes ──
    Route::get('/messages', [MessageController::class, 'conversations']);           // Lista de conversaciones
    Route::get('/messages/unread/count', [MessageController::class, 'unreadCount']); // Cantidad de mensajes no leídos
    Route::get('/messages/{userId}', [MessageController::class, 'messages']);       // Mensajes con un usuario específico
    Route::post('/messages', [MessageController::class, 'send']);                   // Enviar mensaje

    // ── Negociaciones ──
    Route::get('/negociaciones', [NegociacionController::class, 'index']);                      // Ver mis negociaciones
    Route::post('/negociaciones', [NegociacionController::class, 'store']);                     // Crear nueva negociación
    Route::post('/negociaciones/{id}/aceptar', [NegociacionController::class, 'aceptar']);     // Aceptar oferta
    Route::post('/negociaciones/{id}/rechazar', [NegociacionController::class, 'rechazar']);   // Rechazar oferta
    Route::post('/negociaciones/{id}/contraoferta', [NegociacionController::class, 'contraoferta']); // Enviar contraoferta
});
