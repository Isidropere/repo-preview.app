<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Desactivar Scout durante el test para evitar fallos si Elasticsearch está apagado
config(['scout.driver' => null]);

use App\Models\User;
use App\Models\Item;
use App\Models\Direcciones;
use App\Models\TarjetaPago;
use App\Models\Carrito;
use App\Models\ItemIntencionCompra;
use App\Models\Inventario;
use App\Models\PagoCompra;
use App\Services\CheckoutService;
use App\Services\ERPService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

function printHeader($title) {
    echo "\n==================================================\n";
    echo " TEST: " . strtoupper($title) . "\n";
    echo "==================================================\n";
}

function printSuccess($msg) {
    echo "  ✅ EXITO: $msg\n";
}

function printFailure($msg) {
    echo "  ❌ FALLO: $msg\n";
}

function printInfo($msg) {
    echo "  ℹ️ INFO: $msg\n";
}

function resetTestingState() {
    \Illuminate\Support\Facades\Facade::clearResolvedInstances();
    
    app()->forgetInstance(\Illuminate\Http\Client\Factory::class);
    app()->forgetInstance('http');
    
    app()->forgetInstance('mailer');
    app()->forgetInstance('mail.manager');
    app()->forgetInstance(\Illuminate\Contracts\Mail\Mailer::class);
    
    Mockery::close();
    
    // Configurar el driver de correo a 'array' para pruebas en memoria
    config(['mail.default' => 'array']);
}

function setupTestData() {
    // 1. Obtener o crear un usuario de prueba
    $user = User::firstOrCreate(
        ['email' => 'azul_test_user@cambialo.com'],
        [
            'nombres' => 'Juan',
            'apellidos' => 'Perez',
            'telefono' => '(829) 963-4839',
            'nombre_usuario' => 'azul_test_user',
            'password' => bcrypt('password123'),
            'estatus' => 1,
            'id_tipo_usuario' => 1,
        ]
    );

    // 2. Obtener o crear una dirección predeterminada
    $direccion = Direcciones::where('id_user', $user->id)
        ->where('calle', 'Napoleón Bonaparte')
        ->first();

    if (!$direccion) {
        $maxId = (int) (Direcciones::max('id_direccion') ?? 0);
        $direccion = Direcciones::create([
            'id_direccion' => $maxId + 1,
            'id_user' => $user->id,
            'calle' => 'Napoleón Bonaparte',
            'N_casa_edificio' => 'Manzana T, Edificio 21',
            'sector' => 'Res. Pablo Mella Morales II',
            'id_provincia' => 1, // Santo Domingo
            'id_municipio' => 1,
            'es_predeterminada' => 1,
            'telefono_contacto' => '(829) 963-4839',
        ]);
    }
    
    // Asegurar que esté marcada como predeterminada
    Direcciones::where('id_user', $user->id)->update(['es_predeterminada' => 0]);
    Direcciones::where('id_direccion', $direccion->id_direccion)->update(['es_predeterminada' => 1]);

    // 3. Crear una tarjeta de prueba con números de pruebas de AZUL (Visa)
    TarjetaPago::where('id_user', $user->id)->delete();
    
    $tarjeta = new TarjetaPago();
    $tarjeta->id_user = $user->id;
    $tarjeta->no_tarjeta = '4000123456789010'; // Visa de prueba oficial
    $tarjeta->nombre_titular = 'JUAN PEREZ';
    $tarjeta->mes_expiracion = 12;
    $tarjeta->{'año_expiracion'} = 2028;
    $tarjeta->tipo_tarjeta = 'Visa';
    $tarjeta->banco_tarjeta = 'AZUL TEST';
    $tarjeta->last4 = '9010';
    $tarjeta->usar_esta_tarjeta = 1;
    $tarjeta->estatus = 1;
    $tarjeta->save();

    // 4. Buscar o crear un artículo para comprar que pertenezca a OTRO usuario
    $vendedor = User::where('id', '!=', $user->id)->first();
    if (!$vendedor) {
        $vendedor = User::create([
            'nombres' => 'Vendedor',
            'apellidos' => 'Prueba',
            'email' => 'vendedor_azul@cambialo.com',
            'telefono' => '(829) 555-0100',
            'nombre_usuario' => 'vendedor_azul',
            'password' => bcrypt('password123'),
            'estatus' => 1,
            'id_tipo_usuario' => 1,
        ]);
    }

    $item = Item::firstOrCreate(
        [
            'id_user' => $vendedor->id,
            'item' => 'Artículo de Prueba AZUL',
        ],
        [
            'presentacion' => 'Descripción del artículo de prueba para checkout de AZUL',
            'valor' => 1500.00, // RD$ 1,500.00
            'estatus' => 1,
            'id_categoria_item' => 1,
            'tipo_trans' => 1,
        ]
    );
    
    // Asegurar que el item esté activo y tenga valor correcto
    $item->estatus = 1;
    $item->valor = 1500.00;
    $item->save();

    // Asegurar que tenga inventario/stock
    $inventario = Inventario::updateOrCreate(
        ['id_item' => $item->id_item],
        [
            'cantidad' => 10,
            'estatus' => 1,
        ]
    );

    // 5. Configurar el carrito de compras del usuario
    $carrito = Carrito::firstOrCreate(['id_user' => $user->id]);
    $carrito->tipo = 'producto';
    $carrito->save();

    // Limpiar items anteriores del carrito
    ItemIntencionCompra::where('id_carrito', $carrito->id_carrito)->delete();

    // Agregar el item al carrito
    ItemIntencionCompra::create([
        'id_carrito' => $carrito->id_carrito,
        'id_item' => $item->id_item,
        'cantidad' => 1,
        'descuento' => 0.00,
        'es_seleccionado' => 1,
    ]);

    return [$user, $tarjeta, $item, $carrito, $direccion];
}

echo "==================================================\n";
echo "INICIANDO BANCO DE PRUEBAS PARA PASARELA AZUL\n";
echo "==================================================\n";

// -----------------------------------------------------------------------------
// TEST 1: COBRO EXITOSO MOCKEADO
// -----------------------------------------------------------------------------
printHeader("1. Cobro Exitoso Mockeado");
resetTestingState();
DB::beginTransaction();
try {
    list($user, $tarjeta, $item, $carrito, $direccion) = setupTestData();

    // Simular llamada HTTP exitosa de AZUL
    Http::fake([
        'https://pruebas.azul.com.do/*' => Http::response([
            'IsoCode' => '00',
            'ResponseCode' => 'ISO8583',
            'ResponseMessage' => 'APROBADO',
            'AuthorizationCode' => '998877',
            'AzulOrderId' => 'MOCK_ORDER_12345',
            'DateTime' => '2026-06-10 12:00:00'
        ], 200)
    ]);

    auth()->login($user);
    $checkoutService = app(CheckoutService::class);
    $res = $checkoutService->procesar($user->id, $tarjeta->id_tarjeta, '123', '127.0.0.1');

    if (!$res['success']) {
        throw new \Exception("procesar() retornó falso: " . $res['message']);
    }
    
    // Validar en la base de datos
    $pago = PagoCompra::where('id_carrito', $carrito->id_carrito)->latest('fecha')->first();
    if (!$pago) {
        throw new \Exception("No se registró el registro PagoCompra en la base de datos.");
    }
    if ($pago->transaction_id !== 'MOCK_ORDER_12345' || $pago->autorizacion_pago !== '998877') {
        throw new \Exception("Los datos de transacción registrados en base de datos son incorrectos.");
    }

    // Validar stock (debe bajar de 10 a 9)
    $stockActual = Inventario::where('id_item', $item->id_item)->value('cantidad');
    if ($stockActual !== 9) {
        throw new \Exception("El stock no fue decrementado correctamente. Stock actual: " . $stockActual);
    }

    // Validar logs de pagos
    $logPago = DB::table('logs_pagos')->where('custom_order_id', substr((string)$carrito->id_carrito, 0, 15))->first();
    if (!$logPago) {
        throw new \Exception("No se registró el log de pago en la tabla logs_pagos.");
    }
    if ($logPago->is_success != 1) {
        throw new \Exception("El log de pago indica falla (is_success != 1).");
    }

    // Validar envío de correo (usando el driver 'array' en memoria de Symfony)
    $emails = app('mail.manager')->mailer()->getSymfonyTransport()->messages();
    if ($emails->isEmpty()) {
        throw new \Exception("No se envió ningún correo de recibo.");
    }
    
    $sentMessage = $emails->first();
    $envelope = $sentMessage->getEnvelope();
    $recipients = $envelope->getRecipients();
    
    // Buscar si el correo fue enviado al usuario correcto
    $foundRecipient = false;
    foreach ($recipients as $recipient) {
        if ($recipient->getAddress() === $user->email) {
            $foundRecipient = true;
            break;
        }
    }
    
    if (!$foundRecipient) {
        throw new \Exception("El correo de recibo fue enviado pero no al destinatario: " . $user->email);
    }

    printSuccess("El cobro exitoso simulado y todas sus aserciones funcionaron perfectamente.");
} catch (\Throwable $e) {
    printFailure($e->getMessage());
} finally {
    DB::rollBack();
}

// -----------------------------------------------------------------------------
// TEST 2: PAGO DECLINADO MOCKEADO
// -----------------------------------------------------------------------------
printHeader("2. Pago Declinado Mockeado");
resetTestingState();
DB::beginTransaction();
try {
    list($user, $tarjeta, $item, $carrito, $direccion) = setupTestData();

    // Simular llamada HTTP declinada de AZUL (por fondos insuficientes u otro motivo)
    Http::fake([
        'https://pruebas.azul.com.do/*' => Http::response([
            'IsoCode' => '05',
            'ResponseCode' => 'ISO8583',
            'ResponseMessage' => 'DECLINADO',
            'ErrorDescription' => 'Fondos Insuficientes'
        ], 200)
    ]);

    auth()->login($user);
    $checkoutService = app(CheckoutService::class);
    $res = $checkoutService->procesar($user->id, $tarjeta->id_tarjeta, '123', '127.0.0.1');

    if ($res['success']) {
        echo "  ℹ️ DEBUG RES: " . json_encode($res) . "\n";
        $logs = DB::table('logs_pagos')->where('id_user', $user->id)->get();
        echo "  ℹ️ DEBUG LOGS: " . json_encode($logs) . "\n";
        throw new \Exception("procesar() retornó éxito, pero debió fallar por fondos insuficientes.");
    }
    if (!str_contains($res['message'], 'DECLINADO') && !str_contains($res['message'], 'Fondos Insuficientes')) {
        throw new \Exception("El mensaje de respuesta no describe el fallo esperado: " . $res['message']);
    }

    // Validar en la base de datos (no debe existir PagoCompra)
    $pago = PagoCompra::where('id_carrito', $carrito->id_carrito)->latest('fecha')->first();
    if ($pago) {
        throw new \Exception("Se registró un PagoCompra a pesar de que el cobro fue declinado.");
    }

    // Validar stock (debe seguir en 10)
    $stockActual = Inventario::where('id_item', $item->id_item)->value('cantidad');
    if ($stockActual !== 10) {
        throw new \Exception("El stock fue decrementado para un pago rechazado.");
    }

    // Validar logs de pagos (debe indicar is_success = 0)
    $logPago = DB::table('logs_pagos')->where('custom_order_id', substr((string)$carrito->id_carrito, 0, 15))->first();
    if (!$logPago) {
        throw new \Exception("No se registró el log de pago fallido.");
    }
    if ($logPago->is_success != 0) {
        throw new \Exception("El log de pago indica éxito (is_success != 0) para un cobro declinado.");
    }

    printSuccess("El pago declinado simulado y todas sus aserciones funcionaron perfectamente.");
} catch (\Throwable $e) {
    printFailure($e->getMessage());
} finally {
    DB::rollBack();
}

// -----------------------------------------------------------------------------
// TEST 3: FALLO EN REGISTRO DE BASE DE DATOS Y REEMBOLSO AUTOMATICO
// -----------------------------------------------------------------------------
printHeader("3. Fallo en Registro BD y Reembolso Automático");
resetTestingState();
DB::beginTransaction();
try {
    list($user, $tarjeta, $item, $carrito, $direccion) = setupTestData();

    // Simular secuencia HTTP de AZUL:
    // 1. Cobro inicial exitoso
    // 2. Reembolso subsecuente exitoso
    Http::fake([
        'https://pruebas.azul.com.do/*' => Http::sequence()
            ->push([
                'IsoCode' => '00',
                'ResponseCode' => 'ISO8583',
                'ResponseMessage' => 'APROBADO',
                'AuthorizationCode' => '998877',
                'AzulOrderId' => 'MOCK_ORDER_TO_REFUND',
                'DateTime' => '2026-06-10 12:00:00'
            ], 200)
            ->push([
                'IsoCode' => '00',
                'ResponseCode' => 'ISO8583',
                'ResponseMessage' => 'APROBADO',
                'AzulOrderId' => 'MOCK_ORDER_TO_REFUND'
            ], 200)
    ]);

    // Crear mock de ERPService para que tire un Error al procesar venta
    $mockERP = Mockery::mock(ERPService::class);
    $mockERP->shouldReceive('procesarVentaAprobada')
        ->once()
        ->andThrow(new \Error("Simulated Database / ERP Error"));

    $app->instance(ERPService::class, $mockERP);

    auth()->login($user);
    $checkoutService = app(CheckoutService::class);
    $res = $checkoutService->procesar($user->id, $tarjeta->id_tarjeta, '123', '127.0.0.1');

    if ($res['success']) {
        throw new \Exception("procesar() retornó éxito pero debía fallar por error de BD.");
    }
    if (!str_contains($res['message'], 'El cargo fue revertido automáticamente')) {
        throw new \Exception("El mensaje de error no indica la reversión del cargo: " . $res['message']);
    }

    // Verificar que no hay registros guardados en BD debido al rollback
    $pago = PagoCompra::where('id_carrito', $carrito->id_carrito)->latest('fecha')->first();
    if ($pago) {
        throw new \Exception("Se registró un PagoCompra a pesar del error de BD y la reversión.");
    }

    // Verificar stock (debe seguir en 10 por el rollback)
    $stockActual = Inventario::where('id_item', $item->id_item)->value('cantidad');
    if ($stockActual !== 10) {
        throw new \Exception("El stock fue modificado a pesar del error y reversión.");
    }

    // Verificar logs de pagos (debe haber registros de cobro y de reembolso)
    $logs = DB::table('logs_pagos')->where('id_user', $user->id)->get();
    if ($logs->count() < 2) {
        throw new \Exception("Se esperaban al menos 2 logs de pago (sale + refund) para el usuario. Encontrados: " . $logs->count());
    }

    $saleLog = $logs->where('transaction_type', 'sale')->first();
    $refundLog = $logs->where('transaction_type', 'refund')->first();

    if (!$saleLog || $saleLog->is_success != 1) {
        throw new \Exception("El log del cobro original no indica éxito.");
    }
    if (!$refundLog || $refundLog->is_success != 1) {
        throw new \Exception("El log del reembolso/anulación automática no indica éxito.");
    }

    printSuccess("La reversión y el reembolso automático ante errores de BD funcionaron perfectamente.");
} catch (\Throwable $e) {
    printFailure($e->getMessage());
} finally {
    // Restaurar el servicio real ERPService en el contenedor
    $app->instance(ERPService::class, new ERPService());
    Mockery::close();
    DB::rollBack();
}

// -----------------------------------------------------------------------------
// TEST 4: COBRO REAL EN ENTORNO DE PRUEBAS DE AZUL
// -----------------------------------------------------------------------------
printHeader("4. Cobro Real en Sandbox de AZUL");
resetTestingState();
$azulStore = config('services.azul.store');
$azulAuth1 = config('services.azul.auth1');
$azulAuth2 = config('services.azul.auth2');

if ($azulAuth1 === 'factor1' || $azulAuth2 === 'factor2' || empty($azulAuth1) || empty($azulAuth2)) {
    printInfo("El test real se omitió porque las credenciales en .env son marcadores de posición (factor1/factor2).");
    printInfo("Para ejecutar la prueba real contra AZUL, actualice AZUL_AUTH1 y AZUL_AUTH2 en el archivo .env.");
} else {
    DB::beginTransaction();
    try {
        list($user, $tarjeta, $item, $carrito, $direccion) = setupTestData();
        Mail::fake();

        printInfo("Enviando petición de cobro real a https://pruebas.azul.com.do/ con Store ID: $azulStore...");

        auth()->login($user);
        $checkoutService = app(CheckoutService::class);
        $res = $checkoutService->procesar($user->id, $tarjeta->id_tarjeta, '123', '127.0.0.1');

        if ($res['success']) {
            printSuccess("¡Cobro Sandbox REAL completado satisfactoriamente!");
            printInfo("Mensaje: " . $res['message']);
            
            $pago = PagoCompra::where('id_carrito', $carrito->id_carrito)->latest('fecha')->first();
            if ($pago) {
                echo "  - AzulOrderId: {$pago->transaction_id}\n";
                echo "  - Código de Autorización: {$pago->autorizacion_pago}\n";
            }
        } else {
            printFailure("El cobro real en el Sandbox falló: " . $res['message']);
        }
    } catch (\Throwable $e) {
        printFailure("Excepción durante la prueba real: " . $e->getMessage());
    } finally {
        DB::rollBack();
    }
}

echo "==================================================\n";
echo "PRUEBAS DE INTEGRACIÓN FINALIZADAS\n";
echo "==================================================\n";
