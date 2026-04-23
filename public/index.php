<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Contracts\Http\Kernel;

define('LARAVEL_START', microtime(true));

// Capturar errores fatales y escribirlos al log
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        $msg = date('[Y-m-d H:i:s]') . ' FATAL: ' . $error['message'] . ' in ' . $error['file'] . ':' . $error['line'] . "\n";
        file_put_contents(__DIR__ . '/../storage/logs/cabialoErrores.log', $msg, FILE_APPEND);
    }
});

/* ===================================================
   VERIFICACIÓN DE MODO MANTENIMIENTO
   =================================================== */
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

/* ===================================================
   CARGA DEL AUTOLOADER DE COMPOSER
   =================================================== */
if (!file_exists($autoload = __DIR__.'/../vendor/autoload.php')) {
    die("Error: No se encontró el autoloader de Composer. Ejecuta 'composer install'.");
}
require $autoload;

/* ===================================================
   INICIALIZACIÓN DE LA APLICACIÓN LARAVEL
   =================================================== */
$app = require_once __DIR__.'/../bootstrap/app.php';

if (!$app) {
    die("Error: No se pudo inicializar la aplicación Laravel.");
}

/* ===================================================
   CONFIGURACIÓN DEL KERNEL HTTP
   =================================================== */
$kernel = $app->make(Kernel::class);

$response = $kernel->handle(
    $request = Request::capture()
)->send();

$kernel->terminate($request, $response);

// Register the Composer autoloader...
require __DIR__ . '/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__ . '/../bootstrap/app.php';
