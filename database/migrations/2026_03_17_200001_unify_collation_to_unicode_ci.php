<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Unifica todas las tablas legacy de utf8mb4_general_ci a utf8mb4_unicode_ci.
 * Esto evita errores de collation en JOINs entre tablas Laravel y legacy.
 */
return new class extends Migration
{
    private array $tablas = [
        'carritos',
        'categorias_item',
        'colors',
        'deliveries',
        'direcciones',
        'distritos_municipales',
        'facturas_transporte_transaccion',
        'imagenes_item',
        'items',
        'items_intencion_compra',
        'items_oferta',
        'item_color',
        'item_views',
        'messages',
        'miembros',
        'municipios',
        'notas',
        'notas_detalles',
        'ofertas',
        'pagos_compra',
        'paquetes',
        'planes',
        'proveedores_pago',
        'provincias',
        'ratings',
        'tarjetas_pagos',
        'tipos_item',
        'tipos_usuarios',
        'usuarios',
    ];

    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        foreach ($this->tablas as $tabla) {
            try {
                DB::statement("ALTER TABLE `{$tabla}` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            } catch (\Exception $e) {
                // Tabla no existe o ya tiene el collation correcto — continuar
            }
        }
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        foreach ($this->tablas as $tabla) {
            try {
                DB::statement("ALTER TABLE `{$tabla}` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
            } catch (\Exception $e) {
                // Tabla no existe — continuar
            }
        }
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
};
