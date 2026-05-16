<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\TarjetaPago;
use Illuminate\Http\Request;
use App\Http\Requests\ProcesarPagoRequest;

try {
    $buyer = User::find(8);
    auth()->login($buyer);
    
    $tarjeta = TarjetaPago::where('id_user', 8)->first();
    if (!$tarjeta) {
        die("No hay tarjeta.\n");
    }

    $controller = app()->make(\App\Http\Controllers\PagoController::class);
    
    // Crear el request
    $request = ProcesarPagoRequest::create('/carrito/pago', 'POST', [
        'id_tarjeta' => $tarjeta->id_tarjeta,
        'cvv' => '123'
    ]);
    
    $request->setContainer($app);
    $request->setRedirector($app->make(\Illuminate\Routing\Redirector::class));
    $request->validateResolved();

    $response = $controller->procesar($request);
    
    echo "Success!\n";
    print_r($response);

} catch (\Throwable $e) {
    echo "EXCEPTION CAPTURADA:\n";
    echo $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo $e->getTraceAsString();
}
