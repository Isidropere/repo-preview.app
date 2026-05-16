<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Item;
use App\Models\Carrito;
use App\Models\ItemIntencionCompra;


require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);


function simulateRequest($kernel, $method, $uri, $parameters = [], $cookies = [], $user = null) {
    $server = ['SERVER_NAME' => 'localhost'];
    $request = Request::create($uri, $method, $parameters, $cookies, [], $server);
    if ($user) {
        $request->setUserResolver(function () use ($user) {
            return $user;
        });
        Auth::login($user);
    }
    
    // Para que Laravel procese la sesión correctamente en el script
    $request->setLaravelSession(app('session')->driver());

    
    $response = $kernel->handle($request);
    $kernel->terminate($request, $response);
    return $response;
}

echo "=== INICIANDO PRUEBA DE FLUJO COMPLETO ===\n\n";

$provider = User::find(12); // Julio
$buyer = User::find(8);    // Juan

// 1. Verificar el Talento del Proveedor
$talento = Item::where('id_user', $provider->id)->where('id_categoria_item', 29)->first();
if (!$talento) {
    echo "❌ El proveedor no tiene talentos publicados.\n";
    exit;
}
echo "✅ Talento encontrado: {$talento->item} (ID: {$talento->id_item})\n";

// Asegurar que el carrito de Juan esté limpio para la prueba
$carritoJuan = Carrito::firstOrCreate(['id_user' => $buyer->id]);
ItemIntencionCompra::where('id_carrito', $carritoJuan->id_carrito)->delete();


// 2. Comprador (Juan) añade el talento al carrito
echo "\n--- Comprador (Juan) añade al carrito ---\n";
Auth::login($buyer);
// Usar el servicio directamente para mayor facilidad en el script
try {
    $carritoService = app()->make(\App\Services\CarritoService::class);
    $resultado = $carritoService->agregarItem($buyer->id, $talento->id_item, 1);
    echo "Respuesta agregar al carrito: " . $resultado['message'] . "\n";
} catch (\Exception $e) {
    echo "Error agregando al carrito: " . $e->getMessage() . "\n";
}

$cartItem = ItemIntencionCompra::where('id_carrito', $carritoJuan->id_carrito)->where('id_item', $talento->id_item)->first();

if ($cartItem) {
    echo "✅ Talento añadido al carrito correctamente. (Estado: {$cartItem->estado})\n";
} else {
    echo "❌ Falló al añadir al carrito.\n";
    exit;
}

// 3. Comprador solicita el servicio
echo "\n--- Comprador solicita el servicio ---\n";
try {
    // Simulamos la solicitud (puede ser en CheckoutController o CarritoController)
    // El flujo de talentos usa un endpoint específico para solicitar o va por checkout?
    // Revisemos el estado. Normalmente el item del carrito de talento queda en estado "pendiente" automáticamente o hay que solicitarlo.
    echo "Estado actual del item en carrito: {$cartItem->estado}\n";
    
    // Si la lógica requiere una solicitud explícita, deberíamos invocarla.
    // Vamos a buscar la ruta que procesa la solicitud de talento.
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
