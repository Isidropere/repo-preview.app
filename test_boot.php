<?php
echo "Starting boot test...\n";
require __DIR__.'/vendor/autoload.php';
echo "Autoload required\n";
$app = require_once __DIR__.'/bootstrap/app.php';
echo "Bootstrap app required\n";
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
echo "Kernel made\n";
$kernel->bootstrap();
echo "Bootstrapped!\n";
