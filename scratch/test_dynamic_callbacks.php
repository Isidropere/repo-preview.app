<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\Payments\AzulProvider;

echo "Iniciando test de URLs de callback dinámicas en AzulProvider...\n";

$azul = app(AzulProvider::class);

// Test 1: Flujo por defecto (productos)
$resDefault = $azul->generarCamposFormulario(100.00, 'TEST-PROD-01');
$fieldsDefault = $resDefault['fields'];

echo "\n--- Flujo de Producto (Default) ---\n";
echo "ApprovedUrl: " . $fieldsDefault['ApprovedUrl'] . "\n";
echo "DeclinedUrl: " . $fieldsDefault['DeclinedUrl'] . "\n";
echo "CancelUrl: " . $fieldsDefault['CancelUrl'] . "\n";

if ($fieldsDefault['ApprovedUrl'] !== route('pago.redirect.aprobado')) {
    echo "ERROR: ApprovedUrl no coincide con la ruta por defecto.\n";
} else {
    echo "OK: URLs por defecto correctas.\n";
}

// Test 2: Flujo de Talento
$resTalento = $azul->generarCamposFormulario(150.00, 'TEST-TAL-01', [
    'approved_url' => route('talento.pago.aprobado'),
    'declined_url' => route('talento.pago.declinado'),
    'cancel_url'   => route('talento.pago.cancelado'),
]);
$fieldsTalento = $resTalento['fields'];

echo "\n--- Flujo de Registro de Talento ---\n";
echo "ApprovedUrl: " . $fieldsTalento['ApprovedUrl'] . "\n";
echo "DeclinedUrl: " . $fieldsTalento['DeclinedUrl'] . "\n";
echo "CancelUrl: " . $fieldsTalento['CancelUrl'] . "\n";

if ($fieldsTalento['ApprovedUrl'] !== route('talento.pago.aprobado')) {
    echo "ERROR: ApprovedUrl no coincide con la ruta de talento.\n";
} else {
    echo "OK: URLs de talento generadas correctamente.\n";
}

// Test 3: Flujo de Negociación
$resNeg = $azul->generarCamposFormulario(200.00, 'TEST-INT-01', [
    'approved_url' => route('negociaciones.pago.aprobado'),
    'declined_url' => route('negociaciones.pago.declinado'),
    'cancel_url'   => route('negociaciones.pago.cancelado'),
]);
$fieldsNeg = $resNeg['fields'];

echo "\n--- Flujo de Pago de Envío de Negociación ---\n";
echo "ApprovedUrl: " . $fieldsNeg['ApprovedUrl'] . "\n";
echo "DeclinedUrl: " . $fieldsNeg['DeclinedUrl'] . "\n";
echo "CancelUrl: " . $fieldsNeg['CancelUrl'] . "\n";

if ($fieldsNeg['ApprovedUrl'] !== route('negociaciones.pago.aprobado')) {
    echo "ERROR: ApprovedUrl no coincide con la ruta de negociación.\n";
} else {
    echo "OK: URLs de negociación generadas correctamente.\n";
}

echo "\n¡Test completado!\n";
