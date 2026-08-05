<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$compra = \App\Models\PagoCompra::with(['pagoItems', 'trazabilidad'])
    ->where('id_pago_compra', '21496b71-33af-4212-b063-12bdabdac501')
    ->first();

if ($compra) {
    echo "ID Compra: {$compra->id_pago_compra}\n";
    echo "Estatus: {$compra->estatus}\n";
    echo "Total: {$compra->total}\n";
    echo "Impuestos: {$compra->impuestos}\n";
    echo "Costo Envio: {$compra->costo_envio}\n";
    echo "Pago Items:\n";
    foreach ($compra->pagoItems as $item) {
        echo "  - Nombre: {$item->nombre_item} | Cantidad: {$item->cantidad} | Precio: {$item->precio_unitario} | Subtotal: {$item->subtotal}\n";
    }
} else {
    echo "Compra no encontrada.\n";
}
