<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    $providers = DB::table('oauth_providers')->get();
    if ($providers->isEmpty()) {
        echo "The oauth_providers table is empty.\n";
    } else {
        foreach ($providers as $provider) {
            echo "ID: {$provider->id} | Provider: {$provider->provider} | Client ID: {$provider->client_id} | Active: {$provider->activo} | Redirect URI: {$provider->redirect_uri}\n";
        }
    }
} catch (\Exception $e) {
    echo "Error querying oauth_providers table: " . $e->getMessage() . "\n";
}
