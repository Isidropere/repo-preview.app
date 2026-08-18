<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Item;
use App\Models\Direcciones;
use App\Models\PagoCompra;
use App\Models\TarjetaPago;
use App\Models\PagoItem;
use App\Models\Carrito;
use App\Models\Negociacion;
use App\Models\CuentaBancariaUsuario;
use App\Models\RetiroVendedor;
use App\Services\ERPService;
use App\Services\NegociacionService;

class TestComplexFlowsCommand extends Command
{
    protected $signature = 'test:complex-flows {--fresh : Migrate fresh before running}';
    protected $description = 'Simula un flujo completo (Usuarios, Productos, Ventas, ERP, Intercambios, Mensajes)';

    public function handle(ERPService $erpService, NegociacionService $negociacionService)
    {
        $this->info('🚀 Iniciando Simulación de Flujos Complejos...');

        if ($this->option('fresh')) {
            $this->warn('Ejecutando migrate:fresh...');
            $this->call('migrate:fresh', ['--seed' => true]);
        } else {
            $this->info('Limpiando datos de pruebas anteriores...');
            $this->limpiarDatosAnteriores();
        }

        try {
            DB::beginTransaction();

            // 1. Usuarios
            $this->info('1. Creando 6 Usuarios...');
            $users = $this->crearUsuarios();

            // 2. Productos
            $this->info('2. Creando 10 Productos/Servicios por Usuario (60 Total)...');
            $items = $this->crearProductos($users);

            // 3. Venta y Contabilidad
            $this->info('3. Simulando Venta de Producto y Asiento Contable...');
            $this->simularVentaYContabilidad($users, $items, $erpService);

            // 4. Intercambios
            $this->info('4. Simulando Intercambios y Mensajes...');
            $this->simularIntercambios($users, $items, $negociacionService);

            DB::commit();
            $this->info('✅ Simulación completada exitosamente. Puedes revisar la DB.');

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('❌ Error en la simulación: ' . $e->getMessage());
            $this->error('Línea: ' . $e->getLine() . ' - Archivo: ' . $e->getFile());
        }
    }

    private function limpiarDatosAnteriores()
    {
        // Buscar usuarios de prueba anteriores
        $usuariosTest = User::where('email', 'like', 'testflow%@cambialo.com')->get();
        foreach ($usuariosTest as $user) {
            // Eliminar dependencias manuales si la base de datos no tiene onDelete('cascade')
            Item::where('id_user', $user->id)->delete();
            Direcciones::where('id_user', $user->id)->delete();
            Carrito::where('id_user', $user->id)->delete();
            CuentaBancariaUsuario::where('id_usuario', $user->id)->delete();
            RetiroVendedor::where('id_usuario', $user->id)->delete();
            // Eliminar al usuario final
            $user->delete();
        }
    }

    private function crearUsuarios()
    {
        $users = [];
        for ($i = 1; $i <= 6; $i++) {
            $email = "testflow{$i}@cambialo.com";
            $users[$i] = User::firstOrCreate(['email' => $email], [
                'nombres' => "Usuario{$i}",
                'apellidos' => "FlujoComplejo",
                'nombre_usuario' => "userflow{$i}",
                'password' => bcrypt('12345678'),
                'telefono' => "809-555-000{$i}",
                'estatus' => 1,
                'id_tipo_usuario' => 1, // Vendedor/Comprador
            ]);

            Direcciones::firstOrCreate(['id_user' => $users[$i]->id], [
                'id_provincia' => 1, 'id_municipio' => 1, 'calle' => "Calle Principal $i",
                'numero' => "$i", 'sector' => 'Centro', 'codigo_postal' => '10101'
            ]);
        }
        return $users;
    }

    private function crearProductos($users)
    {
        $items = [];
        foreach ($users as $i => $user) {
            $items[$i] = [];
            for ($j = 1; $j <= 10; $j++) {
                // Alternar tipo: 1=Venta, 2=Intercambio, 3=Ambos
                $tipoTrans = ($j % 3) + 1; 
                // Alternar categoria: 29 = Servicio, 1 = Producto fisico
                $categoria = ($j > 7) ? 29 : 1; 
                
                $item = Item::create([
                    'item' => "Producto/Servicio {$j} de Usuario {$i}",
                    'id_categoria_item' => $categoria,
                    'valor' => rand(500, 5000),
                    'presentacion' => "Descripción detallada del item {$j}",
                    'condicion' => 1,
                    'tipo_trans' => $tipoTrans,
                    'peso_lbs' => ($categoria == 29) ? 0 : 5,
                    'alto_cm' => ($categoria == 29) ? 0 : 10,
                    'ancho_cm' => ($categoria == 29) ? 0 : 10,
                    'profundo_cm' => ($categoria == 29) ? 0 : 10,
                    'cantidad' => rand(1, 10),
                    'estatus' => 1,
                    'estado_aprobacion' => 'aprobado',
                    'id_user' => $user->id,
                ]);
                $items[$i][] = $item;
            }
        }
        return $items;
    }

    private function simularVentaYContabilidad($users, $items, ERPService $erpService)
    {
        // Usuario 1 compra un producto de Venta (tipo_trans 1 o 3) del Usuario 2
        $itemAComprar = collect($items[2])->firstWhere('tipo_trans', '!=', 2);
        
        // Simular un Carrito para la compra
        $carrito = Carrito::firstOrCreate(['id_user' => $users[1]->id], ['tipo' => 'venta']);

        // Crear tarjeta de prueba
        $tarjeta = TarjetaPago::firstOrCreate(['id_tarjeta' => (string) Str::uuid()], [
            'tipo_tarjeta' => 'Visa',
            'banco_tarjeta' => 'Banreservas',
            'mes_expiracion' => '12',
            TarjetaPago::COL_ANIO => '2030',
            'estatus' => '1',
            'last4' => '4242',
            'nombre_titular' => 'Usuario 1',
            'id_user' => $users[1]->id,
        ]);

        // Simular un PagoCompra (Checkout)
        $pago = new PagoCompra();
        $pago->id_pago_compra = (string) Str::uuid();
        $pago->id_carrito = $carrito->id_carrito;
        $pago->id_tarjeta = $tarjeta->id_tarjeta;
        $pago->estatus = 'aprobado';
        $pago->id_proveedor_pago = 1;
        $pago->transaction_id = 'TRANS-' . rand(1000, 9999);
        $pago->total = $itemAComprar->valor;
        $pago->cantidad_items = 1;
        $pago->id_direccion = Direcciones::where('id_user', $users[1]->id)->first()->id_direccion;
        $pago->save();

        // Simular PagoItem (Detalle)
        PagoItem::create([
            'id_pago_compra' => $pago->id_pago_compra,
            'id_item' => $itemAComprar->id_item,
            'nombre_item' => $itemAComprar->item,
            'precio_unitario' => $itemAComprar->valor,
            'cantidad' => 1,
            'subtotal' => $itemAComprar->valor,
            'imagen_url' => 'http://example.com/item.png',
        ]);

        // Disparar ERP para hacer el asiento contable de Venta
        $erpService->procesarVentaAprobada($pago);
        
        $this->info("   - Venta procesada (Monto: RD$ {$pago->total}). Asiento contable generado.");
    }

    private function simularIntercambios($users, $items, NegociacionService $negociacionService)
    {
        // 1. Producto vs Producto (Usuario 3 ofrece a Usuario 4)
        $this->info("   - Creando intercambio: Producto vs Producto (User 3 -> User 4)");
        $itemFisico4 = collect($items[4])->where('id_categoria_item', 1)->firstWhere('tipo_trans', '!=', 1);
        $itemFisico3 = collect($items[3])->where('id_categoria_item', 1)->firstWhere('tipo_trans', '!=', 1);
        
        $neg1 = Negociacion::create([
            'receptor_item_id' => $itemFisico4->id_item,
            'usuario_emisor_id' => $users[3]->id,
            'usuario_receptor_id' => $users[4]->id,
            'items_ofrecidos' => [$itemFisico3->id_item],
            'estado' => 'contraoferta',
            'fecha_creacion' => now(),
            'mensaje_inicial' => 'Te propongo un intercambio.',
        ]);
        
        // Simular Mensaje y Contraoferta
        $negociacionService->crearMensaje($users[3]->id, $users[4]->id, $itemFisico4->id_item, null, 'Te cambio mi producto por el tuyo.');
        $neg1->update(['monto_contra_oferta' => 500, 'estado' => 'aceptado']);
        $negociacionService->crearMensaje($users[4]->id, $users[3]->id, $itemFisico4->id_item, null, 'Acepto pero me pagas RD$ 500 de diferencia.');
        
        // 2. Servicio vs Producto (Usuario 5 ofrece Servicio a Usuario 6)
        $this->info("   - Creando intercambio: Servicio vs Producto (User 5 -> User 6)");
        $itemFisico6 = collect($items[6])->where('id_categoria_item', 1)->firstWhere('tipo_trans', '!=', 1);
        $itemServicio5 = collect($items[5])->where('id_categoria_item', 29)->firstWhere('tipo_trans', '!=', 1);
        
        $neg2 = Negociacion::create([
            'receptor_item_id' => $itemFisico6->id_item,
            'usuario_emisor_id' => $users[5]->id,
            'usuario_receptor_id' => $users[6]->id,
            'items_ofrecidos' => [$itemServicio5->id_item],
            'estado' => 'aceptado',
            'fecha_creacion' => now(),
            'emisor_confirmado' => true,
            'receptor_confirmado' => true,
            'mensaje_inicial' => 'Servicio por tu producto.',
        ]);
        $negociacionService->crearMensaje($users[5]->id, $users[6]->id, $itemFisico6->id_item, null, 'Te ofrezco mi talento/servicio por tu producto físico.');

        // 3. Servicio vs Servicio (Usuario 1 ofrece Servicio a Usuario 2)
        $this->info("   - Creando intercambio: Servicio vs Servicio (User 1 -> User 2)");
        $itemServicio2 = collect($items[2])->where('id_categoria_item', 29)->firstWhere('tipo_trans', '!=', 1);
        $itemServicio1 = collect($items[1])->where('id_categoria_item', 29)->firstWhere('tipo_trans', '!=', 1);
        
        $neg3 = Negociacion::create([
            'receptor_item_id' => $itemServicio2->id_item,
            'usuario_emisor_id' => $users[1]->id,
            'usuario_receptor_id' => $users[2]->id,
            'items_ofrecidos' => [$itemServicio1->id_item],
            'estado' => 'aceptado',
            'fecha_creacion' => now(),
            'mensaje_inicial' => 'Servicio por servicio.',
        ]);
        
        // Simular retiro hacia Billetera (para el Usuario 4 que recibió la compensación de 500)
        $this->info("   - Registrando Cuenta Bancaria y Solicitud de Retiro para User 4");
        $cuenta = CuentaBancariaUsuario::create([
            'id_usuario' => $users[4]->id,
            'banco' => 'Banreservas',
            'tipo_cuenta' => 'ahorro',
            'numero_cuenta' => '1234567890',
            'titular' => 'Usuario 4',
            'cedula_titular' => '40200000000',
        ]);
        
        RetiroVendedor::create([
            'id_usuario' => $users[4]->id,
            'monto' => 500,
            'id_cuenta_bancaria' => $cuenta->id,
            'estado' => 'pendiente',
        ]);
    }
}
