<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SystemDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Cuentas Contables (Chart of Accounts)
        $cuentas = [
            ['id' => 1, 'codigo' => '1', 'nombre' => 'ACTIVOS', 'tipo' => 'activo', 'nivel' => 1, 'id_padre' => null, 'permite_movimiento' => 0],
            ['id' => 2, 'codigo' => '1.1', 'nombre' => 'ACTIVOS CORRIENTES', 'tipo' => 'activo', 'nivel' => 2, 'id_padre' => 1, 'permite_movimiento' => 0],
            ['id' => 3, 'codigo' => '1.1.01', 'nombre' => 'EFECTIVO EN CAJA Y BANCOS', 'tipo' => 'activo', 'nivel' => 3, 'id_padre' => 2, 'permite_movimiento' => 0],
            ['id' => 4, 'codigo' => '1.1.01.01', 'nombre' => 'CAJA GENERAL', 'tipo' => 'activo', 'nivel' => 4, 'id_padre' => 3, 'permite_movimiento' => 1],
            ['id' => 5, 'codigo' => '1.1.01.02', 'nombre' => 'BANCO OPERATIVO', 'tipo' => 'activo', 'nivel' => 4, 'id_padre' => 3, 'permite_movimiento' => 1],
            ['id' => 6, 'codigo' => '1.1.02', 'nombre' => 'INVENTARIO DE MERCANCÍAS', 'tipo' => 'activo', 'nivel' => 3, 'id_padre' => 2, 'permite_movimiento' => 1],
            ['id' => 7, 'codigo' => '2', 'nombre' => 'PASIVOS', 'tipo' => 'pasivo', 'nivel' => 1, 'id_padre' => null, 'permite_movimiento' => 0],
            ['id' => 8, 'codigo' => '2.1', 'nombre' => 'PASIVOS CORRIENTES', 'tipo' => 'pasivo', 'nivel' => 2, 'id_padre' => 7, 'permite_movimiento' => 0],
            ['id' => 9, 'codigo' => '2.1.01', 'nombre' => 'CUENTAS POR PAGAR', 'tipo' => 'pasivo', 'nivel' => 3, 'id_padre' => 8, 'permite_movimiento' => 1],
            ['id' => 10, 'codigo' => '3', 'nombre' => 'CAPITAL', 'tipo' => 'capital', 'nivel' => 1, 'id_padre' => null, 'permite_movimiento' => 0],
            ['id' => 11, 'codigo' => '3.1', 'nombre' => 'CAPITAL SOCIAL', 'tipo' => 'capital', 'nivel' => 2, 'id_padre' => 10, 'permite_movimiento' => 1],
            ['id' => 12, 'codigo' => '4', 'nombre' => 'INGRESOS', 'tipo' => 'ingreso', 'nivel' => 1, 'id_padre' => null, 'permite_movimiento' => 0],
            ['id' => 13, 'codigo' => '4.1', 'nombre' => 'INGRESOS POR VENTAS', 'tipo' => 'ingreso', 'nivel' => 2, 'id_padre' => 12, 'permite_movimiento' => 1],
            ['id' => 14, 'codigo' => '4.2', 'nombre' => 'INGRESOS POR SERVICIOS', 'tipo' => 'ingreso', 'nivel' => 2, 'id_padre' => 12, 'permite_movimiento' => 1],
            ['id' => 15, 'codigo' => '5', 'nombre' => 'COSTOS', 'tipo' => 'costo', 'nivel' => 1, 'id_padre' => null, 'permite_movimiento' => 0],
            ['id' => 16, 'codigo' => '5.1', 'nombre' => 'COSTO DE VENTAS', 'tipo' => 'costo', 'nivel' => 2, 'id_padre' => 15, 'permite_movimiento' => 1],
            ['id' => 17, 'codigo' => '6', 'nombre' => 'GASTOS', 'tipo' => 'gasto', 'nivel' => 1, 'id_padre' => null, 'permite_movimiento' => 0],
            ['id' => 18, 'codigo' => '6.1', 'nombre' => 'GASTOS ADMINISTRATIVOS', 'tipo' => 'gasto', 'nivel' => 2, 'id_padre' => 17, 'permite_movimiento' => 1],
            ['id' => 19, 'codigo' => '6.2', 'nombre' => 'GASTOS DE ENVÍO', 'tipo' => 'gasto', 'nivel' => 2, 'id_padre' => 17, 'permite_movimiento' => 1],
        ];

        foreach ($cuentas as $cuenta) {
            DB::table('cont_cuentas')->updateOrInsert(['id' => $cuenta['id']], $cuenta);
        }

        // 2. Configuración de Delivery (Zonas y Porcentajes)
        $deliveryConfigs = [
            [
                'id' => 1, 'clave' => 'cortas', 'porcentaje' => 10.00, 'porcentaje_plataforma' => 2.00, 
                'porcentaje_seguro' => 2.00, 'porcentaje_manejo' => 5.00, 'descripcion' => 'Zonas cortas - porcentaje de utilidad'
            ],
            [
                'id' => 2, 'clave' => 'largas', 'porcentaje' => 10.00, 'porcentaje_plataforma' => 2.00, 
                'porcentaje_seguro' => 2.00, 'porcentaje_manejo' => 5.00, 'descripcion' => 'Zonas largas - porcentaje de utilidad'
            ],
            [
                'id' => 3, 'clave' => 'especiales', 'porcentaje' => 10.00, 'porcentaje_plataforma' => 2.00, 
                'porcentaje_seguro' => 2.00, 'porcentaje_manejo' => 5.00, 'descripcion' => 'Zonas especiales - porcentaje de utilidad'
            ],
            [
                'id' => 4, 'clave' => 'chequeados', 'porcentaje' => 10.00, 'porcentaje_plataforma' => 10.00,
                'porcentaje_seguro' => 10.00, 'porcentaje_manejo' => 6.00, 'descripcion' => 'Bultos chequeados - porcentaje de utilidad'
            ],
        ];

        foreach ($deliveryConfigs as $config) {
            DB::table('delivery_config')->updateOrInsert(['id' => $config['id']], $config);
        }

        echo "✅ Datos de sistema (Cuentas y Configuración) poblados con éxito.\n";
    }
}
