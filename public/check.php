<?php
echo "<pre>\n";

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

// Crear tabla sessions si no existe
try {
    if (!Illuminate\Support\Facades\Schema::hasTable('sessions')) {
        Illuminate\Support\Facades\Schema::create('sessions', function ($table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
        echo "✅ Tabla sessions creada\n";
    } else {
        echo "✅ Tabla sessions ya existe\n";
    }
} catch (Throwable $e) {
    echo "❌ Error creando sessions: " . $e->getMessage() . "\n";
}

// Limpiar caches
$cmds = ['view:clear', 'config:clear', 'route:clear', 'cache:clear'];
foreach ($cmds as $cmd) {
    try {
        Illuminate\Support\Facades\Artisan::call($cmd);
        echo "✅ $cmd OK\n";
    } catch (Throwable $e) {
        echo "❌ $cmd: " . $e->getMessage() . "\n";
    }
}

// Test /home
echo "\n--- Test /home ---\n";
try {
    $httpKernel = $app->make('Illuminate\Contracts\Http\Kernel');
    $request = Illuminate\Http\Request::create('/home', 'GET');
    $response = $httpKernel->handle($request);
    echo "Status: " . $response->getStatusCode() . "\n";
    if ($response->getStatusCode() < 400) {
        echo "✅ /home funciona (" . strlen($response->getContent()) . " bytes)\n";
    }
} catch (Throwable $e) {
    echo "❌ " . $e->getMessage() . "\n";
}

echo "\n✅ Listo\n</pre>";
