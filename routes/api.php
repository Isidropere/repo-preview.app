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
    Route::post('/google',   [AuthApiController::class, 'loginGoogle']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout',             [AuthApiController::class, 'logout']);
        Route::get('/me',                  [AuthApiController::class, 'me']);
        Route::get('/badges',              [AuthApiController::class, 'getBadges']);
        Route::post('/cambiar-contrasena', [AuthApiController::class, 'cambiarContrasena']);
        Route::post('/profile',            [AuthApiController::class, 'updateProfile']);
    });
});

// ── Productos (públicos) ───────────────────────────────────────────────
Route::get('/items',          [ItemApiController::class, 'index']);
Route::get('/items/buscar',   [ItemApiController::class, 'buscar']);
Route::get('/items/{id}',     [ItemApiController::class, 'show'])->where('id', '[0-9]+');
Route::get('/categorias',     [ItemApiController::class, 'categorias']);
Route::get('/colors',         [ItemApiController::class, 'colors']);
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
        Route::put('/{itemIntencionId}/cantidad', [CarritoApiController::class, 'actualizarCantidad']);
        Route::put('/{itemIntencionId}/seleccion', [CarritoApiController::class, 'marcarSeleccionado']);
    });

    // Mis artículos (CRUD)
    Route::get('/mis-items',    [ItemApiController::class, 'userItems']);
    Route::get('/mis-items/{id}', [ItemApiController::class, 'userItemDetail'])->where('id', '[0-9]+');
    Route::post('/items/{id}/update', [ItemApiController::class, 'update'])->where('id', '[0-9]+');
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
        Route::post('/{id}/aceptar-contraoferta', [NegociacionApiController::class, 'aceptarComoEmisor']);
        Route::post('/{id}/modo-entrega',   [NegociacionApiController::class, 'seleccionarModoEntrega']);
        Route::post('/{id}/confirmar-entrega', [NegociacionApiController::class, 'confirmarEntrega']);
        Route::post('/{id}/completar',      [NegociacionApiController::class, 'completar']);
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

    // Talentos
    Route::get('/talentos/config', function () {
        return response()->json([
            'monto_registro' => (float) \App\Models\ConfigTarifaCategoria29::vigente()->monto_registro,
        ]);
    });
    Route::post('/talentos', [ItemApiController::class, 'storeTalento']);

    // Notificaciones
    Route::prefix('notificaciones')->group(function () {
        Route::get('/',             [\App\Http\Controllers\NotificationController::class, 'listar']);
        Route::get('/todas',        [\App\Http\Controllers\NotificationController::class, 'listarTodasApi']);
        Route::post('/leer-todas',  [\App\Http\Controllers\NotificationController::class, 'marcarTodasLeidasApi']);
        Route::post('/{id}/leido',  [\App\Http\Controllers\NotificationController::class, 'marcarLeido']);
    });

    // Historial

    // Rating de intercambios
    Route::post('/rating', function (\Illuminate\Http\Request $request) {
        $request->validate(['id_miembro' => 'required|integer|exists:users,id', 'rating' => 'required|integer|min:1|max:5']);
        \App\Models\Rating::create(['id_usuario' => auth()->id(), 'id_miembro' => $request->id_miembro, 'rating' => $request->rating]);
        return response()->json(['success' => true, 'message' => '¡Gracias por tu calificación!']);
    });
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

        $motivos = \App\Models\MotivoDevolucion::where('activo', true)->get();

        return response()->json([
            'compras'      => $compras,
            'ventas'       => $ventas,
            'intercambios' => $intercambios,
            'motivos'      => $motivos,
        ]);
    });
    Route::post('/historial/devolucion/{id}', [\App\Http\Controllers\HistorialController::class, 'procesarDevolucionApi']);

    // Checkout y pagos
    Route::post('/pago/checkout', [PagoApiController::class, 'checkout']);

    // Solicitudes de servicio (aprobación previa al pago de talentos)
    Route::post('/solicitudes-servicio/enviar', [\App\Http\Controllers\SolicitudServicioController::class, 'enviarDesdeCarrito']);
    Route::get('/solicitudes-servicio/estado/{idItem}', [\App\Http\Controllers\SolicitudServicioController::class, 'estadoItem']);

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


