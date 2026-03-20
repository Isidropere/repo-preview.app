<?php

use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminComprasController;
use App\Http\Controllers\Admin\AdminStatsController;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DireccionesController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\CarritoController;
use App\Http\Controllers\PagoController;
use App\Http\Controllers\ProvinciaController;
use App\Http\Controllers\MunicipioController;
use App\Http\Controllers\DistritoMunicipalController;
use App\Http\Controllers\CategoriaItemController;
use App\Http\Controllers\TarjetaPagoController;
use App\Http\Controllers\PaqueteController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\MessageController;
use App\Models\CategoriaItem;
use App\Http\Controllers\NegociacionController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\Auth\VerificationController;

/*
|--------------------------------------------------------------------------
| Rutas Públicas (Sin autenticación)
|--------------------------------------------------------------------------
|
| Estructura general de rutas de CambialóRD:
|
| PÚBLICAS (sin auth):
|   /home                    → Página principal con productos destacados
|   /intercambio             → Listado de artículos para intercambio
|   /compras                 → Listado de artículos en venta
|   /items/*                 → Búsqueda, categorías, detalle de productos
|   /buscar                  → Búsqueda desde el header
|   /provincias, /municipios → APIs de ubicación
|   Páginas estáticas: sobre-nosotros, contactanos, envios, etc.
|
| AUTENTICADAS (auth):
|   /carrito/*               → Carrito de compras, checkout, pago
|   /negociaciones/*         → Intercambios entre usuarios
|   /mi-perfil               → Perfil del usuario
|   /direcciones             → Gestión de direcciones
|   /messages                → Mensajería entre usuarios
|   /notificaciones          → Sistema de notificaciones
|   /usuarios                → Admin: gestión de usuarios
|
| VERIFICADAS (auth + verified):
|   /mis-productos/*         → Publicar y gestionar artículos
|   /items/{item}/edit       → Editar artículos
|   /cambiar-tipo-usuario    → Cambiar rol de usuario
|
| ADMIN (auth + admin):
|   /admin/*                 → Panel de administración
|
| SUPER ADMIN (auth + superadmin):
|   /admin/estadisticas      → Dashboard de estadísticas
|   /admin/mensajes-*        → CRUD de mensajes predefinidos
|
|--------------------------------------------------------------------------
*/


// Ruta principal
Route::get('/', fn() => redirect()->route('home'));
Route::get('/home', function () {
    $productosIntercambio = \Illuminate\Support\Facades\Cache::remember('home_intercambio', 600, function () {
        return \App\Models\Item::with([
                'imagenes:id_imagen,id_item,nombre,ruta',
                'direccionPredeterminada.municipio:id_municipio,municipio',
            ])
            ->select('id_item', 'item', 'condicion', 'tipo_trans', 'estatus', 'id_user', 'fecha')
            ->whereIn('tipo_trans', [2, 3])
            ->where('estatus', 1)
            ->latest('fecha')
            ->limit(8)
            ->get();
    });

    $productosVenta = \Illuminate\Support\Facades\Cache::remember('home_venta', 600, function () {
        return \App\Models\Item::with([
                'imagenes:id_imagen,id_item,nombre,ruta',
                'direccionPredeterminada.municipio:id_municipio,municipio',
            ])
            ->select('id_item', 'item', 'valor', 'condicion', 'tipo_trans', 'estatus', 'id_user', 'fecha')
            ->where('tipo_trans', 1)
            ->where('estatus', 1)
            ->latest('fecha')
            ->limit(8)
            ->get();
    });

    return view('home', compact('productosIntercambio', 'productosVenta'));
})->name('home');

Route::get('/notificaciones/contador', function () {
    $userId = auth()->id();
    $total = Redis::get("notificaciones:$userId") ?? 0;
    return response()->json(['total' => $total]);
})->middleware('auth');


// Rutas de páginas estáticas
Route::get('/sobre-nosotros', function () {
    return view('sobre-nosotros.about');
})->name('about');

Route::get('/contactanos', function () {
    return view('contactanos.contact');
})->name('cont');

Route::get('/envios', function () {
    return view('envios.envios');
})->name('envios');

Route::get('/empleos', function () {
    return view('empleos.empleos');
})->name('empleos');

Route::get('/responsabilidad', function () {
    return view('responsabilidad.responsabilidad');
})->name('responsabilidad');

Route::get('/realizar-intercambio', function () {
    return view('realizar-intercambio.realizar-intercambio');
})->name('realizar-intercambio');

Route::get('/como-vender', function () {
    return view('como-vender.como-vender');
})->name('como-vender');

Route::get('/realizar-compra', function () {
    return view('realizar-compra.realizar-compra');
})->name('realizar-compra');




Route::get('/registrarse', function () {
    return view('registrarse.registrarse');
})->name('registrarse');

Route::get('/ayuda', function () {
    return view('ayuda.ayuda');
})->name('ayuda');

Route::get('/contraseña', function () {
    return view('contraseña.contrasenna');
})->name('contraseña');

Route::get('/historial', function () {
    $userId = auth()->id();

    // Compras: pagos realizados por el usuario
    $compras = \App\Models\PagoCompra::whereHas('carrito', fn($q) => $q->where('id_user', $userId))
        ->with(['trazabilidad', 'tarjeta', 'pagoItems.item.imagenes', 'carrito.itemsIntencionCompra'])
        ->orderByDesc('fecha')
        ->get();

    // Ventas: items de otros usuarios que fueron comprados (intenciones de compra sobre mis items)
    $ventas = \App\Models\ItemIntencionCompra::whereHas('item', fn($q) => $q->where('id_user', $userId))
        ->with(['item.imagenes', 'carrito.pagosCompra'])
        ->orderByDesc('id_item_intencion_compra')
        ->get();

    // Negociaciones donde el usuario participa (como emisor o receptor)
    $negociaciones = \App\Models\Negociacion::where('usuario_emisor_id', $userId)
        ->orWhere('usuario_receptor_id', $userId)
        ->with(['item.imagenes', 'usuario', 'usuarioReceptor'])
        ->orderByDesc('id_negociacion')
        ->get();

    return view('historial.historial', compact('compras', 'ventas', 'negociaciones'));
})->middleware('auth')->name('historial');

Route::get('/items/search_header', [ItemController::class, 'search_header'])->middleware('throttle:30,1')->name('items.search_header');
Route::get('/buscar', [ItemController::class, 'search_header'])->middleware('throttle:30,1');

// Rutas de categorías
Route::get('categorias-item', [CategoriaItemController::class, 'index']);
Route::get('categorias-item/{id}', [CategoriaItemController::class, 'show']);

// Rutas de ubicación
Route::get('/provincias', [ProvinciaController::class, 'getProvincias']);
Route::get('/municipios', [MunicipioController::class, 'getMunicipio']);
Route::get('distritos-municipales', [DistritoMunicipalController::class, 'index']);

// Rutas de tipos de productos
Route::get('/intercambio', [ItemController::class, 'showItemsTipo2y3'])->name('intercambio');
Route::get('/compras', [ItemController::class, 'showItemsTipo1'])->name('compra');







Route::prefix('items')->group(function () {
    Route::get('/', [ItemController::class, 'index'])->name('items.index');
    Route::get('/categoria29', [ItemController::class, 'soloCategoria29'])->name('items.soloCategoria29');
    Route::get('/search', [ItemController::class, 'search'])->middleware('throttle:30,1')->name('items.search');
    Route::get('/info/{id}', [ItemController::class, 'info'])->name('items.info');
    Route::get('/categoria/{id}', [ItemController::class, 'porCategoria'])->name('categorias.show');
    Route::get('/{id}', [ItemController::class, 'show'])->name('items.show');
    Route::get('/producto/{slug}', [ItemController::class, 'showDetail'])
        ->name('producto.detalle');
});


Route::middleware(['auth'])->prefix('carrito')->name('carrito.')->group(function () {
    Route::get('/carrito', [CarritoController::class, 'show'])->name('show');
    Route::post('/agregar', [CarritoController::class, 'agregarItem'])->name('agregar');
    Route::delete('/item/{id_item}', [CarritoController::class, 'eliminarItem'])->name('eliminarItem');
    Route::delete('/vaciar', [CarritoController::class, 'vaciar'])->name('vaciar');
    Route::get('/checkout', [CarritoController::class, 'checkout'])->name('checkout_index');

    // Ruta de pago: throttle estricto (5 intentos por minuto por usuario)
    Route::post('/pago', [PagoController::class, 'procesar'])
        ->middleware('throttle.sensitive:5,1')
        ->name('pago_procesar');

    Route::patch('/carrito/{item}', [CarritoController::class, 'update'])->name('carrito_update');
    Route::post('/marcar-seleccionados', [CarritoController::class, 'marcarSeleccionados'])
        ->name('marcarSeleccionados');

    Route::post('/tarjetas', [TarjetaPagoController::class, 'store'])->name('tarjetas_store');
    Route::post('/tarjetas/usar', [TarjetaPagoController::class, 'usarEstaTarjeta'])
        ->name('tarjetas_usar');
    Route::delete('/tarjetas/{id}', [TarjetaPagoController::class, 'destroy'])
        ->name('tarjetas_destroy');

    Route::put('/actualizarPaquete/{id}', [PaqueteController::class, 'actualizarPaquete'])->name('actualizar_Paquete');
    Route::delete('/eliminarPaquete/{id}', [PaqueteController::class, 'eliminarPaquete'])
        ->name('carrito_eliminarPaquete');
    Route::get('/listarPaquetes', [PaqueteController::class, 'listarPaquetes'])->name('paquetes_listar');
    Route::get('/obtenerPaquete/{id}', [PaqueteController::class, 'obtenerPaquete'])->name('paquetes_obtener');
    Route::post('/crearPaquete', [PaqueteController::class, 'crearPaquete'])->name('paquetes_crear');
    Route::put('/editarPaquete/{id}', [PaqueteController::class, 'editarPaquete'])->name('paquetes_editar');

    Route::get('/items-usuario', [ItemController::class, 'getItemsUsuario'])->name('items_usuario');

    Route::get('/getnegociaciones/{itemId}', [NegociacionController::class, 'getNegociaciones'])->name('get_Negociaciones');

    // Guardar negociación: throttle para evitar spam
    Route::post('/savenegociaciones', [NegociacionController::class, 'store'])
        ->middleware('throttle.sensitive:10,1')
        ->name('save_negociaciones');

    Route::get(
        '/negociaciones/mensajes/{id_emisor}/{id_receptor}',
        [NegociacionController::class, 'obtenerMensajes']
    );
    Route::get('/negociaciones/ver/{id_emisor}/{id_receptor}', [NegociacionController::class, 'obtenerMensajes'])
        ->name('negociaciones.ver');
});

Route::middleware(['auth'])->prefix('negociaciones')->group(function () {
    Route::get('/{item}', [NegociacionController::class, 'index'])->name('negociaciones.index');
    Route::post('/{id}/aceptar', [NegociacionController::class, 'aceptar'])->name('negociaciones.aceptar');
    Route::post('/{id}/rechazar', [NegociacionController::class, 'rechazar'])->name('negociaciones.rechazar');
    Route::get('/{id}/contraoferta', [NegociacionController::class, 'contraoferta'])->name('negociaciones.contraoferta');
    Route::post('/{id}/contraoferta', [NegociacionController::class, 'storeContraoferta'])->name('negociaciones.store_contraoferta');

    Route::post('/enviar', [App\Http\Controllers\NegociacionController::class, 'store'])
        ->middleware('throttle.sensitive:10,1')
        ->name('negociaciones.store');

    Route::get('/ver-chat/{id}', [NegociacionController::class, 'verChat'])->name('negociaciones.verChat');
});


///*
//|--------------------------------------------------------------------------
//| Rutas Protegidas (Requieren autenticación)
//|--------------------------------------------------------------------------
//*/

Route::middleware(['auth'])->group(function () {
    // Gestión de usuarios (solo admin)
    Route::middleware('admin')->controller(UserController::class)->group(function () {
        Route::resource('usuarios', UserController::class)->except(['create', 'store']);
        Route::put('/usuarios/{id}/toggle-status', 'toggleStatus')->name('usuarios.toggle-status');
    });

    // Perfil propio (cualquier usuario autenticado)
    Route::get('/mi-perfil', [UserController::class, 'profile'])->name('profile');
    Route::put('/actualizar-perfil', [UserController::class, 'updateProfile'])->name('update-profile');

    // Direcciones
    Route::post('/direccion/predeterminada/{id}', [DireccionesController::class, 'marcarComoPredeterminada']);
    Route::resource('direcciones', DireccionesController::class)->parameters([
        'direcciones' => 'direccion'
    ]);

    // Rutas de items protegidas
    Route::prefix('items')->group(function () {
        Route::get('/{slug}/detalle', [ItemController::class, 'VerDetalle'])->name('items.VerDetalle');
        Route::get('/{id}/Talentoeditar', [ItemController::class, 'talentoedit'])->name('items.talentoedit');
        Route::put('/{id}/talentoupdate', [ItemController::class, 'talentoupdate'])->name('items.talentoupdate');
        Route::get('/', [ItemController::class, 'itemsCategoria29'])->name('items.categoria29');
    });

    // Talentos
    Route::get('/talentos/crear', function () {
        $categorias = CategoriaItem::all();
        return view('talentos.agregar-talentos', compact('categorias'));
    })->name('items.talento_create');
    Route::post('/talento/agregar', [ItemController::class, 'AddTalento'])->name('items.AddTalento');
    Route::get('/talentos', [ItemController::class, 'userItemstalento'])->name('items.admintalento');
    Route::get('/items/categoria-29', [ItemController::class, 'itemsCategoria29']);

    // Mensajes
    Route::get('/messages', [MessageController::class, 'index'])->name('messages.index');
    Route::get('/messages/create/{item?}', [MessageController::class, 'create'])->name('messages.create');
    Route::get('/messages/unread/count', [MessageController::class, 'unreadCount'])->name('messages.unreadCount');
    Route::post('/messages', [MessageController::class, 'store'])->name('messages.store');
    Route::get('/messages/{message}', [MessageController::class, 'show'])->name('messages.show');
    Route::post('/messages/{message}/read', [MessageController::class, 'markAsRead'])->name('messages.markAsRead');

    // Notificaciones
    Route::get('/notificaciones', [NotificationController::class, 'listar'])->name('notificaciones.listar');
    Route::post('/notificaciones/leido/{id}', [NotificationController::class, 'marcarLeido'])->name('notificaciones.leido');

    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications', [NotificationController::class, 'store']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy']);

    // Rutas de administración (solo para admin)
    Route::middleware('role:3')->group(function () {
        Route::apiResource('categorias-item', CategoriaItemController::class)->except(['index', 'show']);
    });

    // Rutas de cuenta
    Route::get('/tu-cuenta/updateProducts', function () {
        return view('tu-cuenta.updateProducts.updateProducts');
    })->name('updateProducts');

    Route::get('/tu-cuenta', function () {
        return view('tu-cuenta.tu_cuenta');
    })->name('tu_cuenta');
});



    /* codificacion de erirn en route */

Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('login', [LoginController::class, 'login'])->name('login.post');
Route::post('logout', [LoginController::class, 'logout'])->name('logout');

// Google OAuth
Route::get('auth/google', [GoogleController::class, 'redirectToGoogle'])->name('google.login');
Route::get('auth/google/callback', [GoogleController::class, 'handleGoogleCallback']);


// Password Reset
Route::get('password/reset', [PasswordResetController::class, 'request'])->name('password.request');
Route::post('password/email', [PasswordResetController::class, 'email'])->name('password.email');
Route::get('password/reset/{token}', [PasswordResetController::class, 'reset'])->name('password.reset');
Route::post('password/reset', [PasswordResetController::class, 'update'])->name('password.update');

// Registration routes

Route::controller(RegisterController::class)->group(function () {
    Route::post('/registro', 'registrarUsuario')->name('registro.usuario');
    Route::get('/registro', 'showRegistroForm')->name('registro');

});

// Allow viewing items without verification
Route::get('/items/{item}', [ItemController::class, 'show']);

Route::middleware(['auth', 'verified'])->group(function () {
    // Mi cuenta
    Route::get('/micuenta', function () {
        return redirect()->route('tu_cuenta');
    })->name('micuenta');
    // User type management
    Route::get('/cambiar-tipo-usuario', [UserController::class, 'editTipoUsuario'])->name('usuario.tipo.edit');
    Route::post('/cambiar-tipo-usuario', [UserController::class, 'updateTipoUsuario'])->name('usuario.tipo.update');

  
    // Item management
    // Productos del usuario
    Route::prefix('mis-productos')->group(function () {
        Route::get('/', [ItemController::class, 'userItems'])->name('items.user');
        Route::get('/crear', [ItemController::class, 'create'])->name('items.create');
        Route::post('/', [ItemController::class, 'store'])->name('items.store');
    });

    Route::get('gestiona-item', [ItemController::class, 'gestion'])->name('items.gestion');
    Route::get('/items/{item}/edit', [ItemController::class, 'edit'])->name('items.edit');
    Route::put('/items/{item}', [ItemController::class, 'update'])->name('items.update');
    Route::delete('/items/{item}', [ItemController::class, 'destroy'])->name('items.destroy');
});



// Email verification routes
Route::get('email/verify', [VerificationController::class, 'show'])->middleware('auth')->name('verification.notice');
Route::get('email/verify/{id}/{hash}', [VerificationController::class, 'verify'])
    ->middleware(['auth', 'signed'])
    ->name('verification.verify');
Route::post('email/resend', [VerificationController::class, 'resend'])->middleware('auth')->name('verification.resend');

/*
|--------------------------------------------------------------------------
| Fallback para rutas no encontradas
|--------------------------------------------------------------------------
*/
/*

Route::fallback(function () {
    return response()->json([
        'success' => false,
        'message' => 'Ruta no encontrada',
        'requested_url' => request()->fullUrl(), // Muestra la URL solicitada
        'defined_routes' => Route::getRoutes()->getRoutesByName() // Lista rutas (cuidado en producción)
    ], 404);
});*/

// -----------------------------------------------------------------------
// Administración de Compras
// -----------------------------------------------------------------------

// Rutas accesibles por admin Y superadmin
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // Panel principal con tabs
    Route::get('/', [AdminComprasController::class, 'index'])->name('index');

    // Compras
    Route::get('/compras', [AdminComprasController::class, 'indexCompras'])->name('compras.index');
    Route::get('/compras/{id}', [AdminComprasController::class, 'showCompra'])->name('compras.show');
    Route::post('/compras/{id}/estado', [AdminComprasController::class, 'actualizarEstado'])->name('compras.estado');

    // Ventas
    Route::get('/ventas/{id}', [AdminComprasController::class, 'showVenta'])->name('ventas.show');

    // Intercambios
    Route::get('/intercambios/{id}', [AdminComprasController::class, 'showIntercambio'])->name('intercambios.show');
    Route::post('/intercambios/{id}/estado', [AdminComprasController::class, 'actualizarEstadoIntercambio'])->name('intercambios.estado');

    // Mensajes predefinidos — solo lectura para admin normal
    Route::get('/mensajes-predefinidos', [\App\Http\Controllers\Admin\AdminMensajesController::class, 'index'])->name('mensajes.index');
});

// Rutas exclusivas de superadmin
Route::middleware(['auth', 'superadmin'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard de estadísticas
    Route::get('/estadisticas', [AdminStatsController::class, 'index'])->name('stats.index');
    Route::get('/estadisticas/data', [AdminStatsController::class, 'data'])->name('stats.data');
    Route::post('/estadisticas/delivery-config/{clave}', [\App\API\DeliveryZonaController::class, 'updateConfig'])->name('stats.delivery.config');

    // Mensajes predefinidos — mutaciones solo superadmin
    Route::post('/mensajes-predefinidos', [\App\Http\Controllers\Admin\AdminMensajesController::class, 'store'])->name('mensajes.store');
    Route::put('/mensajes-predefinidos/{id}', [\App\Http\Controllers\Admin\AdminMensajesController::class, 'update'])->name('mensajes.update');
    Route::delete('/mensajes-predefinidos/{id}', [\App\Http\Controllers\Admin\AdminMensajesController::class, 'destroy'])->name('mensajes.destroy');
    Route::patch('/mensajes-predefinidos/{id}/toggle', [\App\Http\Controllers\Admin\AdminMensajesController::class, 'toggleActivo'])->name('mensajes.toggle');
});
