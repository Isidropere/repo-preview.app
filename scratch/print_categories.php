<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

foreach(\App\Models\CategoriaItem::all() as $c) {
    echo $c->id_categoria_item . ': ' . $c->categoria . "\n";
}
