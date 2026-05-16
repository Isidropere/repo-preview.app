<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\TarjetaPago;

$tarjeta = new TarjetaPago();
$tarjeta->id_user = 8;
$tarjeta->nombre_titular = 'Juan Guzman (Test)';
$tarjeta->no_tarjeta = '4111111111111111'; // Se encriptará automáticamente
$tarjeta->last4 = '1111';
$tarjeta->mes_expiracion = '12';
$tarjeta->setAttribute(TarjetaPago::COL_ANIO, '2030');
$tarjeta->tipo_tarjeta = 'credito';
$tarjeta->banco_tarjeta = 'Banco Test';
$tarjeta->usar_esta_tarjeta = 1;
$tarjeta->estatus = 1;
$tarjeta->save();

echo "Tarjeta mock creada para Juan con ID: {$tarjeta->id_tarjeta}\n";
