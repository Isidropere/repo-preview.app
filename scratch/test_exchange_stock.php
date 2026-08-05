<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Negociacion;
use App\Models\Item;
use App\Services\NegociacionService;
use App\Services\AdminComprasService;
use Illuminate\Support\Facades\DB;

DB::beginTransaction();

try {
    echo "=== Iniciando prueba de stock de intercambios ===\n";

    // 1. Obtener o crear una negociación de prueba
    $neg = Negociacion::first();
    if (!$neg) {
        // Si no hay negociaciones, crear una dummy
        echo "No hay negociaciones, creando una dummy...\n";
        
        // Buscar dos usuarios distintos
        $user1 = \App\Models\User::first();
        $user2 = \App\Models\User::skip(1)->first();
        
        if (!$user1 || !$user2) {
            throw new Exception("Se necesitan al menos 2 usuarios en la DB.");
        }
        
        // Crear un item para el receptor (user2)
        $itemReceptor = Item::create([
            'item' => 'Item Receptor Test',
            'id_user' => $user2->id,
            'id_categoria_item' => 1,
            'estatus' => 1,
            'valor' => 100
        ]);
        
        // Crear inventario para el item
        DB::table('inventarios')->insert([
            'id_item' => $itemReceptor->id_item,
            'cantidad' => 5
        ]);
        
        // Crear un item para el emisor (user1)
        $itemEmisor = Item::create([
            'item' => 'Item Emisor Test',
            'id_user' => $user1->id,
            'id_categoria_item' => 1,
            'estatus' => 1,
            'valor' => 100
        ]);
        
        DB::table('inventarios')->insert([
            'id_item' => $itemEmisor->id_item,
            'cantidad' => 10
        ]);
        
        $neg = Negociacion::create([
            'receptor_item_id' => $itemReceptor->id_item,
            'usuario_emisor_id' => $user1->id,
            'usuario_receptor_id' => $user2->id,
            'mensaje_inicial' => 'Hola',
            'items_ofrecidos' => [$itemEmisor->id_item],
            'estado' => 'Inicial'
        ]);
    } else {
        echo "Usando negociación existente ID: {$neg->id_negociacion}\n";
        // Asegurarse de que el estado sea Inicial para la prueba
        $neg->update(['estado' => 'Inicial', 'emisor_confirmado' => false, 'receptor_confirmado' => false]);
        
        // Verificar/inicializar inventarios
        $receptorItem = Item::with('inventarios')->find($neg->receptor_item_id);
        if (!$receptorItem->inventarios) {
            DB::table('inventarios')->insert(['id_item' => $receptorItem->id_item, 'cantidad' => 5]);
        } else {
            $receptorItem->inventarios->update(['cantidad' => 5]);
        }
        
        $negService = app(NegociacionService::class);
        $emisorItemsIds = $negService->obtenerItemsOfrecidosIds($neg);
        if (empty($emisorItemsIds)) {
            // Ofrecer un ítem temporal para probar la doble reserva
            $tempItem = Item::where('id_user', $neg->usuario_emisor_id)->first();
            if ($tempItem) {
                $neg->update(['items_ofrecidos' => [$tempItem->id_item]]);
                if (!$tempItem->inventarios) {
                    DB::table('inventarios')->insert(['id_item' => $tempItem->id_item, 'cantidad' => 10]);
                } else {
                    $tempItem->inventarios->update(['cantidad' => 10]);
                }
            }
        } else {
            foreach ($emisorItemsIds as $eid) {
                $eitem = Item::with('inventarios')->find($eid);
                if ($eitem) {
                    if (!$eitem->inventarios) {
                        DB::table('inventarios')->insert(['id_item' => $eid, 'cantidad' => 10]);
                    } else {
                        $eitem->inventarios->update(['cantidad' => 10]);
                    }
                }
            }
        }
        $neg = $neg->fresh();
    }

    $negService = app(NegociacionService::class);
    $adminService = app(AdminComprasService::class);

    // 2. Leer stocks iniciales
    $receptorItem = Item::with('inventarios')->find($neg->receptor_item_id);
    $emisorItemsIds = $negService->obtenerItemsOfrecidosIds($neg);
    
    echo "Artículos de la negociación:\n";
    echo "  - Receptor (solicitado): ID {$receptorItem->id_item} (Stock: {$receptorItem->inventarios->cantidad})\n";
    foreach ($emisorItemsIds as $eid) {
        $eitem = Item::with('inventarios')->find($eid);
        echo "  - Emisor (ofrecido): ID {$eid} (Stock: " . ($eitem->inventarios->cantidad ?? 'N/A') . ")\n";
    }

    $stockReceptorInicial = $receptorItem->inventarios->cantidad;
    $stocksEmisorIniciales = [];
    foreach ($emisorItemsIds as $eid) {
        $eitem = Item::with('inventarios')->find($eid);
        $stocksEmisorIniciales[$eid] = $eitem->inventarios->cantidad ?? 0;
    }

    // 3. Simular Aceptación (descontar stock de ambos lados)
    echo "\n--> Simulando ACEPTACIÓN por el Receptor...\n";
    $res = $negService->aceptar($neg->usuario_receptor_id, $neg->id_negociacion);
    if (!$res['success']) {
        throw new Exception("Error al aceptar: " . $res['message']);
    }
    
    // Recargar y verificar stock
    $neg = $neg->fresh();
    $receptorItemReloaded = Item::with('inventarios')->find($neg->receptor_item_id);
    echo "  - Nuevo stock Receptor: {$receptorItemReloaded->inventarios->cantidad} (Esperado: " . ($stockReceptorInicial - 1) . ")\n";
    if ($receptorItemReloaded->inventarios->cantidad !== $stockReceptorInicial - 1) {
        throw new Exception("El stock del receptor no se decrementó correctamente.");
    }

    foreach ($emisorItemsIds as $eid) {
        $eitemReloaded = Item::with('inventarios')->find($eid);
        $stockEsperado = $stocksEmisorIniciales[$eid] - 1;
        echo "  - Nuevo stock Emisor (ID {$eid}): {$eitemReloaded->inventarios->cantidad} (Esperado: {$stockEsperado})\n";
        if ($eitemReloaded->inventarios->cantidad !== $stockEsperado) {
            throw new Exception("El stock del emisor (ID {$eid}) no se decrementó correctamente.");
        }
    }
    
    echo "  - Estado de la negociación: {$neg->estado} (Esperado: aceptado)\n";
    if ($neg->estado !== 'aceptado') {
        throw new Exception("El estado no cambió a aceptado.");
    }

    // 4. Simular cancelación administrativa (restaurar stock de ambos lados)
    echo "\n--> Simulando CANCELACIÓN ADMINISTRATIVA...\n";
    $resCancel = $adminService->actualizarEstadoIntercambio($neg->id_negociacion, 'cancelado');
    if (!$resCancel['success']) {
        throw new Exception("Error al cancelar administrativamente: " . $resCancel['message']);
    }

    // Recargar y verificar stock restaurado
    $neg = $neg->fresh();
    $receptorItemRestored = Item::with('inventarios')->find($neg->receptor_item_id);
    echo "  - Stock Restaurado Receptor: {$receptorItemRestored->inventarios->cantidad} (Esperado: {$stockReceptorInicial})\n";
    if ($receptorItemRestored->inventarios->cantidad !== $stockReceptorInicial) {
        throw new Exception("El stock del receptor no se restauró correctamente.");
    }

    foreach ($emisorItemsIds as $eid) {
        $eitemRestored = Item::with('inventarios')->find($eid);
        echo "  - Stock Restaurado Emisor (ID {$eid}): {$eitemRestored->inventarios->cantidad} (Esperado: {$stocksEmisorIniciales[$eid]})\n";
        if ($eitemRestored->inventarios->cantidad !== $stocksEmisorIniciales[$eid]) {
            throw new Exception("El stock del emisor (ID {$eid}) no se restauró correctamente.");
        }
    }

    echo "  - Estado de la negociación: {$neg->estado} (Esperado: cancelado)\n";
    if ($neg->estado !== 'cancelado') {
        throw new Exception("El estado no cambió a cancelado.");
    }

    echo "\n✅ ¡Prueba completada con éxito! Todos los flujos de stock funcionan de manera atómica y correcta.\n";

} catch (Exception $e) {
    echo "\n❌ ERROR EN LA PRUEBA: " . $e->getMessage() . "\n";
} finally {
    // Revertir todo para no tocar los datos reales de la DB
    DB::rollBack();
    echo "Revolviendo la transacción (DB::rollBack) limpia exitosa.\n";
}
