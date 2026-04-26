<?php
echo "<pre>\n";

// INFO BÁSICA
echo "PHP: " . phpversion() . "\n";
echo "Server: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'N/A') . "\n";
echo "Doc Root: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'N/A') . "\n\n";

// AUTLOAD
try {
    require __DIR__ . '/../vendor/autoload.php';
    echo "✅ Autoload OK\n";
} catch (Throwable $e) {
    echo "❌ Autoload: " . $e->getMessage() . "\n";
    exit("</pre>");
}

// BOOTSTRAP
try {
    $app = require __DIR__ . '/../bootstrap/app.php';
    echo "✅ Bootstrap OK\n";
} catch (Throwable $e) {
    echo "❌ Bootstrap: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    exit("</pre>");
}

// KERNEL
try {
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();
    echo "✅ Kernel OK\n";
} catch (Throwable $e) {
    echo "❌ Kernel: " . $e->getMessage() . "\n";
    echo "File: " . basename($e->getFile()) . ":" . $e->getLine() . "\n";
    if ($e->getPrevious()) {
        echo "Caused by: " . $e->getPrevious()->getMessage() . "\n";
    }
    exit("</pre>");
}

// BASE DE DATOS
try {
    $pdo = Illuminate\Support\Facades\DB::connection()->getPdo();
    echo "✅ BD conectada (" . config('database.default') . ")\n";
} catch (Throwable $e) {
    echo "❌ BD: " . $e->getMessage() . "\n";
}

// TABLA SESSIONS
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
    echo "❌ Sessions: " . $e->getMessage() . "\n";
}

// LIMPIAR CACHE
$cmds = ['view:clear', 'config:clear', 'route:clear', 'cache:clear'];
foreach ($cmds as $cmd) {
    try {
        Illuminate\Support\Facades\Artisan::call($cmd);
        echo "✅ $cmd OK\n";
    } catch (Throwable $e) {
        echo "❌ $cmd: " . $e->getMessage() . "\n";
    }
}

// MIGRACIONES
try {
    Illuminate\Support\Facades\Artisan::call('migrate:status');
    $status = Illuminate\Support\Facades\Artisan::output();
    $pending = substr_count($status, 'Pending');

    echo "\nMigraciones pendientes: $pending\n";
    if ($pending > 0) {
        echo $status . "\n";
    }
} catch (Throwable $e) {
    echo "❌ Migrate status: " . $e->getMessage() . "\n";
}

// TEST RUTA /home
echo "\n--- Test /home ---\n";
try {
    $httpKernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
    $request = Illuminate\Http\Request::create('/home', 'GET');
    $response = $httpKernel->handle($request);

    echo "Status: " . $response->getStatusCode() . "\n";

    if ($response->getStatusCode() < 400) {
        echo "✅ /home funciona (" . strlen($response->getContent()) . " bytes)\n";
    } else {
        echo "❌ Error en /home\n";
    }

} catch (Throwable $e) {
    echo "❌ /home: " . $e->getMessage() . "\n";
}

echo "\n✅ Diagnóstico completo\n";
echo "</pre>";