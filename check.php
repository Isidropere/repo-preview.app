<?php

use Illuminate\Support\Facades\Artisan;

/**
 * Script de utilidad para ejecutar migraciones y optimización
 * de manera programática (útil para entornos sin acceso directo a SSH).
 * 
 * SEGURIDAD: Se requiere un token en la URL (?token=cambialo_2026)
 */

if (php_sapi_name() !== 'cli' && (!isset($_GET['token']) || $_GET['token'] !== 'cambialo_2026')) {
    die('Acceso no autorizado.');
}


// Determinar la ruta base (si está en public/ o en la raíz)
$basePath = file_exists(__DIR__.'/vendor/autoload.php') ? __DIR__ : __DIR__.'/..';

require $basePath.'/vendor/autoload.php';
$app = require_once $basePath.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "--- INICIANDO PROCESO DE MIGRACIÓN Y OPTIMIZACIÓN ---\n\n";

try {
    echo "1. Ejecutando migraciones...\n";
    Artisan::call('migrate', ['--force' => true]);
    echo Artisan::output();
    echo "✅ Migraciones completadas.\n\n";

    echo "2. Ejecutando optimización (config, routes, views)...\n";
    Artisan::call('optimize');
    echo Artisan::output();
    echo "✅ Optimización completada.\n\n";

    echo "--- PROCESO FINALIZADO CON ÉXITO ---\n";
} catch (\Exception $e) {
    echo "❌ ERROR durante el proceso: " . $e->getMessage() . "\n";
}
