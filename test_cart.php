<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

Auth::loginUsingId(76);
$response = app(App\Http\Controllers\NotificationController::class)->listarTodasApi(request());
echo $response->getContent();
