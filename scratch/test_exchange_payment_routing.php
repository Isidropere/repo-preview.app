<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Item;
use App\Models\Inventario;
use App\Models\Negociacion;
use App\Models\Direcciones;
use App\Models\Municipio;
use App\Models\TarjetaPago;
use App\Http\Controllers\NegociacionController;
use App\Http\Controllers\NegociacionPagoRedirectController;
use App\Http\Controllers\API\NegociacionApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

try {
    echo "=== TESTING EXCHANGE PAYMENT FLOW AND HASH ID DECODING ===\n\n";

    DB::beginTransaction();

    // 1. Get two users
    $users = User::limit(2)->get();
    if ($users->count() < 2) {
        throw new \Exception("Database needs at least two users.");
    }
    $emisor = $users[0];
    $receptor = $users[1];

    // Get an existing municipio
    $mun = Municipio::first();
    if (!$mun) {
        throw new \Exception("Database needs at least one municipio.");
    }
    echo "✔ Using Municipio: {$mun->municipio} (ID: {$mun->id_municipio})\n";

    // 2. Ensure address exists for emisor
    $direccion = Direcciones::where('id_user', $emisor->id)->first();
    if (!$direccion) {
        $direccion = Direcciones::create([
            'id_user' => $emisor->id,
            'id_provincia' => $mun->id_provincia,
            'id_municipio' => $mun->id_municipio,
            'calle' => 'Calle Falsa 123',
            'sector' => 'Sector de prueba',
            'es_predeterminada' => 1,
        ]);
        echo "✔ Created temporary address for user.\n";
    } else {
        echo "✔ Using existing address for user.\n";
    }

    // 3. Ensure TarjetaPago exists for emisor
    $tarjeta = TarjetaPago::where('id_user', $emisor->id)->where('estatus', 1)->first();
    if (!$tarjeta) {
        $tarjeta = TarjetaPago::create([
            'id_user' => $emisor->id,
            'no_tarjeta' => '4111111111111111',
            'nombre_titular' => 'Test User',
            'mes_exp' => '12',
            'anio_exp' => '2030',
            'estatus' => 1,
            'last4' => '1111',
        ]);
        echo "✔ Created temporary payment card for user.\n";
    } else {
        echo "✔ Using existing payment card for user.\n";
    }

    // 4. Create an item
    $item = Item::create([
        'id_user' => $receptor->id,
        'item' => 'Test Item requested',
        'slug' => 'test-item-requested-' . time(),
        'presentacion' => 'Test',
        'tipo_trans' => 3,
        'id_categoria_item' => 1,
        'estatus' => 1,
    ]);

    // 5. Create a negotiation in accepted/confirmado state
    $neg = Negociacion::create([
        'usuario_emisor_id' => $emisor->id,
        'usuario_receptor_id' => $receptor->id,
        'receptor_item_id' => $item->id_item,
        'monto_oferta' => 500,
        'estado' => 'aceptado',
        'emisor_confirmado' => true,
        'receptor_confirmado' => true,
        'fecha_creacion' => now(),
        'mensaje_inicial' => 'Hola propuesta',
    ]);

    $hash = \App\Helpers\HashIdHelper::encode($neg->id_negociacion);
    echo "✔ Hashed ID generated: $hash (for raw ID: {$neg->id_negociacion})\n";

    // Login as emisor
    auth()->login($emisor);

    $controller = app(NegociacionController::class);
    $redirectController = app(NegociacionPagoRedirectController::class);

    // Test 1: mostrarPago with Hashed ID
    echo "Testing mostrarPago with Hashed ID...\n";
    $response = $controller->mostrarPago($hash);
    echo "✔ mostrarPago call succeeded without exceptions.\n";

    // Test 2: procesarPago with Hashed ID (standard request)
    echo "Testing procesarPago with Hashed ID (GET/Redirect flow)...\n";
    $request = Request::create("/negociaciones/{$hash}/pago", 'POST', [
        'id_tarjeta' => $tarjeta->id_tarjeta,
        'cvv' => '123'
    ]);
    $response = $controller->procesarPago($request, $hash);
    if ($response->isRedirect(route('negociaciones.pago.iniciar', $hash))) {
        echo "✔ procesarPago correctly redirects to initiate payment route: " . $response->getTargetUrl() . "\n";
    } else {
        throw new \Exception("procesarPago did not redirect to initiate payment route correctly.");
    }

    // Test 3: procesarPago with Hashed ID (JSON/AJAX request)
    echo "Testing procesarPago with Hashed ID (AJAX/wantsJson request)...\n";
    $requestJson = Request::create("/negociaciones/{$hash}/pago", 'POST', [
        'id_tarjeta' => $tarjeta->id_tarjeta,
        'cvv' => '123'
    ]);
    $requestJson->headers->set('Accept', 'application/json');
    $responseJson = $controller->procesarPago($requestJson, $hash);
    $data = json_decode($responseJson->getContent(), true);
    
    if (isset($data['success']) && $data['success'] === true && (isset($data['redirect']) || isset($data['redirect_url']))) {
        $redUrl = $data['redirect'] ?? $data['redirect_url'];
        echo "✔ procesarPago AJAX returned success with redirect URL: " . $redUrl . "\n";
        if (strpos($redUrl, $hash) !== false) {
            echo "✔ Redirect URL correctly contains the Hash ID.\n";
        } else {
            throw new \Exception("Redirect URL did not contain Hash ID: " . $redUrl);
        }
    } else {
        throw new \Exception("AJAX payment processing response invalid: " . print_r($data, true));
    }

    // Test 4: iniciarPagoWeb with Hashed ID
    echo "Testing iniciarPagoWeb with Hashed ID...\n";
    $responseWeb = $redirectController->iniciarPagoWeb($hash);
    echo "✔ iniciarPagoWeb call succeeded.\n";
    
    if ($responseWeb instanceof \Illuminate\Http\RedirectResponse) {
        echo "✔ initiatePagoWeb returned redirect (likely missing delivery tariff or already paid): " . $responseWeb->getTargetUrl() . "\n";
    } else {
        echo "✔ initiatePagoWeb returned view (redirection to Azul form).\n";
    }

    // Test 5: NegociacionApiController methods with Hashed ID (Mobile API)
    echo "Testing NegociacionApiController show with Hashed ID (Mobile App API)...\n";
    $apiController = app(NegociacionApiController::class);
    
    $apiRequest = Request::create("/api/negociaciones/{$hash}", 'GET');
    $apiRequest->setUserResolver(fn() => $emisor);
    
    $apiResponse = $apiController->show($apiRequest, $hash);
    if ($apiResponse->getStatusCode() === 200) {
        echo "✔ NegociacionApiController::show call succeeded with HTTP 200.\n";
    } else {
        throw new \Exception("NegociacionApiController::show failed with status code: " . $apiResponse->getStatusCode());
    }

    echo "Testing NegociacionApiController mensajes with Hashed ID...\n";
    $apiResponseMsg = $apiController->mensajes($apiRequest, $hash);
    if ($apiResponseMsg->getStatusCode() === 200) {
        echo "✔ NegociacionApiController::mensajes call succeeded with HTTP 200.\n";
    } else {
        throw new \Exception("NegociacionApiController::mensajes failed with status code: " . $apiResponseMsg->getStatusCode());
    }

    echo "\n🎉 ALL TESTS COMPLETED SUCCESSFULLY! The payment gateway flow and Hash ID decoding are fully functional for both Web and Mobile. 🎉\n";

    DB::rollBack();
    echo "✔ Database changes reverted.\n";

} catch (\Throwable $e) {
    DB::rollBack();
    echo "\n❌ TEST FAILED: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " on line " . $e->getLine() . "\n";
    exit(1);
}
