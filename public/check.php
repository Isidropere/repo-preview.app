<?php
echo "<pre>\n";

try {
    require __DIR__ . '/../vendor/autoload.php';
    $app = require __DIR__ . '/../bootstrap/app.php';
    $kernel = $app->make('Illuminate\Contracts\Console\Kernel');
    $kernel->bootstrap();

    // Buscar el error real en el log de hoy
    $logFile = storage_path('logs/cabialoErrores-' . date('Y-m-d') . '.log');
    if (!file_exists($logFile)) {
        $logFile = storage_path('logs/laravel.log');
    }

    if (file_exists($logFile)) {
        $content = file_get_contents($logFile);
        // Buscar los últimos errores ERROR (no INFO)
        preg_match_all('/\[\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\] \w+\.ERROR: (.+?)(?=\[\d{4}-\d{2}-\d{2}|\z)/s', $content, $matches);
        
        if (!empty($matches[1])) {
            $lastErrors = array_slice($matches[1], -3); // últimos 3 errores
            echo "=== ÚLTIMOS ERRORES ===\n\n";
            foreach ($lastErrors as $i => $err) {
                // Solo mostrar las primeras 5 líneas de cada error
                $lines = explode("\n", $err);
                $short = array_slice($lines, 0, 5);
                echo "--- Error " . ($i + 1) . " ---\n";
                echo implode("\n", $short) . "\n\n";
            }
        } else {
            echo "No se encontraron errores ERROR en el log\n";
            // Mostrar las últimas 30 líneas
            $lines = explode("\n", $content);
            $last = array_slice($lines, -30);
            echo implode("\n", $last);
        }
    } else {
        echo "No se encontró archivo de log\n";
        echo "Buscado: " . $logFile . "\n";
        
        // Listar archivos de log disponibles
        $logDir = storage_path('logs');
        echo "\nArchivos en logs/:\n";
        foreach (glob($logDir . '/*') as $f) {
            echo "  " . basename($f) . " (" . filesize($f) . " bytes)\n";
        }
    }

    // También intentar capturar el error directamente
    echo "\n=== TEST DIRECTO ===\n";
    try {
        $request = Illuminate\Http\Request::create('/home', 'GET');
        $httpKernel = $app->make('Illuminate\Contracts\Http\Kernel');
        $response = $httpKernel->handle($request);
        echo "Status: " . $response->getStatusCode() . "\n";
        if ($response->getStatusCode() >= 400) {
            // Buscar exception en el response
            $c = $response->getContent();
            if (preg_match('/exception_message.*?>(.*?)</s', $c, $m)) echo "Msg: " . strip_tags($m[1]) . "\n";
            if (preg_match('/exception_class.*?>(.*?)</s', $c, $m)) echo "Class: " . strip_tags($m[1]) . "\n";
        }
    } catch (Throwable $e) {
        echo "❌ " . get_class($e) . ": " . $e->getMessage() . "\n";
        echo "File: " . basename($e->getFile()) . ":" . $e->getLine() . "\n";
    }

} catch (Throwable $e) {
    echo "❌ " . $e->getMessage() . "\n";
    echo "File: " . basename($e->getFile()) . ":" . $e->getLine() . "\n";
}

echo "</pre>";
