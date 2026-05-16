<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ContCuentasSeeder extends Seeder
{
    public function run(): void
    {
        $cuentas = [
            // ACTIVOS (1)
            ['codigo' => '1', 'nombre' => 'ACTIVOS', 'tipo' => 'activo', 'nivel' => 1, 'id_padre' => null, 'permite_movimiento' => false],
            ['codigo' => '1.1', 'nombre' => 'ACTIVOS CORRIENTES', 'tipo' => 'activo', 'nivel' => 2, 'id_padre' => 1, 'permite_movimiento' => false],
            ['codigo' => '1.1.01', 'nombre' => 'EFECTIVO EN CAJA Y BANCOS', 'tipo' => 'activo', 'nivel' => 3, 'id_padre' => 2, 'permite_movimiento' => false],
            ['codigo' => '1.1.01.01', 'nombre' => 'CAJA GENERAL', 'tipo' => 'activo', 'nivel' => 4, 'id_padre' => 3, 'permite_movimiento' => true],
            ['codigo' => '1.1.01.02', 'nombre' => 'BANCO OPERATIVO', 'tipo' => 'activo', 'nivel' => 4, 'id_padre' => 3, 'permite_movimiento' => true],
            ['codigo' => '1.1.02', 'nombre' => 'INVENTARIO DE MERCANCÍAS', 'tipo' => 'activo', 'nivel' => 3, 'id_padre' => 2, 'permite_movimiento' => true],
            
            // PASIVOS (2)
            ['codigo' => '2', 'nombre' => 'PASIVOS', 'tipo' => 'pasivo', 'nivel' => 1, 'id_padre' => null, 'permite_movimiento' => false],
            ['codigo' => '2.1', 'nombre' => 'PASIVOS CORRIENTES', 'tipo' => 'pasivo', 'nivel' => 2, 'id_padre' => 7, 'permite_movimiento' => false],
            ['codigo' => '2.1.01', 'nombre' => 'CUENTAS POR PAGAR', 'tipo' => 'pasivo', 'nivel' => 3, 'id_padre' => 8, 'permite_movimiento' => true],

            // CAPITAL (3)
            ['codigo' => '3', 'nombre' => 'CAPITAL', 'tipo' => 'capital', 'nivel' => 1, 'id_padre' => null, 'permite_movimiento' => false],
            ['codigo' => '3.1', 'nombre' => 'CAPITAL SOCIAL', 'tipo' => 'capital', 'nivel' => 2, 'id_padre' => 10, 'permite_movimiento' => true],

            // INGRESOS (4)
            ['codigo' => '4', 'nombre' => 'INGRESOS', 'tipo' => 'ingreso', 'nivel' => 1, 'id_padre' => null, 'permite_movimiento' => false],
            ['codigo' => '4.1', 'nombre' => 'INGRESOS POR VENTAS', 'tipo' => 'ingreso', 'nivel' => 2, 'id_padre' => 12, 'permite_movimiento' => true],
            ['codigo' => '4.2', 'nombre' => 'INGRESOS POR SERVICIOS', 'tipo' => 'ingreso', 'nivel' => 2, 'id_padre' => 12, 'permite_movimiento' => true],

            // COSTOS (5)
            ['codigo' => '5', 'nombre' => 'COSTOS', 'tipo' => 'costo', 'nivel' => 1, 'id_padre' => null, 'permite_movimiento' => false],
            ['codigo' => '5.1', 'nombre' => 'COSTO DE VENTAS', 'tipo' => 'costo', 'nivel' => 2, 'id_padre' => 15, 'permite_movimiento' => true],

            // GASTOS (6)
            ['codigo' => '6', 'nombre' => 'GASTOS', 'tipo' => 'gasto', 'nivel' => 1, 'id_padre' => null, 'permite_movimiento' => false],
            ['codigo' => '6.1', 'nombre' => 'GASTOS ADMINISTRATIVOS', 'tipo' => 'gasto', 'nivel' => 2, 'id_padre' => 17, 'permite_movimiento' => true],
            ['codigo' => '6.2', 'nombre' => 'GASTOS DE ENVÍO', 'tipo' => 'gasto', 'nivel' => 2, 'id_padre' => 17, 'permite_movimiento' => true],
        ];

        // Insertar uno por uno para asegurar que los IDs coincidan con las referencias id_padre manuales
        // Nota: En un entorno real se usaría búsqueda por código para el id_padre, 
        // pero aquí los insertamos secuencialmente.
        foreach ($cuentas as $index => $c) {
            DB::table('cont_cuentas')->insert([
                'id' => $index + 1,
                'codigo' => $c['codigo'],
                'nombre' => $c['nombre'],
                'tipo' => $c['tipo'],
                'nivel' => $c['nivel'],
                'id_padre' => $c['id_padre'],
                'permite_movimiento' => $c['permite_movimiento'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
