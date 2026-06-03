<?php

use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminComprasController;
use App\Http\Controllers\Admin\AdminStatsController;
use App\Http\Controllers\Admin\ERPController;
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
use App\Http\Controllers\Auth\SocialAuthController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\Auth\VerificationController;
use App\Http\Controllers\HomeController;


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

// Delivery calcular (ruta web)
Route::get('/delivery/calcular', [\App\API\DeliveryZonaController::class, 'calcular'])->name('delivery.calcular');

// Crear symlink storage en hosting compartido (MochaHost)
// Visitar /storage-link una sola vez después de deploy

// Ruta temporal para correr migraciones y limpiar caches en hosting sin SSH
// Visitar UNA SOLA VEZ: /deploy-migrate?key=CambiaRD2026
// ELIMINAR DESPUÉS DE USAR
Route::get('/deploy-migrate', function (\Illuminate\Http\Request $request) {
    if ($request->query('key') !== 'CambiaRD2026') {
        abort(404);
    }

    $output = [];

    // 1. Migraciones
    try {
        \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
        $output[] = '✅ Migraciones: ' . \Illuminate\Support\Facades\Artisan::output();
    } catch (\Throwable $e) {
        $output[] = '❌ Migraciones: ' . $e->getMessage();
    }

    // 2. Limpiar caches
    $commands = ['view:clear', 'config:clear', 'route:clear', 'cache:clear'];
    foreach ($commands as $cmd) {
        try {
            \Illuminate\Support\Facades\Artisan::call($cmd);
            $output[] = '✅ ' . $cmd . ': OK';
        } catch (\Throwable $e) {
            $output[] = '❌ ' . $cmd . ': ' . $e->getMessage();
        }
    }

    // 3. Info del estado
    $output[] = '';
    $output[] = '--- Estado ---';
    $output[] = 'PHP: ' . phpversion();
    $output[] = 'Laravel: ' . app()->version();
    $output[] = 'APP_DEBUG: ' . (config('app.debug') ? 'true' : 'false');
    $output[] = 'APP_ENV: ' . config('app.env');
    $output[] = 'DB: ' . config('database.default');

    return '<pre>' . implode("\n", $output) . '</pre>';
});

Route::get('/storage-link', function () {
    $target = storage_path('app/public');
    $link   = public_path('storage');

    if (file_exists($link)) {
        return 'El enlace /public/storage ya existe.';
    }

    if (function_exists('symlink')) {
        try {
            symlink($target, $link);
            return 'Symlink creado correctamente: public/storage → storage/app/public';
        } catch (\Throwable $e) {
            // Si symlink falla, copiar
        }
    }

    \Illuminate\Support\Facades\File::copyDirectory($target, $link);
    return 'Directorio copiado (symlink no disponible).';
});

// Migrar imágenes de storage/app/public/ a public/ (ejecutar una vez en MochaHost)
Route::get('/migrate-images', function () {
    $source = storage_path('app/public');
    $dest   = public_path();
    $count  = 0;

    $dirs = ['imgs/articulos/items', 'imgs/videos/items', 'videos/articulos/items'];
    foreach ($dirs as $dir) {
        $src = $source . '/' . $dir;
        $dst = $dest . '/' . $dir;
        if (is_dir($src)) {
            if (!is_dir($dst)) {
                mkdir($dst, 0755, true);
            }
            foreach (scandir($src) as $file) {
                if ($file === '.' || $file === '..') continue;
                if (!file_exists($dst . '/' . $file)) {
                    copy($src . '/' . $file, $dst . '/' . $file);
                    $count++;
                }
            }
        }
    }

    return "Migración completada: {$count} archivos copiados a public/.";
});

Route::get('/debug-paths', function () {
    return response()->json([
        'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? 'N/A',
        'base_path' => base_path(),
        'public_path' => public_path(),
        'storage_path' => storage_path('app/public'),
        'has_public_html' => file_exists(base_path('public_html')),
        'has_symlink' => file_exists(public_path('storage')),
        'sample_image_in_public' => file_exists(public_path('imgs/articulos/items/item_21_20260529135916_PsnYkcMrGO.jpg')),
        'sample_image_in_storage' => file_exists(storage_path('app/public/imgs/articulos/items/item_21_20260529135916_PsnYkcMrGO.jpg')),
        'storage_dir_exists' => is_dir(storage_path('app/public/imgs/articulos/items')),
        'public_dir_exists' => is_dir(public_path('imgs/articulos/items'))
    ]);
});

Route::get('/sync-public-folders', function () {
    $source = base_path('public');
    $dest   = base_path('public_html');

    if (!is_dir($source)) {
        return "El directorio de origen public/ no existe.";
    }
    if (!is_dir($dest)) {
        return "El directorio de destino public_html/ no existe.";
    }

    $count = 0;
    try {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            $subPath = $iterator->getSubPathName();
            $targetPath = $dest . '/' . $subPath;

            if ($item->isDir()) {
                if (!is_dir($targetPath)) {
                    mkdir($targetPath, 0755, true);
                }
            } else {
                if (!file_exists($targetPath)) {
                    copy($item->getRealPath(), $targetPath);
                    $count++;
                }
            }
        }
        return "Sincronización completada: {$count} archivos copiados de public/ a public_html/.";
    } catch (\Throwable $e) {
        return "Error durante la sincronización: " . $e->getMessage();
    }
});

Route::get('/home', [HomeController::class, 'index'])->name('home');


Route::get('/notificaciones/contador', function () {
    $userId = auth()->id();
    $total = Redis::get("notificaciones:$userId") ?? 0;
    return response()->json(['total' => $total]);
})->middleware('auth');

Route::get('/usuario/municipio', function () {
    $userId = auth()->id();
    if (!$userId) return response()->json(['municipio' => '']);
    $dir = \App\Models\Direcciones::where('id_user', $userId)
        ->where('es_predeterminada', 1)
        ->with('municipio')
        ->first()
        ?? \App\Models\Direcciones::where('id_user', $userId)->with('municipio')->first();
    return response()->json(['municipio' => $dir?->municipio?->municipio ?? '']);
})->middleware('auth')->name('usuario.municipio');


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

Route::get('/como-publicar-articulo', function () {
    return view('como-publicar.articulo');
})->name('como-publicar-articulo');

Route::get('/como-publicar-talento', function () {
    return view('como-publicar.talento');
})->name('como-publicar-talento');




Route::get('/registrarse', function () {
    return view('registrarse.registrarse');
})->name('registrarse');

Route::get('/ayuda', function () {
    return view('ayuda.ayuda');
})->name('ayuda');

Route::get('/contraseña', function () {
    return view('contraseña.contrasenna');
})->name('contraseña');

Route::get('/historial', [\App\Http\Controllers\HistorialController::class, 'index'])->middleware('auth')->name('historial');

Route::get('/items/search_header', [ItemController::class, 'search_header'])->middleware('throttle:30,1')->name('items.search_header');
// /buscar redirige a search_header (consolidado)
Route::get('/buscar', fn() => redirect()->route('items.search_header', request()->query()))->middleware('throttle:30,1');

// Rutas de categorías
Route::get('categorias-item', [CategoriaItemController::class, 'index']);
Route::get('categorias-item/{id}', [CategoriaItemController::class, 'show']);

// Rutas de ubicación
Route::get('/provincias', [ProvinciaController::class, 'getProvincias']);
Route::get('/municipios', [MunicipioController::class, 'getMunicipio']);
Route::get('distritos-municipales', [DistritoMunicipalController::class, 'index']);

// Rutas de tipos de productos
Route::get('/intercambio', [ItemController::class, 'showItemsTipo2y3'])->middleware('throttle:60,1')->name('intercambio');
Route::get('/compras', [ItemController::class, 'showItemsTipo1'])->middleware('throttle:60,1')->name('compra');







Route::prefix('items')->group(function () {
    Route::get('/', [ItemController::class, 'index'])->name('items.index');
    Route::get('/categoria29', [ItemController::class, 'soloCategoria29'])->name('items.soloCategoria29');
    Route::get('/search', [ItemController::class, 'search'])->middleware('throttle:30,1')->name('items.search');
    Route::get('/info/{id}', [ItemController::class, 'info'])->name('items.info');
    Route::get('/otras-categorias', [ItemController::class, 'otrasCategorias'])->name('categorias.otras');
    Route::get('/categoria/{slug}', [ItemController::class, 'porCategoria'])->name('categorias.show');
    Route::get('/producto/{slug}', [ItemController::class, 'showDetail'])->name('producto.detalle');

    // Rutas protegidas con segmentos específicos — deben ir ANTES del wildcard /{id}
    Route::get('/{slug}/edit', [ItemController::class, 'edit'])->middleware(['auth', 'verified'])->name('items.edit');
    Route::get('/{slug}/Talentoeditar', [ItemController::class, 'talentoedit'])->middleware(['auth'])->name('items.talentoedit');
    Route::get('/{slug}/detalle', [ItemController::class, 'VerDetalle'])->middleware(['auth'])->name('items.VerDetalle');

    // Wildcard al final — solo captura slugs sin segmentos adicionales
    Route::get('/{id}', [ItemController::class, 'show'])->name('items.show')->where('id', '[^/]+');
});


Route::middleware(['auth'])->prefix('carrito')->name('carrito.')->group(function () {
    Route::get('/item-ids', [CarritoController::class, 'getItemIds'])->name('item_ids');
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
    Route::get('/',           [NegociacionController::class, 'misIntercambios'])->name('negociaciones.mis');
    Route::get('/pendientes', [NegociacionController::class, 'contarPendientes'])->name('negociaciones.pendientes');
    Route::post('/{id}/mensaje', [NegociacionController::class, 'enviarMensaje'])->name('negociaciones.mensaje');
    Route::get('/{item}',     [NegociacionController::class, 'index'])->name('negociaciones.index');
    Route::post('/{id}/aceptar', [NegociacionController::class, 'aceptar'])->name('negociaciones.aceptar');
    Route::post('/{id}/rechazar', [NegociacionController::class, 'rechazar'])->name('negociaciones.rechazar');
    Route::get('/{id}/contraoferta', [NegociacionController::class, 'contraoferta'])->name('negociaciones.contraoferta');
    Route::post('/{id}/contraoferta', [NegociacionController::class, 'storeContraoferta'])->name('negociaciones.store_contraoferta');
    Route::post('/{id}/cancelar', [NegociacionController::class, 'cancelar'])->name('negociaciones.cancelar');
    Route::post('/{id}/completar', [NegociacionController::class, 'completar'])->name('negociaciones.completar');
    Route::post('/{id}/confirmar-emisor', [NegociacionController::class, 'confirmarEmisor'])->name('negociaciones.confirmar_emisor');
    Route::post('/{id}/confirmar-receptor', [NegociacionController::class, 'confirmarReceptor'])->name('negociaciones.confirmar_receptor');
    Route::post('/{id}/aceptar-contraoferta', [NegociacionController::class, 'aceptarComoEmisor'])->name('negociaciones.aceptar_contraoferta');
    Route::post('/{id}/modo-entrega', [NegociacionController::class, 'seleccionarModoEntrega'])->name('negociaciones.modo_entrega');
    Route::post('/{id}/confirmar-entrega', [NegociacionController::class, 'confirmarEntrega'])->name('negociaciones.confirmar_entrega');

    Route::post('/enviar', [App\Http\Controllers\NegociacionController::class, 'store'])
        ->middleware('throttle.sensitive:10,1')
        ->name('negociaciones.store');

    Route::get('/ver-chat/{id}', [NegociacionController::class, 'verChat'])->name('negociaciones.verChat');
    Route::get('/{id}/pago',    [NegociacionController::class, 'mostrarPago'])->name('negociaciones.pago');
    Route::post('/{id}/pago',   [NegociacionController::class, 'procesarPago'])->name('negociaciones.pago.procesar');
});

// Solicitudes de servicio (aprobación previa al pago de talentos)
Route::middleware(['auth'])->group(function () {
    Route::get('/mis-ventas-talentos', [\App\Http\Controllers\SolicitudServicioController::class, 'index'])->name('solicitudes.index');
    Route::post('/solicitudes-servicio/{id}/aprobar', [\App\Http\Controllers\SolicitudServicioController::class, 'aprobar'])->name('solicitudes.aprobar');
    Route::post('/solicitudes-servicio/{id}/rechazar', [\App\Http\Controllers\SolicitudServicioController::class, 'rechazar'])->name('solicitudes.rechazar');
    // JSON endpoints para carrito y mis-ventas-talentos
    Route::post('/solicitudes-servicio/enviar', [\App\Http\Controllers\SolicitudServicioController::class, 'enviarDesdeCarrito'])->name('solicitudes.enviar');
    Route::get('/solicitudes-servicio/estado/{idItem}', [\App\Http\Controllers\SolicitudServicioController::class, 'estadoItem'])->name('solicitudes.estado');
    Route::post('/solicitudes-servicio/{id}/aprobar-json', [\App\Http\Controllers\SolicitudServicioController::class, 'aprobarJson'])->name('solicitudes.aprobar_json');
    Route::post('/solicitudes-servicio/{id}/rechazar-json', [\App\Http\Controllers\SolicitudServicioController::class, 'rechazarJson'])->name('solicitudes.rechazar_json');
});

// Hoja de vida (perfil profesional para talentos)
Route::middleware(['auth'])->group(function () {
    Route::get('/mi-hoja-vida', [\App\Http\Controllers\HojaVidaController::class, 'form'])->name('hoja-vida.form');
    Route::post('/mi-hoja-vida', [\App\Http\Controllers\HojaVidaController::class, 'save'])->name('hoja-vida.save');
});

// Rating de intercambios
Route::post('/rating', function (\Illuminate\Http\Request $request) {
    $request->validate(['id_miembro' => 'required|integer|exists:users,id', 'rating' => 'required|integer|min:1|max:5']);
    \App\Models\Rating::create(['id_usuario' => auth()->id(), 'id_miembro' => $request->id_miembro, 'rating' => $request->rating]);
    return back()->with('success', '¡Gracias por tu calificación!');
})->middleware('auth')->name('rating.store');

// DEBUG: ruta temporal para diagnosticar /negociaciones
Route::get('/debug-negociaciones', function () {
    $log = [];
    $log[] = '=== DEBUG /negociaciones ===';
    $log[] = 'Fecha: ' . now();

    // Paso 1: Auth
    $log[] = '';
    $log[] = '--- PASO 1: Autenticación ---';
    $log[] = 'Auth check: ' . (auth()->check() ? 'SI' : 'NO');
    $log[] = 'User ID: ' . (auth()->id() ?? 'NULL');

    if (!auth()->check()) {
        $log[] = 'ERROR: No hay usuario autenticado';
        file_put_contents(storage_path('logs/debug-negociaciones.txt'), implode("\n", $log));
        return 'Debug guardado en storage/logs/debug-negociaciones.txt';
    }

    $userId = auth()->id();

    // Paso 2: Query emisor
    $log[] = '';
    $log[] = '--- PASO 2: Query comoEmisor ---';
    try {
        $comoEmisor = \App\Models\Negociacion::where('usuario_emisor_id', $userId)
            ->whereNotIn('estado', ['cancelado'])
            ->with(['item.imagenes', 'usuarioReceptor', 'item.inventarios'])
            ->orderByDesc('id_negociacion')
            ->get();
        $log[] = 'OK - ' . $comoEmisor->count() . ' registros';
        foreach ($comoEmisor as $n) {
            $log[] = '  #' . $n->id_negociacion . ' estado=' . $n->estado . ' item_id=' . $n->receptor_item_id . ' item_exists=' . ($n->item ? 'SI' : 'NO');
        }
    } catch (\Throwable $e) {
        $log[] = 'ERROR: ' . $e->getMessage();
        $log[] = 'FILE: ' . $e->getFile() . ':' . $e->getLine();
    }

    // Paso 3: Query receptor
    $log[] = '';
    $log[] = '--- PASO 3: Query comoReceptor ---';
    try {
        $comoReceptor = \App\Models\Negociacion::where('usuario_receptor_id', $userId)
            ->whereNotIn('estado', ['cancelado'])
            ->with(['item.imagenes', 'usuario', 'item.inventarios'])
            ->orderByDesc('id_negociacion')
            ->get();
        $log[] = 'OK - ' . $comoReceptor->count() . ' registros';
        foreach ($comoReceptor as $n) {
            $log[] = '  #' . $n->id_negociacion . ' estado=' . $n->estado . ' item_id=' . $n->receptor_item_id . ' item_exists=' . ($n->item ? 'SI' : 'NO');
        }
    } catch (\Throwable $e) {
        $log[] = 'ERROR: ' . $e->getMessage();
        $log[] = 'FILE: ' . $e->getFile() . ':' . $e->getLine();
    }

    // Paso 4: Tarjetas
    $log[] = '';
    $log[] = '--- PASO 4: Tarjetas ---';
    try {
        $tarjetas = \App\Models\TarjetaPago::where('id_user', $userId)->where('estatus', 1)->get();
        $log[] = 'OK - ' . $tarjetas->count() . ' tarjetas';
    } catch (\Throwable $e) {
        $log[] = 'ERROR: ' . $e->getMessage();
    }

    // Paso 5: Columnas de negociaciones
    $log[] = '';
    $log[] = '--- PASO 5: Columnas tabla negociaciones ---';
    try {
        $cols = \Illuminate\Support\Facades\Schema::getColumnListing('negociaciones');
        $log[] = implode(', ', $cols);
        $log[] = 'receptor_confirmado existe: ' . (in_array('receptor_confirmado', $cols) ? 'SI' : 'NO');
    } catch (\Throwable $e) {
        $log[] = 'ERROR: ' . $e->getMessage();
    }

    // Paso 6: Render vista
    $log[] = '';
    $log[] = '--- PASO 6: Render vista ---';
    try {
        $html = view('negociaciones.mis-intercambios', compact('comoEmisor', 'comoReceptor', 'tarjetas'))->render();
        $log[] = 'OK - ' . strlen($html) . ' bytes renderizados';
    } catch (\Throwable $e) {
        $log[] = 'ERROR: ' . $e->getMessage();
        $log[] = 'FILE: ' . basename($e->getFile()) . ':' . $e->getLine();
        // Buscar causa raíz
        $prev = $e->getPrevious();
        while ($prev) {
            $log[] = 'CAUSED BY: ' . $prev->getMessage();
            $log[] = '  AT: ' . basename($prev->getFile()) . ':' . $prev->getLine();
            $prev = $prev->getPrevious();
        }
    }

    $log[] = '';
    $log[] = '=== FIN DEBUG ===';

    file_put_contents(storage_path('logs/debug-negociaciones.txt'), implode("\n", $log));
    return '<pre>' . implode("\n", $log) . '</pre>';
})->middleware('auth');


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
    Route::post('/cambiar-contrasena', [UserController::class, 'updatePassword'])->name('password.update.profile');

    // Direcciones
    Route::post('/direccion/predeterminada/{id}', [DireccionesController::class, 'marcarComoPredeterminada']);
    Route::resource('direcciones', DireccionesController::class)->parameters([
        'direcciones' => 'direccion'
    ]);

    // Rutas de items protegidas
    Route::put('/items/{slug}/talentoupdate', [ItemController::class, 'talentoupdate'])->name('items.talentoupdate');
    Route::get('/items', [ItemController::class, 'itemsCategoria29'])->name('items.categoria29');

    // Talentos
    Route::get('/talentos/crear', function () {
        // Verificar hoja de vida antes de crear talento
        if (!\App\Models\HojaVida::where('id_user', auth()->id())->exists()) {
            return redirect()->route('hoja-vida.form')
                ->with('warning', 'Debes completar tu hoja de vida antes de publicar un talento.')
                ->with('redirect_after', 'items.talento_create');
        }

        $categorias = CategoriaItem::all();
        $tarjetas = \App\Models\TarjetaPago::where('id_user', auth()->id())->where('estatus', 1)->get();
        $montoRegistro = \App\Models\ConfigTarifaCategoria29::vigente()->monto_registro;
        $direccionesCount = \App\Models\Direcciones::where('id_user', auth()->id())->count();
        return view('talentos.agregar-talentos', compact('categorias', 'tarjetas', 'montoRegistro', 'direccionesCount'));
    })->name('items.talento_create');
    Route::post('/talento/agregar', [ItemController::class, 'AddTalento'])->name('items.AddTalento');
    Route::get('/talentos', [ItemController::class, 'userItemstalento'])->name('items.admintalento');
    Route::get('/items/categoria-29', [ItemController::class, 'itemsCategoria29']);

    // Flujo de pago para registro de talento (categoría 29)
    Route::get('/talento/pago', [\App\Http\Controllers\TalentoRegistroPagoController::class, 'mostrarPago'])->name('talento.pago.show');
    Route::post('/talento/pago', [\App\Http\Controllers\TalentoRegistroPagoController::class, 'procesarPago'])
        ->middleware('throttle.sensitive:5,1')
        ->name('talento.pago.procesar');

    // Mensajes
    Route::get('/messages', [MessageController::class, 'index'])->name('messages.index');
    Route::get('/messages/create/{item?}', [MessageController::class, 'create'])->name('messages.create');
    Route::get('/messages/unread/count', [MessageController::class, 'unreadCount'])->name('messages.unreadCount');
    Route::post('/messages', [MessageController::class, 'store'])->name('messages.store');
    Route::get('/messages/{message}', [MessageController::class, 'show'])->name('messages.show');
    Route::post('/messages/{message}/read', [MessageController::class, 'markAsRead'])->name('messages.markAsRead');

    // Notificaciones
    Route::get('/notificaciones', [NotificationController::class, 'listar'])->name('notificaciones.listar');
    Route::get('/mis-notificaciones', [NotificationController::class, 'misNotificaciones'])->name('notificaciones.pagina');
    Route::post('/notificaciones/leido/{id}', [NotificationController::class, 'marcarLeido'])->name('notificaciones.leido');
    Route::post('/notificaciones/leer-todas', [NotificationController::class, 'marcarTodasLeidas'])->name('notificaciones.leerTodas');

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
Route::get('logout', [LoginController::class, 'logout'])->name('logout.get');
Route::post('adultos/verificar', [LoginController::class, 'verificarCredenciales'])->middleware('auth')->name('adultos.verificar');

// Social OAuth unificado: Google, Facebook, Instagram
// Rutas: /auth/{provider}  y  /auth/{provider}/callback
Route::get('auth/{provider}', [SocialAuthController::class, 'redirect'])
    ->where('provider', 'google|facebook|instagram')
    ->name('social.login');
Route::get('auth/{provider}/callback', [SocialAuthController::class, 'callback'])
    ->where('provider', 'google|facebook|instagram')
    ->name('social.callback');


// Password Reset
Route::get('password/reset', [PasswordResetController::class, 'request'])->name('password.request');
Route::post('password/email', [PasswordResetController::class, 'email'])->name('password.email');
Route::get('password/reset/{token}', [PasswordResetController::class, 'reset'])->name('password.reset');
Route::post('password/reset', [PasswordResetController::class, 'update'])->name('password.update');

// Registration routes

// Transporte y Mudanza
Route::get('/transporte', [\App\Http\Controllers\TransporteController::class, 'create'])->name('transporte.create');
Route::post('/transporte', [\App\Http\Controllers\TransporteController::class, 'store'])->name('transporte.store');

Route::controller(RegisterController::class)->group(function () {
    Route::post('/registro', 'registrarUsuario')->name('registro.usuario');
    Route::get('/registro', 'showRegistroForm')->name('registro');
    Route::get('/registro/verificar', 'showVerificarForm')->name('registro.verificar.form');
    Route::post('/registro/verificar', 'verificarCodigo')->name('registro.verificar');
    Route::post('/registro/reenviar', 'reenviarCodigo')->name('registro.reenviar');
});

// Allow viewing items without verification (handled in prefix group above)

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
    Route::put('/items/{slug}', [ItemController::class, 'update'])->name('items.update');
    Route::delete('/items/{slug}', [ItemController::class, 'destroy'])->name('items.destroy');
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
    Route::post('/compras/{id}/tracking', [AdminComprasController::class, 'enviarTracking'])->name('compras.tracking');
    Route::get('/compras/{id}/pdf', [AdminComprasController::class, 'descargarPdf'])->name('compras.pdf');

    // Ventas
    Route::get('/ventas/{id}', [AdminComprasController::class, 'showVenta'])->name('ventas.show');

    // Intercambios
    Route::get('/intercambios/{id}', [AdminComprasController::class, 'showIntercambio'])->name('intercambios.show');
    Route::post('/intercambios/{id}/estado', [AdminComprasController::class, 'actualizarEstadoIntercambio'])->name('intercambios.estado');

    // --- GESTIÓN EMPRESARIAL (Super Admin) ---
    Route::prefix('erp')->name('erp.')->group(function () {
        Route::get('/contabilidad', [ERPController::class, 'contabilidad'])->name('contabilidad');
        Route::post('/contabilidad/asiento', [ERPController::class, 'storeAsiento'])->name('contabilidad.asiento');
        Route::get('/contabilidad/asientos/{id}/detalle', [ERPController::class, 'detalleAsiento'])->name('contabilidad.asiento.detalle');
        Route::get('/contabilidad/reportes', [ERPController::class, 'reportesFinancieros'])->name('contabilidad.reportes');
        Route::get('/contabilidad/reportes/{tipo}/pdf', [ERPController::class, 'descargarReportePdf'])->name('contabilidad.reportes.pdf');
        
        // Transporte
        Route::get('/transporte', [\App\Http\Controllers\Admin\AdminTransporteController::class, 'index'])->name('transporte.index');
        Route::post('/transporte/{id}/aprobar', [\App\Http\Controllers\Admin\AdminTransporteController::class, 'aprobar'])->name('transporte.aprobar');
        Route::post('/transporte/{id}/rechazar', [\App\Http\Controllers\Admin\AdminTransporteController::class, 'rechazar'])->name('transporte.rechazar');
        Route::get('/transporte/{id}/pdf', [\App\Http\Controllers\Admin\AdminTransporteController::class, 'generarPdf'])->name('transporte.pdf');
        Route::post('/transporte/articulos', [\App\Http\Controllers\Admin\AdminTransporteController::class, 'storeArticulo'])->name('transporte.articulos.store');
        Route::put('/transporte/articulos/{id}', [\App\Http\Controllers\Admin\AdminTransporteController::class, 'updateArticulo'])->name('transporte.articulos.update');
        Route::delete('/transporte/articulos/{id}', [\App\Http\Controllers\Admin\AdminTransporteController::class, 'destroyArticulo'])->name('transporte.articulos.destroy');
        Route::put('/transporte/config', [\App\Http\Controllers\Admin\AdminTransporteController::class, 'updateConfig'])->name('transporte.config.update');


        // Cuentas CRUD
        Route::post('/contabilidad/cuentas', [ERPController::class, 'storeCuenta'])->name('contabilidad.cuentas.store');
        Route::put('/contabilidad/cuentas/{id}', [ERPController::class, 'updateCuenta'])->name('contabilidad.cuentas.update');
        Route::delete('/contabilidad/cuentas/{id}', [ERPController::class, 'destroyCuenta'])->name('contabilidad.cuentas.destroy');
        Route::get('/contabilidad/cuentas/{id}/mayor', [ERPController::class, 'libroMayor'])->name('contabilidad.cuentas.mayor');

        Route::get('/inventario', [ERPController::class, 'inventario'])->name('inventario');
        Route::get('/caja', [ERPController::class, 'caja'])->name('caja');
        Route::post('/caja/abrir', [ERPController::class, 'abrirCaja'])->name('caja.abrir');
        Route::post('/caja/cerrar', [ERPController::class, 'cerrarCaja'])->name('caja.cerrar');
    });

    // Mensajes predefinidos — solo lectura para admin normal
    Route::get('/mensajes-predefinidos', [\App\Http\Controllers\Admin\AdminMensajesController::class, 'index'])->name('mensajes.index');

    // Notificaciones a usuarios
    Route::get('/notificaciones', [\App\Http\Controllers\Admin\AdminNotificacionesController::class, 'index'])->name('notificaciones.index');
    Route::post('/notificaciones/enviar', [\App\Http\Controllers\Admin\AdminNotificacionesController::class, 'enviar'])->name('notificaciones.enviar');
    Route::get('/notificaciones/buscar-usuarios', [\App\Http\Controllers\Admin\AdminNotificacionesController::class, 'buscarUsuarios'])->name('notificaciones.buscar');

    // Panel de aprobación de imágenes (accesible por admin y superadmin)
    Route::prefix('imagenes')->name('imagenes.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\AdminImagenesController::class, 'index'])->name('index');
        Route::post('/items/aprobar-todas',    [\App\Http\Controllers\Admin\AdminImagenesController::class, 'aprobarTodosItems'])->name('items.aprobarTodas');
        Route::post('/items/{id}/aprobar',     [\App\Http\Controllers\Admin\AdminImagenesController::class, 'aprobarItem'])->name('items.aprobar');
        Route::post('/items/{id}/rechazar',    [\App\Http\Controllers\Admin\AdminImagenesController::class, 'rechazarItem'])->name('items.rechazar');
        Route::post('/perfiles/aprobar-todas', [\App\Http\Controllers\Admin\AdminImagenesController::class, 'aprobarTodosPerfiles'])->name('perfiles.aprobarTodas');
        Route::post('/perfiles/{id}/aprobar',  [\App\Http\Controllers\Admin\AdminImagenesController::class, 'aprobarPerfil'])->name('perfiles.aprobar');
        Route::post('/perfiles/{id}/rechazar', [\App\Http\Controllers\Admin\AdminImagenesController::class, 'rechazarPerfil'])->name('perfiles.rechazar');
    });
});

// Rutas exclusivas de superadmin
Route::middleware(['auth', 'superadmin'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard de estadísticas
    Route::get('/estadisticas', [AdminStatsController::class, 'index'])->name('stats.index');
    Route::get('/estadisticas/data', [AdminStatsController::class, 'data'])->name('stats.data');
    Route::post('/estadisticas/delivery-config/{clave}', [\App\API\DeliveryZonaController::class, 'updateConfig'])->name('stats.delivery.config');

    // CRUD Zonas de Delivery
    Route::get('/delivery-zonas', function () {
        return response()->json(\App\Models\DeliveryZona::orderBy('tipo')->orderBy('zona')->get());
    })->name('delivery-zonas.index');
    Route::post('/delivery-zonas', function (\Illuminate\Http\Request $request) {
        try {
            $data = $request->validate([
                'zona'            => 'required|string|max:255',
                'tipo'            => 'required|in:corta,larga,especial,chequeado',
                'pueblos'         => 'nullable|string',
                'precio_persona'  => 'required|numeric|min:0',
                'precio_empresa'  => 'nullable|numeric|min:0',
                'dias_entrega'    => 'nullable|string|max:255',
            ]);
            $pueblos = trim($data['pueblos'] ?? '');
            $data['pueblos'] = $pueblos === ''
                ? []
                : array_values(array_filter(array_map('trim', explode(',', $pueblos))));
            $data['precio_empresa'] = $data['precio_empresa'] ?? $data['precio_persona'];
            $data['activo'] = true;
            $zona = \App\Models\DeliveryZona::create($data);
            return response()->json(['success' => true, 'data' => $zona]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Datos inválidos',
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    })->name('delivery-zonas.store');
    Route::put('/delivery-zonas/{id}', function (\Illuminate\Http\Request $request, $id) {
        try {
            $zona = \App\Models\DeliveryZona::findOrFail($id);
            $data = $request->validate([
                'zona'            => 'sometimes|string|max:255',
                'tipo'            => 'sometimes|in:corta,larga,especial,chequeado',
                'pueblos'         => 'nullable|string',
                'precio_persona'  => 'sometimes|numeric|min:0',
                'precio_empresa'  => 'nullable|numeric|min:0',
                'dias_entrega'    => 'nullable|string|max:255',
                'activo'          => 'sometimes|boolean',
            ]);
            if (array_key_exists('pueblos', $data)) {
                $pueblos = trim($data['pueblos'] ?? '');
                $data['pueblos'] = $pueblos === ''
                    ? []
                    : array_values(array_filter(array_map('trim', explode(',', $pueblos))));
            }
            if (isset($data['precio_persona']) && ! isset($data['precio_empresa'])) {
                $data['precio_empresa'] = $data['precio_persona'];
            }
            $zona->update($data);
            return response()->json(['success' => true, 'data' => $zona->fresh()]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Datos inválidos',
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    })->name('delivery-zonas.update');
    Route::delete('/delivery-zonas/{id}', function ($id) {
        \App\Models\DeliveryZona::findOrFail($id)->delete();
        return response()->json(['success'=>true]);
    })->name('delivery-zonas.destroy');

    // Mensajes predefinidos — mutaciones solo superadmin
    Route::post('/mensajes-predefinidos', [\App\Http\Controllers\Admin\AdminMensajesController::class, 'store'])->name('mensajes.store');
    Route::put('/mensajes-predefinidos/{id}', [\App\Http\Controllers\Admin\AdminMensajesController::class, 'update'])->name('mensajes.update');
    Route::delete('/mensajes-predefinidos/{id}', [\App\Http\Controllers\Admin\AdminMensajesController::class, 'destroy'])->name('mensajes.destroy');
    Route::patch('/mensajes-predefinidos/{id}/toggle', [\App\Http\Controllers\Admin\AdminMensajesController::class, 'toggleActivo'])->name('mensajes.toggle');

    // Cuentas bancarias (informativas)
    Route::get('/cuentas-banco',               [\App\Http\Controllers\Admin\AdminCuentaBancoController::class, 'index'])->name('cuentas.index');
    Route::post('/cuentas-banco',              [\App\Http\Controllers\Admin\AdminCuentaBancoController::class, 'store'])->name('cuentas.store');
    Route::put('/cuentas-banco/{id}',          [\App\Http\Controllers\Admin\AdminCuentaBancoController::class, 'update'])->name('cuentas.update');
    Route::delete('/cuentas-banco/{id}',       [\App\Http\Controllers\Admin\AdminCuentaBancoController::class, 'destroy'])->name('cuentas.destroy');
    Route::patch('/cuentas-banco/{id}/toggle', [\App\Http\Controllers\Admin\AdminCuentaBancoController::class, 'toggleActivo'])->name('cuentas.toggle');

    // Config tarifa categoría 29
    Route::get('/config-tarifa', [\App\Http\Controllers\Admin\AdminConfigTarifaController::class, 'show'])->name('config_tarifa.show');
    Route::put('/config-tarifa', [\App\Http\Controllers\Admin\AdminConfigTarifaController::class, 'update'])->name('config_tarifa.update');
});
