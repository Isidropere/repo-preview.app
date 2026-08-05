<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$logs = \DB::table('logs_pagos')
    ->whereIn('id_user', [75, 76])
    ->get();

if ($logs->isEmpty()) {
    echo "No payment logs found for users 75/76.\n";
} else {
    foreach ($logs as $l) {
        echo "ID: {$l->id} | User: {$l->id_user} | Order: {$l->custom_order_id} | Provider: {$l->provider} | Type: {$l->transaction_type} | Success: {$l->is_success} | Created: {$l->created_at}\n";
    }
}
