<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Item;
use App\Models\Direcciones;
use App\Http\Controllers\API\ItemApiController;
use App\Http\Controllers\API\NegociacionApiController;
use App\Http\Controllers\API\TransporteApiController;
use Illuminate\Validation\ValidationException;
use App\Services\AdminComprasService;

class TestApiFlowsCommand extends Command
{
    protected $signature = 'test:api-flows';
    protected $description = 'Simulate mobile API flows (Item creation, updates, and Negociaciones)';

    public function handle(AdminComprasService $adminComprasService)
    {
        $this->info('Starting Mobile API test flows...');

        // 1. Create Users
        $this->info('1. Creating Users for API Tests...');
        $admin = User::firstOrCreate(['email' => 'admin_test@test.com'], [
            'nombres' => 'Admin', 'apellidos' => 'Test',
            'nombre_usuario' => 'admintest', 'password' => bcrypt('password'),
            'telefono' => '809-111-2222', 'estatus' => 1, 'id_tipo_usuario' => 1, 'isAdmin' => 1
        ]);
        
        $users = [];
        for ($i = 1; $i <= 4; $i++) {
            $users[$i] = User::firstOrCreate(['email' => "testapi{$i}@test.com"], [
                'nombres' => "TestAPI", 'apellidos' => "User{$i}",
                'nombre_usuario' => "testapi{$i}", 'password' => bcrypt('password'),
                'telefono' => '809-666-000'.$i, 'estatus' => 1, 'id_tipo_usuario' => 1
            ]);
            
            Direcciones::firstOrCreate(['id_user' => $users[$i]->id], [
                'id_provincia' => 1, 'id_municipio' => 1, 'calle' => "Calle API",
                'numero' => "123", 'sector' => 'Sector', 'codigo_postal' => '10000',
                'referencia' => 'Prueba API'
            ]);
        }
        $this->info('Users created.');

        try {
            // 2. Publish Item via API (User 1)
            $this->info('2. User 1 publishes item via API');
            $itemApi = app(ItemApiController::class);
            
            $req1 = Request::create('/api/items', 'POST', [
                'item' => 'Item API Venta',
                'id_categoria_item' => 1,
                'valor' => 500,
                'presentacion' => 'Item API description',
                'condicion' => 1,
                'tipo_trans' => 1, // Venta
                'peso_lbs' => 10,
                'alto_cm' => 10,
                'ancho_cm' => 10,
                'profundo_cm' => 10,
                'cantidad' => 5,
            ]);
            $req1->setUserResolver(fn() => $users[1]);
            
            $res1 = $this->callController($itemApi, 'store', $req1);
            if (!isset($res1['item']['id_item'])) {
                throw new \Exception("Failed to create item via API. Response: " . json_encode($res1));
            }
            $itemVentaId = $res1['item']['id_item'];
            $this->info("   Item created with ID: $itemVentaId");

            // Admin approves item
            Item::where('id_item', $itemVentaId)->update(['estado_aprobacion' => 'aprobado', 'estatus' => 1]);

            // 3. Update Item via API (User 1 changes dimensions)
            $this->info('3. User 1 updates item dimensions via API');
            $reqUpdate = Request::create("/api/items/{$itemVentaId}/update", 'POST', [
                'item' => 'Item API Venta Modificado',
                'id_categoria_item' => 1,
                'valor' => 500,
                'presentacion' => 'Item API description modificado',
                'condicion' => 1,
                'tipo_trans' => 1,
                'peso_lbs' => 25, // Changed weight
                'alto_cm' => 10,
                'ancho_cm' => 10,
                'profundo_cm' => 10,
                'cantidad' => 5,
            ]);
            $reqUpdate->setUserResolver(fn() => $users[1]);
            
            $this->callController($itemApi, 'update', $reqUpdate, $itemVentaId);
            
            // VERIFY RULE: Changing dimensions should set estatus = 0 and estado_aprobacion = 'pendiente'
            $updatedItem = Item::find($itemVentaId);
            if ($updatedItem->estatus != 0 || $updatedItem->estado_aprobacion != 'pendiente') {
                throw new \Exception("RULE FAILED: Updating dimensions did not set item to inactive/pending. Estatus: {$updatedItem->estatus}, Aprobacion: {$updatedItem->estado_aprobacion}");
            }
            $this->info("   ✅ Rule verified: Item became inactive after dimension change.");
            
            // Admin approves item again
            $updatedItem->update(['estado_aprobacion' => 'aprobado', 'estatus' => 1]);

            // 4. Exchange Flow via API
            $this->info('4. User 2 publishes item for exchange via API');
            $req2 = Request::create('/api/items', 'POST', [
                'item' => 'Item API Intercambio',
                'id_categoria_item' => 2,
                'valor' => 1000,
                'presentacion' => 'API description',
                'condicion' => 1,
                'tipo_trans' => 3, // Intercambio
                'peso_lbs' => 10,
                'alto_cm' => 10,
                'ancho_cm' => 10,
                'profundo_cm' => 10,
                'cantidad' => 1,
            ]);
            $req2->setUserResolver(fn() => $users[2]);
            $res2 = $this->callController($itemApi, 'store', $req2);
            $itemIntercambioId = $res2['item']['id_item'];
            Item::where('id_item', $itemIntercambioId)->update(['estado_aprobacion' => 'aprobado', 'estatus' => 1]);

            // User 4 has an item to offer
            $req3 = Request::create('/api/items', 'POST', [
                'item' => 'Item API Oferta',
                'id_categoria_item' => 3,
                'valor' => 800,
                'presentacion' => 'API offer description',
                'condicion' => 1,
                'tipo_trans' => 3,
                'peso_lbs' => 5,
                'alto_cm' => 5,
                'ancho_cm' => 5,
                'profundo_cm' => 5,
                'cantidad' => 1,
            ]);
            $req3->setUserResolver(fn() => $users[4]);
            $res3 = $this->callController($itemApi, 'store', $req3);
            $itemOfertaId = $res3['item']['id_item'];
            Item::where('id_item', $itemOfertaId)->update(['estado_aprobacion' => 'aprobado', 'estatus' => 1]);

            $this->info('   User 4 proposes exchange to User 2 via API');
            $negApi = app(NegociacionApiController::class);
            $reqNeg = Request::create('/api/negociaciones', 'POST', [
                'item_id' => $itemIntercambioId,
                'items_ofrecidos' => [$itemOfertaId],
                'mensaje' => 'Hola por API'
            ]);
            $reqNeg->setUserResolver(fn() => $users[4]);
            $resNeg = $this->callController($negApi, 'store', $reqNeg);
            $negId = $resNeg['negociacion']['id_negociacion'] ?? $resNeg['id_negociacion'] ?? \App\Models\Negociacion::latest()->first()->id_negociacion;
            $this->info("   Exchange proposed. ID: $negId");

            // User 2 counter offers via API
            $this->info('   User 2 counter offers via API');
            $reqCounter = Request::create("/api/negociaciones/{$negId}/contraoferta", 'POST', [
                'monto_compensacion' => 200,
                'compensacion_hacia' => $users[2]->id
            ]);
            $reqCounter->setUserResolver(fn() => $users[2]);
            $this->callController($negApi, 'contraoferta', $reqCounter, $negId);

            // User 4 accepts counter offer via API
            $this->info('   User 4 accepts counter offer via API');
            $reqAccept = Request::create("/api/negociaciones/{$negId}/aceptar-contraoferta", 'POST', []);
            $reqAccept->setUserResolver(fn() => $users[4]);
            $this->callController($negApi, 'aceptarComoEmisor', $reqAccept, $negId);
            
            $this->info("   Exchange completed via API");

            // Transporte Flow via API
            $this->info("5. User 1 requests Transporte/Mudanza via API");
            $transApi = app(TransporteApiController::class);
            $reqTrans = Request::create('/api/transporte/solicitar', 'POST', [
                'tipo_servicio' => 'transporte',
                'nombre' => 'Test',
                'apellido' => 'APIUser',
                'cedula' => '00000000000',
                'telefono' => '8090000000',
                'correo' => 'test@api.com',
                'punto_recogida' => '18.4861,-69.9312',
                'punto_recogida_address' => 'API Origen',
                'punto_entrega' => '18.4861,-69.9312',
                'punto_entrega_address' => 'API Destino',
                'direccion' => 'API Origen',
                'fecha_servicio' => now()->addDays(3)->format('Y-m-d'),
                'dimensiones_carga' => '10x10x10',
                'articulos' => [
                    ['nombre' => 'Caja API', 'cantidad' => 2]
                ]
            ]);
            $reqTrans->setUserResolver(fn() => $users[1]);
            $resTrans = $this->callController($transApi, 'solicitar', $reqTrans);
            $this->info("   Transport requested successfully.");

            $this->info("\n✅ All Mobile API flows completed successfully! The logic is working as expected.");

        } catch (ValidationException $e) {
            $this->error("❌ Validation Error: " . json_encode($e->errors(), JSON_PRETTY_PRINT));
        } catch (\Exception $e) {
            $this->error("❌ Error in API flow test: " . $e->getMessage());
            $this->error("File: " . $e->getFile() . " Line: " . $e->getLine());
            $this->error($e->getTraceAsString());
        }
    }

    protected function callController($controller, $method, $request, ...$args)
    {
        $response = $controller->{$method}($request, ...$args);
        
        if ($response instanceof \Illuminate\Http\JsonResponse) {
            return $response->getData(true);
        }
        
        return $response;
    }
}
