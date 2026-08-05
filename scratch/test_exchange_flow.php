<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Item;
use App\Models\Inventario;
use App\Models\Negociacion;
use App\Services\NegociacionService;
use Illuminate\Support\Facades\DB;

try {
    echo "=== INICIANDO PRUEBA DE FLUJO DE INTERCAMBIO END-TO-END ===\n\n";

    DB::beginTransaction();
    echo "✔ Transacción de base de datos iniciada (los cambios se revertirán al finalizar).\n";

    // 1. Obtener dos usuarios existentes de la base de datos para evitar restricciones de esquema en la creación
    $users = User::limit(2)->get();
    if ($users->count() < 2) {
        throw new \Exception("La base de datos debe tener al menos dos usuarios para realizar la prueba.");
    }
    $emisor = $users[0];
    $receptor = $users[1];
    echo "✔ Usuario Emisor seleccionado: {$emisor->nombres} {$emisor->apellidos} (ID: {$emisor->id}, Email: {$emisor->email})\n";
    echo "✔ Usuario Receptor seleccionado: {$receptor->nombres} {$receptor->apellidos} (ID: {$receptor->id}, Email: {$receptor->email})\n";

    // 2. Obtener o crear ítems de prueba
    // Ítem del receptor (el solicitado)
    $itemReceptor = Item::where('id_user', $receptor->id)->first();
    if (!$itemReceptor) {
        $itemReceptor = Item::create([
            'id_user' => $receptor->id,
            'item' => 'Articulo Solicitado Receptor',
            'slug' => 'articulo-solicitado-receptor-' . time(),
            'presentacion' => 'Presentación de prueba',
            'tipo_trans' => 3, // Venta e Intercambio
            'id_categoria_item' => 1,
            'estatus' => 1,
        ]);
        echo "✔ Ítem del Receptor creado (ID: {$itemReceptor->id_item}, Nombre: {$itemReceptor->item}).\n";
    } else {
        // Asegurar estatus activo
        $itemReceptor->update(['estatus' => 1, 'tipo_trans' => 3]);
        echo "✔ Ítem del Receptor encontrado (ID: {$itemReceptor->id_item}, Nombre: {$itemReceptor->item}).\n";
    }

    // Asegurar stock del ítem solicitado
    $stockReceptor = Inventario::where('id_item', $itemReceptor->id_item)->first();
    if (!$stockReceptor) {
        $stockReceptor = Inventario::create([
            'id_item' => $itemReceptor->id_item,
            'cantidad' => 5,
        ]);
    } else {
        $stockReceptor->update(['cantidad' => 5]);
    }
    echo "  -> Stock inicial del receptor: {$stockReceptor->cantidad} unidades.\n";

    // Ítem del emisor (el ofrecido)
    $itemEmisor = Item::where('id_user', $emisor->id)->first();
    if (!$itemEmisor) {
        $itemEmisor = Item::create([
            'id_user' => $emisor->id,
            'item' => 'Articulo Ofrecido Emisor',
            'slug' => 'articulo-ofrecido-emisor-' . time(),
            'presentacion' => 'Presentación de prueba emisor',
            'tipo_trans' => 2, // Intercambio
            'id_categoria_item' => 1,
            'estatus' => 1,
        ]);
        echo "✔ Ítem del Emisor creado (ID: {$itemEmisor->id_item}, Nombre: {$itemEmisor->item}).\n";
    } else {
        // Asegurar estatus activo
        $itemEmisor->update(['estatus' => 1, 'tipo_trans' => 2]);
        echo "✔ Ítem del Emisor encontrado (ID: {$itemEmisor->id_item}, Nombre: {$itemEmisor->item}).\n";
    }

    // Asegurar stock del ítem ofrecido
    $stockEmisor = Inventario::where('id_item', $itemEmisor->id_item)->first();
    if (!$stockEmisor) {
        $stockEmisor = Inventario::create([
            'id_item' => $itemEmisor->id_item,
            'cantidad' => 3,
        ]);
    } else {
        $stockEmisor->update(['cantidad' => 3]);
    }
    echo "  -> Stock inicial del emisor: {$stockEmisor->cantidad} unidades.\n";


    // 3. PASO 1: Emisor propone intercambio
    echo "\n--- PASO 1: Creación de la Propuesta de Intercambio ---\n";
    $service = app(NegociacionService::class);
    
    $datosPropuesta = [
        'item_id' => $itemReceptor->id_item,
        'mensaje' => 'Hola, te propongo cambiar mi artículo por el tuyo.',
        'monto_oferta' => 150.00,
        'items_ofrecidos' => [$itemEmisor->id_item],
    ];

    $res = $service->crear($emisor->id, $datosPropuesta);
    if (!$res['success']) {
        throw new \Exception("Fallo al crear propuesta: " . $res['message']);
    }
    echo "✔ Propuesta creada con éxito: {$res['message']}\n";

    // Obtener la negociación recién creada
    $negociacion = Negociacion::where('usuario_emisor_id', $emisor->id)
        ->where('receptor_item_id', $itemReceptor->id_item)
        ->orderByDesc('id_negociacion')
        ->first();

    if (!$negociacion) {
        throw new \Exception("No se encontró el registro de la negociación.");
    }
    echo "  -> Negociación ID: {$negociacion->id_negociacion}, Estado actual: '{$negociacion->estado}'\n";


    // 4. PASO 2: Receptor acepta la propuesta
    echo "\n--- PASO 2: Receptor Acepta la Propuesta ---\n";
    
    // Logear al receptor
    auth()->login($receptor);

    $res = $service->aceptar($receptor->id, $negociacion->id_negociacion);
    if (!$res['success']) {
        throw new \Exception("Fallo al aceptar propuesta: " . $res['message']);
    }
    echo "✔ Propuesta aceptada con éxito: {$res['message']}\n";

    $negociacion->refresh();
    echo "  -> Estado de la negociación: '{$negociacion->estado}'\n";

    // Verificar reserva de inventario
    $stockReceptor->refresh();
    $stockEmisor->refresh();
    echo "  -> Stock actual del receptor: {$stockReceptor->cantidad} (esperado: 4, se descuenta 1 al reservar/aceptar)\n";
    echo "  -> Stock actual del emisor: {$stockEmisor->cantidad} (esperado: 2, se descuenta 1 al reservar/aceptar)\n";

    if ($stockReceptor->cantidad !== 4 || $stockEmisor->cantidad !== 2) {
        throw new \Exception("Fallo en la reserva/descuento de inventario.");
    }


    // 5. PASO 3: Confirmaciones mutuas
    echo "\n--- PASO 3: Aprobaciones Mutuas (Confirmaciones) ---\n";

    // Emisor confirma
    $res = $service->confirmarEmisor($emisor->id, $negociacion->id_negociacion);
    if (!$res['success']) {
        throw new \Exception("Fallo al confirmar por parte del emisor: " . $res['message']);
    }
    echo "✔ Emisor confirmó: {$res['message']}\n";

    // Receptor confirma
    $res = $service->confirmarReceptor($receptor->id, $negociacion->id_negociacion);
    if (!$res['success']) {
        throw new \Exception("Fallo al confirmar por parte del receptor: " . $res['message']);
    }
    echo "✔ Receptor confirmó: {$res['message']}\n";

    $negociacion->refresh();
    echo "  -> Estado confirmado emisor: " . ($negociacion->emisor_confirmado ? 'SÍ' : 'NO') . "\n";
    echo "  -> Estado confirmado receptor: " . ($negociacion->receptor_confirmado ? 'SÍ' : 'NO') . "\n";


    // 6. PASO 4: Completar el intercambio
    echo "\n--- PASO 4: Completar el Intercambio ---\n";

    $res = $service->completar($emisor->id, $negociacion->id_negociacion);
    if (!$res['success']) {
        throw new \Exception("Fallo al completar intercambio: " . $res['message']);
    }
    echo "✔ Intercambio marcado como completado: {$res['message']}\n";

    $negociacion->refresh();
    echo "  -> Estado de la negociación final: '{$negociacion->estado}'\n";

    // Verificar stocks definitivos en el ERP
    $stockReceptor->refresh();
    $stockEmisor->refresh();
    echo "  -> Stock final del receptor: {$stockReceptor->cantidad} unidades (esperado: 4)\n";
    echo "  -> Stock final del emisor: {$stockEmisor->cantidad} unidades (esperado: 2)\n";

    if ($stockReceptor->cantidad !== 4 || $stockEmisor->cantidad !== 2) {
        throw new \Exception("Error en stock final.");
    }

    echo "\n🎉 TODO EL FLUJO DE INTERCAMBIO SE COMPLETÓ CON ÉXITO SIN ERRORES! 🎉\n";

    // Revertir transacción para no ensuciar la BD real
    DB::rollBack();
    echo "\n✔ Transacción de base de datos revertida. Base de datos limpia.\n";

} catch (\Throwable $e) {
    DB::rollBack();
    echo "\n❌ ERROR EN EL FLUJO: " . $e->getMessage() . "\n";
    echo "Línea: " . $e->getLine() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
    exit(1);
}
