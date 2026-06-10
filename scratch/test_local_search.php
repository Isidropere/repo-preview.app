<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Http\Request;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\API\ItemApiController;

echo "Iniciando test de búsqueda con fallback...\n";

// Simular la petición: GET /items/search_header?q=tel
$request = Request::create('/items/search_header', 'GET', ['q' => 'tel']);

try {
    $controller = app(ItemController::class);
    $response = $controller->search_header($request);
    echo "¡ÉXITO! La ruta de búsqueda local cargó correctamente (se aplicó el fallback de DB).\n";
    echo "Tipo de respuesta: " . get_class($response) . "\n";
} catch (\Throwable $e) {
    echo "ERROR AL CARGAR BÚSQUEDA LOCAL:\n";
    echo $e->getMessage() . "\n";
}

echo "\n------------------------------------------\n";
echo "Probando API index con query 'tel'...\n";
$apiRequestIndex = Request::create('/api/items', 'GET', ['q' => 'tel']);
try {
    $apiController = app(ItemApiController::class);
    $response = $apiController->index($apiRequestIndex);
    echo "¡ÉXITO! API index cargó correctamente (fallback DB aplicado).\n";
    echo "Tipo de respuesta: " . get_class($response) . "\n";
} catch (\Throwable $e) {
    echo "ERROR EN API INDEX:\n";
    echo $e->getMessage() . "\n";
}

echo "\n------------------------------------------\n";
echo "Probando API buscar con query 'tel'...\n";
$apiRequestBuscar = Request::create('/api/items/buscar', 'GET', ['q' => 'tel']);
try {
    $apiController = app(ItemApiController::class);
    $response = $apiController->buscar($apiRequestBuscar);
    echo "¡ÉXITO! API buscar cargó correctamente (fallback DB aplicado).\n";
    echo "Tipo de respuesta: " . get_class($response) . "\n";
} catch (\Throwable $e) {
    echo "ERROR EN API BUSCAR:\n";
    echo $e->getMessage() . "\n";
}
