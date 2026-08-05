<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$cats = \App\Models\CategoriaItem::all();

foreach ($cats as $c) {
    echo "ID: {$c->id_categoria_item} | Name: {$c->categoria} | Slug: {$c->slug}\n";
}
