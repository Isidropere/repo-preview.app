<?php

use Illuminate\Support\Facades\Artisan;

/**
 * Script de utilidad para ejecutar migraciones y optimización
 * de manera programática (útil para entornos sin acceso directo a SSH).
 * 
 * SEGURIDAD: Se requiere un token en la URL (?token=cambialo_2026)
 */





header('Content-Type: text/html; charset=utf-8');
echo "<pre style='background: #1a1a1a; color: #00ff00; padding: 20px; border-radius: 8px; font-family: monospace;'>";

// Seguridad: Validar token
$token_esperado = 'cambialo_2026';
if (!isset($_GET['token']) || $_GET['token'] !== $token_esperado) {
    die("❌ Acceso denegado. Token inválido.");
}

// Determinar la ruta base
$basePath = file_exists(__DIR__.'/vendor/autoload.php') ? __DIR__ : __DIR__.'/..';

require $basePath.'/vendor/autoload.php';
$app = require_once $basePath.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "--- INICIANDO PROCESO DE MIGRACIÓN Y OPTIMIZACIÓN ---\n\n";

try {
    echo "1. Ejecutando migraciones...\n";
    $status = Artisan::call('migrate', ['--force' => true]);
    echo Artisan::output();
    echo ($status === 0) ? "✅ Migraciones completadas.\n\n" : "⚠️  Aviso: El proceso terminó con código $status\n\n";

    echo "2. Poblando datos del sistema (Cuentas, Configuración)...\n";
    Artisan::call('db:seed', ['--class' => 'SystemDataSeeder', '--force' => true]);
    echo Artisan::output();
    echo "✅ Datos poblados.\n\n";

    echo "3. Limpiando caché y optimizando...\n";
    Artisan::call('optimize:clear');
    echo Artisan::output();
    Artisan::call('optimize');
    echo Artisan::output();
    echo "✅ Optimización completada.\n\n";

    echo "--- PROCESO FINALIZADO CON ÉXITO ---\n";
} catch (\Exception $e) {
    echo "\n❌ ERROR durante el proceso:\n";
    echo "Mensaje: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . " (Línea " . $e->getLine() . ")\n";
}
echo "</pre>";
