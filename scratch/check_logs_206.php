<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$logs = \DB::table('logs_pagos')
    ->where('custom_order_id', 'like', '%TAL-206%')
    ->get();

if ($logs->isEmpty()) {
    echo "No payment logs found for TAL-206.\n";
} else {
    foreach ($logs as $l) {
        echo "ID: {$l->id} | User: {$l->id_user} | Order: {$l->custom_order_id} | Provider: {$l->provider} | Type: {$l->transaction_type} | Success: {$l->is_success}\n";
    }
}
