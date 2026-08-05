<?php
include 'vendor/autoload.php';
$app = include 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

foreach ([236, 237] as $id) {
    $item = \App\Models\Item::find($id);
    if ($item) {
        echo "ID: $id - Item: {$item->item} - Estatus: {$item->estatus}\n";
    } else {
        echo "ID: $id not found\n";
    }
}
