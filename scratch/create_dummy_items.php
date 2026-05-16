<?php

use App\Models\Item;
use App\Models\Negociacion;
use App\Models\User;
use Illuminate\Support\Facades\DB;

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$userId = 12;
$idCatServicio = 29;
$otroUserId = 3; // El que salió en el test anterior

// Crear items de prueba si no existen
function crearItemTest($uid, $cat, $nombre) {
    return Item::create([
        'item'              => $nombre,
        'id_categoria_item' => $cat,
        'id_user'           => $uid,
        'estatus'           => 1,
        'tipo_trans'        => 2, // Intercambio
        'valor'             => 1000,
        'fecha'             => now(),
        'presentacion'      => 'Prueba automatizada'
    ]);
}

echo "Creando items de servicio para pruebas...\n";
$serv12 = crearItemTest($userId, $idCatServicio, 'Talento de Prueba (User 12)');
$serv3  = crearItemTest($otroUserId, $idCatServicio, 'Talento de Prueba (User 3)');
$prod3  = crearItemTest($otroUserId, 1, 'Producto de Prueba (User 3)');

// 2. Servicio vs Producto
Negociacion::create([
    'receptor_item_id'   => $prod3->id_item,
    'usuario_emisor_id'   => $userId,
    'usuario_receptor_id' => $otroUserId,
    'mensaje_inicial'     => 'Intercambio TEST: Servicio (mio) vs Producto (tuyo)',
    'estado'              => 'pendiente',
    'fecha_creacion'      => now(),
    'items_ofrecidos'     => [$serv12->id_item],
]);
echo "✅ Creado: Servicio vs Producto\n";

// 3. Servicio vs Servicio
Negociacion::create([
    'receptor_item_id'   => $serv12->id_item,
    'usuario_emisor_id'   => $otroUserId,
    'usuario_receptor_id' => $userId,
    'mensaje_inicial'     => 'Intercambio TEST: Servicio vs Servicio',
    'estado'              => 'pendiente',
    'fecha_creacion'      => now(),
    'items_ofrecidos'     => [$serv3->id_item],
]);
echo "✅ Creado: Servicio vs Servicio\n";
