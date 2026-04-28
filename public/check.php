<?php
echo "<pre>\n";
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

// Verificar y crear columna id_user_rated en ratings
echo "=== Verificar tabla ratings ===\n";
try {
    if (Illuminate\Support\Facades\Schema::hasTable('ratings')) {
        $cols = Illuminate\Support\Facades\Schema::getColumnListing('ratings');
        echo "Columnas: " . implode(', ', $cols) . "\n";
        
        if (!in_array('id_user_rated', $cols)) {
            Illuminate\Support\Facades\Schema::table('ratings', function ($table) {
                $table->unsignedBigInteger('id_user_rated')->nullable()->after('id_usuario');
            });
            echo "✅ Columna id_user_rated creada\n";
        } else {
            echo "✅ id_user_rated ya existe\n";
        }
    } else {
        echo "❌ Tabla ratings no existe\n";
    }
} catch (Throwable $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

// Limpiar caches
$cmds = ['view:clear', 'config:clear', 'route:clear', 'cache:clear'];
foreach ($cmds as $cmd) {
    try {
        Illuminate\Support\Facades\Artisan::call($cmd);
        echo "✅ $cmd\n";
    } catch (Throwable $e) {
        echo "❌ $cmd: " . $e->getMessage() . "\n";
    }
}

echo "\n✅ Listo\n</pre>";
