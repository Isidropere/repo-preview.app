<?php

/**
 * =========================================================================
 * Script de Pruebas de Integración para el Flujo de Redirección de AZUL
 * =========================================================================
 *
 * Simula de forma programática las transiciones de estado de base de datos
 * e inventario de un checkout con redirección a AZUL.
 *
 * Ejecutar con: php scratch/test_azul_redirect_flow.php
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Desactivar Scout para evitar excepciones de Elasticsearch
config(['scout.driver' => null]);
config(['mail.default' => 'array']);

use App\Models\User;
use App\Models\Item;
use App\Models\Direcciones;
use App\Models\Carrito;
use App\Models\ItemIntencionCompra;
use App\Models\Inventario;
use App\Models\PagoCompra;
use App\Models\PagoItem;
use App\Models\CompraTrazabilidad;
use App\Http\Controllers\PagoRedirectController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

function printRes(bool $success, string $msg) {
    if ($success) {
        echo "  ✅ EXITO: {$msg}\n";
    } else {
        echo "  ❌ FALLO: {$msg}\n";
        throw new \Exception("Aserción de test fallida: " . $msg);
    }
}

// 1. Preparar datos de prueba
echo "=== Preparando datos de prueba ===\n";

$user = User::firstOrCreate(
    ['email' => 'redirect_auditor@cambialo.com'],
    [
        'nombres' => 'Franklyn',
        'apellidos' => 'Auditor',
        'telefono' => '(829) 963-4839',
        'nombre_usuario' => 'redirect_auditor',
        'password' => bcrypt('password123'),
        'estatus' => 1,
        'id_tipo_usuario' => 1,
    ]
);

$direccion = Direcciones::where('id_user', $user->id)->first();
if (!$direccion) {
    $maxId = (int) (Direcciones::max('id_direccion') ?? 0);
    $direccion = Direcciones::create([
        'id_direccion' => $maxId + 1,
        'id_user' => $user->id,
        'calle' => 'Napoleón Bonaparte',
        'N_casa_edificio' => 'Edificio 21',
        'sector' => 'Res. Pablo Mella',
        'id_provincia' => 1,
        'id_municipio' => 1,
        'es_predeterminada' => 1,
        'telefono_contacto' => '(829) 963-4839',
    ]);
}
Direcciones::where('id_user', $user->id)->update(['es_predeterminada' => 0]);
Direcciones::where('id_direccion', $direccion->id_direccion)->update(['es_predeterminada' => 1]);

// Autenticar al usuario en la sesión para el test
auth()->login($user);

// Limpiar carrito y compras viejas del test
$carrito = Carrito::firstOrCreate(['id_user' => $user->id], ['tipo' => 'producto']);
ItemIntencionCompra::where('id_carrito', $carrito->id_carrito)->delete();

// Asegurar base de datos limpia de compras anteriores
$oldPagoCompras = PagoCompra::where('id_carrito', $carrito->id_carrito)->get();
foreach ($oldPagoCompras as $oldPago) {
    PagoItem::where('id_pago_compra', $oldPago->id_pago_compra)->delete();
    CompraTrazabilidad::where('id_pago_compra', $oldPago->id_pago_compra)->delete();
    $oldPago->delete();
}

// Crear un artículo de prueba con inventario
$item = Item::create([
    'item' => 'Laptop de Prueba Azul Redirect',
    'descripcion' => 'Artículo para probar la redirección',
    'presentacion' => 'Descripción corta de laptop',
    'valor' => 1000.00,
    'estatus' => 1,
    'id_user' => 9999, // Dueño ficticio para evitar comprarse a sí mismo
    'id_categoria_item' => 1,
    'tipo_trans' => 1,
]);

$inventario = Inventario::create([
    'id_item' => $item->id_item,
    'cantidad' => 10,
    'cantidad_minima' => 1,
    'fecha_actualizacion' => now(),
]);

// Agregar el item al carrito como seleccionado
ItemIntencionCompra::create([
    'id_carrito' => $carrito->id_carrito,
    'id_item' => $item->id_item,
    'cantidad' => 2,
    'es_seleccionado' => true,
    'descuento' => 0,
]);

echo "Datos de prueba configurados correctamente.\n\n";

try {
    // 1. Crear y configurar el proveedor
    $provider = app(\App\Services\Payments\AzulProvider::class);
    $reflection = new \ReflectionClass($provider);
    $prop = $reflection->getProperty('authKey');
    $prop->setAccessible(true);
    $testKey = 'super_secret_auth_key_12345';
    $prop->setValue($provider, $testKey);
    
    // Registrar la misma instancia en el contenedor de dependencias (Singleton para el test)
    app()->instance(\App\Services\Payments\AzulProvider::class, $provider);

    // 2. Instanciar el controlador que recibirá el proveedor configurado
    $controller = app(PagoRedirectController::class);

    // -------------------------------------------------------------------------
    // TEST 1: Iniciar Pago (Paso 1: Reservar Stock)
    // -------------------------------------------------------------------------
    echo "--- TEST 1: Iniciar Pago (Reserva de stock) ---\n";
    
    // Simular el inicio de pago
    $request = Request::create('/pago/iniciar', 'POST');
    $responseView = $controller->iniciarPago($request);
    
    // Buscar la orden creada
    $pagoCompra = PagoCompra::where('id_carrito', $carrito->id_carrito)
        ->where('estatus', 'pendiente')
        ->first();
        
    printRes($pagoCompra !== null, "Se creó la compra en estado 'pendiente'.");
    printRes($pagoCompra->total == 2000.00, "El monto total de la orden es correcto (RD$ 2,000.00).");
    
    // Validar que el stock se haya reducido temporalmente (10 -> 8)
    $inventario->refresh();
    printRes($inventario->cantidad == 8, "El inventario se decrementó/reservó con éxito (10 -> 8).");
    
    // Validar que se crearon los PagoItems
    $pagoItems = PagoItem::where('id_pago_compra', $pagoCompra->id_pago_compra)->get();
    printRes($pagoItems->count() === 1, "Se registró el PagoItem temporalmente.");
    printRes($pagoItems->first()->cantidad === 2, "La cantidad del PagoItem es correcta (2).");
    echo "TEST 1: COMPLETADO CON ÉXITO\n\n";

    // -------------------------------------------------------------------------
    // TEST 2: Pago Aprobado (Confirmación de compra)
    // -------------------------------------------------------------------------
    echo "--- TEST 2: Confirmación de Pago Aprobado ---\n";
    
    // Generar firma de respuesta legítima para el test
    $responseParams = [
        'OrderNumber'       => $pagoCompra->id_pago_compra,
        'Amount'            => '200000', // RD$ 2,000.00 en centavos
        'AuthorizationCode' => 'AUTH777888',
        'DateTime'          => '20260611171000',
        'ResponseCode'      => 'ISO8583',
        'IsoCode'           => '00',
        'ResponseMessage'   => 'APROBADA',
        'ErrorDescription'  => '',
        'RRN'               => '112233445566',
        'AzulOrderId'       => 'AZUL-11223344',
    ];
    
    $responseConcat = $responseParams['OrderNumber'] .
                      $responseParams['Amount'] .
                      $responseParams['AuthorizationCode'] .
                      $responseParams['DateTime'] .
                      $responseParams['ResponseCode'] .
                      $responseParams['IsoCode'] .
                      $responseParams['ResponseMessage'] .
                      $responseParams['ErrorDescription'] .
                      $responseParams['RRN'] .
                      $testKey;
                      
    $responseUtf16 = mb_convert_encoding($responseConcat, 'UTF-16LE', 'UTF-8');
    $responseParams['AuthHash'] = hash_hmac('sha512', $responseUtf16, $testKey);
    
    // Simular el callback de aprobado
    $callbackRequest = Request::create('/pago/aprobado', 'POST', $responseParams);
    $responseRedirect = $controller->pagoAprobado($callbackRequest);
    
    // Verificar que la compra haya cambiado a aprobada
    $pagoCompra->refresh();
    printRes($pagoCompra->estatus === 'aprobado', "El estatus de la compra cambió exitosamente a 'aprobado'.");
    printRes($pagoCompra->autorizacion_pago === 'AUTH777888', "Se registró el código de autorización correcto.");
    printRes($pagoCompra->transaction_id === 'AZUL-11223344', "Se registró el ID de transacción de AZUL.");
    
    // Validar que los ítems del carrito comprados hayan sido eliminados
    $cartItemsCount = ItemIntencionCompra::where('id_carrito', $carrito->id_carrito)->count();
    printRes($cartItemsCount === 0, "Se eliminaron con éxito los productos comprados del carrito de compras.");
    
    // Validar que el stock se mantiene en 8 (se confirmó la reserva)
    $inventario->refresh();
    printRes($inventario->cantidad == 8, "El inventario se mantiene correctamente consolidado en 8.");
    
    // Cambiar la fecha hacia atrás para saltarse la validación de orden duplicada de 2 minutos
    $pagoCompra->fecha = now()->subMinutes(5);
    $pagoCompra->save();

    echo "TEST 2: COMPLETADO CON ÉXITO\n\n";

    // -------------------------------------------------------------------------
    // TEST 3: Declinación / Cancelación (Revertir reserva de stock)
    // -------------------------------------------------------------------------
    echo "--- TEST 3: Pago Declinado (Revertir stock) ---\n";
    
    // Crear otra orden de prueba pendiente para simular una declinación
    ItemIntencionCompra::create([
        'id_carrito' => $carrito->id_carrito,
        'id_item' => $item->id_item,
        'cantidad' => 3,
        'es_seleccionado' => true,
        'descuento' => 0,
    ]);
    
    $req2 = Request::create('/pago/iniciar', 'POST');
    $controller->iniciarPago($req2);
    
    $pagoCompra2 = PagoCompra::where('id_carrito', $carrito->id_carrito)
        ->where('estatus', 'pendiente')
        ->first();
        
    $inventario->refresh();
    printRes($inventario->cantidad == 5, "Se reservó el stock del segundo pedido correctamente (8 -> 5).");
    
    // Generar firma de respuesta declinada para el test
    $declineParams = [
        'OrderNumber'       => $pagoCompra2->id_pago_compra,
        'Amount'            => '300000',
        'AuthorizationCode' => '',
        'DateTime'          => '20260611172000',
        'ResponseCode'      => 'ISO8583',
        'IsoCode'           => '05', // Declinada
        'ResponseMessage'   => 'DECLINADA',
        'ErrorDescription'  => 'Fondos insuficientes',
        'RRN'               => '999999999999',
        'AzulOrderId'       => 'AZUL-99999999',
    ];
    
    $declineConcat = $declineParams['OrderNumber'] .
                     $declineParams['Amount'] .
                     $declineParams['AuthorizationCode'] .
                     $declineParams['DateTime'] .
                     $declineParams['ResponseCode'] .
                     $declineParams['IsoCode'] .
                     $declineParams['ResponseMessage'] .
                     $declineParams['ErrorDescription'] .
                     $declineParams['RRN'] .
                     $testKey;
                     
    $declineUtf16 = mb_convert_encoding($declineConcat, 'UTF-16LE', 'UTF-8');
    $declineParams['AuthHash'] = hash_hmac('sha512', $declineUtf16, $testKey);
    
    // Simular el callback de declinado
    $declineRequest = Request::create('/pago/declinado', 'POST', $declineParams);
    $controller->pagoDeclinado($declineRequest);
    
    // Verificar que la compra cambió a declinada
    $pagoCompra2->refresh();
    printRes($pagoCompra2->estatus === 'declinado', "El estatus de la segunda orden cambió a 'declinado'.");
    
    // Verificar que el inventario se haya liberado e incrementado de vuelta (5 -> 8)
    $inventario->refresh();
    printRes($inventario->cantidad == 8, "El inventario reservado fue liberado y restablecido correctamente (5 -> 8).");
    echo "TEST 3: COMPLETADO CON ÉXITO\n\n";

    // 4. Limpieza final de la base de datos
    echo "=== Limpiando base de datos ===\n";
    $pagoIds = [$pagoCompra->id_pago_compra, $pagoCompra2->id_pago_compra];
    PagoItem::whereIn('id_pago_compra', $pagoIds)->delete();
    CompraTrazabilidad::whereIn('id_pago_compra', $pagoIds)->delete();
    PagoCompra::whereIn('id_pago_compra', $pagoIds)->delete();
    ItemIntencionCompra::where('id_carrito', $carrito->id_carrito)->delete();
    $inventario->delete();
    $item->delete();
    
    echo "🎉 TODOS LOS TESTS DE FLUJO COMPLETADOS CON ÉXITO.\n";

} catch (\Throwable $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    
    // Limpieza de emergencia
    if (isset($pagoCompra)) {
        PagoItem::where('id_pago_compra', $pagoCompra->id_pago_compra)->delete();
        CompraTrazabilidad::where('id_pago_compra', $pagoCompra->id_pago_compra)->delete();
        $pagoCompra->delete();
    }
    if (isset($pagoCompra2)) {
        PagoItem::where('id_pago_compra', $pagoCompra2->id_pago_compra)->delete();
        CompraTrazabilidad::where('id_pago_compra', $pagoCompra2->id_pago_compra)->delete();
        $pagoCompra2->delete();
    }
    if (isset($item)) {
        $item->delete();
    }
    exit(1);
}
