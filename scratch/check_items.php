<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$items = \App\Models\Item::where('id_categoria_item', 29)->orderBy('id_item', 'desc')->limit(10)->get();

foreach ($items as $item) {
    echo "ID: {$item->id_item} | Name: {$item->item} | User: {$item->id_user} | Estatus: {$item->estatus} | Date: {$item->fecha}\n";
}
