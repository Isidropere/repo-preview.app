<?php
require 'c:/Users/iperez/Desktop/datos/repos/copi/CB.app/vendor/autoload.php';
$app = require_once 'c:/Users/iperez/Desktop/datos/repos/copi/CB.app/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$item = \App\Models\Item::find(226);
if (!$item) {
    echo "Item 226 no encontrado\n";
    exit;
}

echo "Intentando actualizar item estatus a 1...\n";
$start = microtime(true);
$item->update(['estatus' => 1]);
echo "Actualizado en: " . (microtime(true) - $start) . " segundos\n";
