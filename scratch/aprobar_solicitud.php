<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Illuminate\Http\Request;

try {
    $provider = User::find(12); // Julio
    auth()->login($provider);
    
    $controller = app()->make(\App\Http\Controllers\SolicitudServicioController::class);
    
    // El método es aprobarJson($id)
    $response = $controller->aprobarJson(9);
    
    echo "Respuesta Aprobar:\n";
    echo json_encode($response->getData(), JSON_PRETTY_PRINT);
    
} catch (\Throwable $e) {
    echo "EXCEPTION CAPTURADA:\n";
    echo $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
