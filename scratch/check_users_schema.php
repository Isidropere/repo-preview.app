<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

try {
    $columns = DB::select("SHOW COLUMNS FROM users");
    foreach ($columns as $column) {
        echo "Field: {$column->Field} | Type: {$column->Type} | Null: {$column->Null} | Key: {$column->Key} | Default: {$column->Default}\n";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
