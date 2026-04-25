<?php
// Diagnóstico básico - acceder via: https://cambialord.com/check.php
echo "<pre>\n";
echo "PHP: " . phpversion() . "\n";
echo "Server: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'N/A') . "\n";
echo "Doc Root: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'N/A') . "\n\n";

// Intentar cargar Laravel
try {
    require __DIR__ . '/../vendor/autoload.php';
    echo "✅ Autoload OK\n";
} catch (Throwable $e) {
    echo "❌ Autoload: " . $e->getMessage() . "\n";
    echo "</pre>";
    exit;
}

try {
    $app = require __DIR__ . '/../bootstrap/app.php';
    echo "✅ Bootstrap OK\n";
} catch (Throwable $e) {
    echo "❌ Bootstrap: " . $e->getMessage() . "\n";
    echo "   File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "</pre>";
    exit;
}

try {
    $kernel = $app->make('Illuminate\Contracts\Console\Kernel');
    $kernel->bootstrap();
    echo "✅ Kernel OK\n";
} catch (Throwable $e) {
    echo "❌ Kernel: " . $e->getMessage() . "\n";
    echo "   File: " . basename($e->getFile()) . ":" . $e->getLine() . "\n";
    $prev = $e->getPrevious();
    if ($prev) echo "   Caused by: " . $prev->getMessage() . "\n";
    echo "</pre>";
    exit;
}

// Verificar BD
try {
    $pdo = Illuminate\Support\Facades\DB::connection()->getPdo();
    echo "✅ BD conectada: " . config('database.default') . "\n";
} catch (Throwable $e) {
    echo "❌ BD: " . $e->getMessage() . "\n";
}

// Verificar migraciones pendientes
try {
    Illuminate\Support\Facades\Artisan::call('migrate:status');
    $status = Illuminate\Support\Facades\Artisan::output();
    $pending = substr_count($status, 'Pending');
    echo "\nMigraciones pendientes: " . $pending . "\n";
    if ($pending > 0) {
        echo $status;
    }
} catch (Throwable $e) {
    echo "❌ Migrate status: " . $e->getMessage() . "\n";
}

echo "\n✅ Diagnóstico completo\n";
echo "</pre>";
