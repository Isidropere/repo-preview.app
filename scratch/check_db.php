<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$solicitudes = App\Models\SolicitudServicio::where('id_comprador', 8)->get();

echo "Solicitudes de Juan:\n";
print_r($solicitudes->toArray());
