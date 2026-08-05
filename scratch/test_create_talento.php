<?php
require 'c:/Users/iperez/Desktop/datos/repos/copi/CB.app/vendor/autoload.php';
$app = require_once 'c:/Users/iperez/Desktop/datos/repos/copi/CB.app/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = \App\Models\User::find(4);
if (!$user) {
    echo "Usuario 4 no encontrado\n";
    exit;
}

// Asegurarse de que el usuario tiene hoja de vida para que pase la validación
$tieneHoja = \App\Models\HojaVida::where('id_user', $user->id)->exists();
if (!$tieneHoja) {
    echo "Creando Hoja de Vida para usuario 4...\n";
    \App\Models\HojaVida::create([
        'id_user' => $user->id,
        'nombre' => $user->name,
        'profesion' => 'Desarrollador',
        'presentacion' => 'Presentación de prueba',
        'experiencia' => 'Sin experiencia',
        'educacion' => 'Universidad',
        'habilidades' => 'PHP, Dart',
    ]);
}

$request = \Illuminate\Http\Request::create('/api/talentos', 'POST', [
    'item'              => 'Servicio de prueba de carga ' . time(),
    'presentacion'      => 'Descripción larga de prueba para el servicio de talentos.',
    'valor'             => '150.00',
    'condicion'         => 1,
    'tipo_trans'        => 1,
    'id_categoria_item' => 29,
    'cantidad'          => 5,
]);

$request->setUserResolver(fn() => $user);

try {
    $response = (new \App\Http\Controllers\API\ItemApiController)->storeTalento($request);
    echo "Código de Respuesta: " . $response->getStatusCode() . "\n";
    echo "Contenido: " . $response->getContent() . "\n";
} catch (\Illuminate\Validation\ValidationException $e) {
    echo "Error de Validación:\n";
    print_r($e->errors());
} catch (\Exception $e) {
    echo "Excepción: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
