<?php
echo "<pre>\n";

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

// Verificar que las variables del controller llegan bien
echo "=== Test misIntercambios() directo ===\n";
try {
    // Login como usuario 1 (o el que exista)
    $user = App\Models\User::first();
    if (!$user) { echo "No hay usuarios\n"; exit; }
    Illuminate\Support\Facades\Auth::login($user);
    echo "User: " . $user->id . " - " . $user->nombres . "\n";

    $userId = $user->id;

    // Simular lo que hace el controller
    $comoEmisor = App\Models\Negociacion::where('usuario_emisor_id', $userId)
        ->whereNotIn('estado', ['cancelado'])
        ->with(['item.imagenes', 'item.categoria', 'usuarioReceptor', 'item.inventarios'])
        ->orderByDesc('id_negociacion')
        ->get();
    echo "comoEmisor: " . $comoEmisor->count() . " registros\n";

    $comoReceptor = App\Models\Negociacion::where('usuario_receptor_id', $userId)
        ->whereNotIn('estado', ['cancelado'])
        ->with(['item.imagenes', 'item.categoria', 'usuario', 'item.inventarios'])
        ->orderByDesc('id_negociacion')
        ->get();
    echo "comoReceptor: " . $comoReceptor->count() . " registros\n";

    $tarjetas = App\Models\TarjetaPago::where('id_user', $userId)->where('estatus', 1)->get();
    echo "tarjetas: " . $tarjetas->count() . "\n";

    // Mensajes predefinidos
    $mensajesPredefinidos = App\Models\PredefinedMessage::where('activo', true)->get();
    echo "mensajesPredefinidos: " . $mensajesPredefinidos->count() . "\n";

    $accionesPredefinidas = App\Models\PredefinedMessage::where('activo', true)->select('tipo')->distinct()->pluck('tipo');
    echo "accionesPredefinidas: " . $accionesPredefinidas->count() . " tipos\n";

    // Delivery
    $direccion = App\Models\Direcciones::where('id_user', $userId)->with('municipio')->first();
    echo "direccion: " . ($direccion ? $direccion->municipio->municipio ?? 'sin municipio' : 'SIN DIRECCION') . "\n";

    $costoEnvioPorNeg = ['_municipio' => ''];
    if ($direccion && $direccion->municipio) {
        $deliveryService = app(App\Services\DeliveryService::class);
        $municipio = $direccion->municipio->municipio ?? '';
        $costoEnvioPorNeg['_municipio'] = $municipio;
        $todasNegs = $comoEmisor->merge($comoReceptor);
        foreach ($todasNegs as $neg) {
            if ($neg->item) {
                $resultado = $deliveryService->calcular($municipio, 'persona', 0);
                $costoEnvioPorNeg[$neg->id_negociacion] = $resultado['success'] ? ($resultado['costo_envio_total'] ?? 0) : 0;
            } else {
                $costoEnvioPorNeg[$neg->id_negociacion] = 0;
            }
        }
    }
    echo "costoEnvioPorNeg: " . json_encode($costoEnvioPorNeg) . "\n";

    // Intentar renderizar la vista
    echo "\n=== Render vista ===\n";
    $html = view('negociaciones.mis-intercambios', compact(
        'comoEmisor', 'comoReceptor', 'tarjetas', 'costoEnvioPorNeg', 'mensajesPredefinidos', 'accionesPredefinidas'
    ))->render();
    echo "OK - " . strlen($html) . " bytes\n";

} catch (Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "FILE: " . basename($e->getFile()) . ":" . $e->getLine() . "\n";
    $prev = $e->getPrevious();
    while ($prev) {
        echo "CAUSED BY: " . $prev->getMessage() . "\n";
        echo "  AT: " . basename($prev->getFile()) . ":" . $prev->getLine() . "\n";
        $prev = $prev->getPrevious();
    }
}

echo "\n✅ Fin\n</pre>";
