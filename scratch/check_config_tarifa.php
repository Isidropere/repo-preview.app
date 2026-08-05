<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$config = \App\Models\ConfigTarifaCategoria29::vigente();

if ($config) {
    echo "Active Config ID: {$config->id}\n";
    echo "Monto Registro: {$config->monto_registro}\n";
    echo "Estatus: {$config->estatus}\n";
} else {
    echo "No active config found!\n";
}
