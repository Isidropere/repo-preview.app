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
        foreach ($this->tablas as $tabla) {
            DB::statement("ALTER TABLE `{$tabla}` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        }
    }

    public function down(): void
    {
        foreach ($this->tablas as $tabla) {
            DB::statement("ALTER TABLE `{$tabla}` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
        }
    }
};
