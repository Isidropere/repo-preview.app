<?php
include 'vendor/autoload.php';
$app = include 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$user = \App\Models\User::where('email', 'juliogimenez@gmail.com')->first();
if (!$user) { echo "Usuario no encontrado\n"; exit; }

echo "User ID: " . $user->id . "\n";
echo "Nombre: " . $user->nombres . " " . $user->apellidos . "\n\n";

$dirs = \App\Models\Direcciones::where('id_user', $user->id)->with('municipio')->get();
echo "Total direcciones: " . $dirs->count() . "\n";
foreach ($dirs as $d) {
    echo "  ID: " . $d->id_direccion
       . " | municipio_id: " . $d->id_municipio
       . " | municipio_nombre: " . ($d->municipio->municipio ?? 'N/A')
       . " | predeterminada: " . $d->es_predeterminada . "\n";
}

// Simulate API call /direcciones
echo "\n--- Simulating GET /direcciones response ---\n";
$apiDirs = \App\Models\Direcciones::where('id_user', $user->id)
    ->with(['municipio'])
    ->get();
echo json_encode(['data' => $apiDirs->toArray()], JSON_PRETTY_PRINT) . "\n";
