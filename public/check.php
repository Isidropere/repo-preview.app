<?php
echo "<pre>\n";

try {
    require __DIR__ . '/../vendor/autoload.php';
    $app = require __DIR__ . '/../bootstrap/app.php';
    $kernel = $app->make('Illuminate\Contracts\Http\Kernel');

    // Simular request a /home
    $request = Illuminate\Http\Request::create('/home', 'GET');
    $response = $kernel->handle($request);

    echo "Status: " . $response->getStatusCode() . "\n";

    if ($response->getStatusCode() >= 400) {
        $content = $response->getContent();
        // Extraer el mensaje de error
        if (preg_match('/exception-message[^>]*>(.*?)<\//s', $content, $m)) {
            echo "Error: " . strip_tags($m[1]) . "\n";
        }
        if (preg_match('/class="trace-file-path"[^>]*>(.*?)<\//s', $content, $m)) {
            echo "File: " . strip_tags($m[1]) . "\n";
        }
        // Buscar en el contenido cualquier mensaje de error
        if (preg_match('/<title>(.*?)<\/title>/s', $content, $m)) {
            echo "Title: " . trim(strip_tags($m[1])) . "\n";
        }
        // Si es producción (no debug), buscar el error en el log
        $logFile = storage_path('logs/laravel.log');
        if (!file_exists($logFile)) {
            $logFile = storage_path('logs/cabialoErrores-' . date('Y-m-d') . '.log');
        }
        if (file_exists($logFile)) {
            $lines = array_slice(file($logFile), -20);
            echo "\n--- Últimas líneas del log ---\n";
            echo implode('', $lines);
        }
    } else {
        echo "✅ /home cargó correctamente (" . strlen($response->getContent()) . " bytes)\n";
    }

} catch (Throwable $e) {
    echo "❌ " . $e->getMessage() . "\n";
    echo "File: " . basename($e->getFile()) . ":" . $e->getLine() . "\n";
    $prev = $e->getPrevious();
    while ($prev) {
        echo "Caused by: " . $prev->getMessage() . "\n";
        echo "  At: " . basename($prev->getFile()) . ":" . $prev->getLine() . "\n";
        $prev = $prev->getPrevious();
    }
}

echo "</pre>";
