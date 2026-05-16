<?php

use App\Models\Item;
use App\Models\Negociacion;
use App\Models\User;
use Illuminate\Support\Facades\DB;

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$userId = 12;
$targetUser = User::find($userId);

if (!$targetUser) {
    echo "Error: Usuario con ID $userId no encontrado.\n";
    exit(1);
}

// Según documentación interna: Categoría 29 = Talentos (servicios)
$idCatServicio = 29;

// Buscar items para las pruebas
$otroUser = User::where('id', '!=', $userId)->first();
if (!$otroUser) {
    echo "Error: Necesitas al menos otro usuario en la DB.\n";
    exit(1);
}

echo "Usando otro usuario para el intercambio: ID {$otroUser->id} ({$otroUser->name})\n";

// 1. Producto vs Producto (Cat != 29)
$itemReceptorPP = Item::where('id_categoria_item', '!=', $idCatServicio)->where('id_user', $userId)->first();
$itemEmisorPP   = Item::where('id_categoria_item', '!=', $idCatServicio)->where('id_user', $otroUser->id)->first();

if ($itemReceptorPP && $itemEmisorPP) {
    Negociacion::create([
        'receptor_item_id'   => $itemReceptorPP->id_item,
        'usuario_emisor_id'   => $otroUser->id,
        'usuario_receptor_id' => $userId,
        'mensaje_inicial'     => 'Intercambio TEST: Producto vs Producto',
        'estado'              => 'pendiente',
        'fecha_creacion'      => now(),
        'items_ofrecidos'     => [$itemEmisorPP->id_item],
    ]);
    echo "✅ Creado: Producto vs Producto\n";
} else {
    echo "❌ Falló: Producto vs Producto (faltan items de categoria != 29)\n";
}

// 2. Servicio vs Producto
// Caso: Usuario 12 (Service) ofrece su talento por un Producto ajeno
$itemReceptorSP = Item::where('id_categoria_item', '!=', $idCatServicio)->where('id_user', $otroUser->id)->first();
$itemEmisorSP   = Item::where('id_categoria_item', $idCatServicio)->where('id_user', $userId)->first();

if ($itemReceptorSP && $itemEmisorSP) {
    Negociacion::create([
        'receptor_item_id'   => $itemReceptorSP->id_item,
        'usuario_emisor_id'   => $userId,
        'usuario_receptor_id' => $otroUser->id,
        'mensaje_inicial'     => 'Intercambio TEST: Servicio (mio) vs Producto (tuyo)',
        'estado'              => 'pendiente',
        'fecha_creacion'      => now(),
        'items_ofrecidos'     => [$itemEmisorSP->id_item],
    ]);
    echo "✅ Creado: Servicio vs Producto\n";
} else {
    echo "❌ Falló: Servicio vs Producto (faltan items)\n";
}

// 3. Servicio vs Servicio
$itemReceptorSS = Item::where('id_categoria_item', $idCatServicio)->where('id_user', $userId)->first();
$itemEmisorSS   = Item::where('id_categoria_item', $idCatServicio)->where('id_user', $otroUser->id)->first();

if ($itemReceptorSS && $itemEmisorSS) {
    Negociacion::create([
        'receptor_item_id'   => $itemReceptorSS->id_item,
        'usuario_emisor_id'   => $otroUser->id,
        'usuario_receptor_id' => $userId,
        'mensaje_inicial'     => 'Intercambio TEST: Servicio vs Servicio',
        'estado'              => 'pendiente',
        'fecha_creacion'      => now(),
        'items_ofrecidos'     => [$itemEmisorSS->id_item],
    ]);
    echo "✅ Creado: Servicio vs Servicio\n";
} else {
    echo "❌ Falló: Servicio vs Servicio\n";
}
