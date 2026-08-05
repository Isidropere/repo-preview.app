<?php
require 'c:/Users/iperez/Desktop/datos/repos/copi/CB.app/vendor/autoload.php';
$app = require_once 'c:/Users/iperez/Desktop/datos/repos/copi/CB.app/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$logs = \DB::table('logs_pagos')->orderBy('id', 'desc')->limit(5)->get();
foreach ($logs as $log) {
    echo "ID: {$log->id} | User: {$log->id_user} | Order: {$log->custom_order_id} | Type: {$log->transaction_type} | Success: {$log->is_success} | Created: {$log->created_at}\n";
    echo "Response: " . substr($log->response_payload, 0, 150) . "...\n\n";
}
