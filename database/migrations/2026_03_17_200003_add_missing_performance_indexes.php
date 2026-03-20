<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Agrega índices faltantes para las queries más frecuentes del sistema.
 */
return new class extends Migration
{
    public function up(): void
    {
        // items: query del home (estatus + tipo_trans + fecha)
        Schema::table('items', function (Blueprint $table) {
            $table->index(['estatus', 'tipo_trans', 'fecha'], 'idx_items_estatus_tipo_fecha');
            $table->index(['id_user', 'estatus'], 'idx_items_user_estatus');
        });

        // items_intencion_compra: checkout filtra seleccionados por carrito
        if (Schema::hasColumn('items_intencion_compra', 'es_seleccionado')) {
            Schema::table('items_intencion_compra', function (Blueprint $table) {
                $table->index(['id_carrito', 'es_seleccionado'], 'idx_iic_carrito_seleccionado');
            });
        }

        // pagos_compra: historial ordena por fecha, admin filtra por estatus
        Schema::table('pagos_compra', function (Blueprint $table) {
            $table->index('fecha', 'idx_pagos_compra_fecha');
            $table->index('estatus', 'idx_pagos_compra_estatus');
        });

        // negociaciones: historial de negociaciones por usuario
        if (Schema::hasTable('negociaciones')) {
            Schema::table('negociaciones', function (Blueprint $table) {
                $table->index('usuario_emisor_id', 'idx_negociaciones_emisor');
                $table->index('usuario_receptor_id', 'idx_negociaciones_receptor');
                $table->index('receptor_item_id', 'idx_negociaciones_item');
            });
        }

        // imagenes_item: siempre se cargan ordenadas por item
        Schema::table('imagenes_item', function (Blueprint $table) {
            $table->index(['id_item', 'orden_visualizacion'], 'idx_imagenes_item_orden');
        });

        // item_views: estadísticas de vistas por item
        Schema::table('item_views', function (Blueprint $table) {
            $table->index(['id_item', 'created_at'], 'idx_item_views_item_fecha');
        });

        // direcciones: buscar predeterminada del usuario
        Schema::table('direcciones', function (Blueprint $table) {
            $table->index(['id_user', 'es_predeterminada'], 'idx_direcciones_user_predet');
        });
    }

    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropIndex('idx_items_estatus_tipo_fecha');
            $table->dropIndex('idx_items_user_estatus');
        });

        if (Schema::hasColumn('items_intencion_compra', 'es_seleccionado')) {
            Schema::table('items_intencion_compra', function (Blueprint $table) {
                $table->dropIndex('idx_iic_carrito_seleccionado');
            });
        }

        Schema::table('pagos_compra', function (Blueprint $table) {
            $table->dropIndex('idx_pagos_compra_fecha');
            $table->dropIndex('idx_pagos_compra_estatus');
        });

        if (Schema::hasTable('negociaciones')) {
            Schema::table('negociaciones', function (Blueprint $table) {
                $table->dropIndex('idx_negociaciones_emisor');
                $table->dropIndex('idx_negociaciones_receptor');
                $table->dropIndex('idx_negociaciones_item');
            });
        }

        Schema::table('imagenes_item', function (Blueprint $table) {
            $table->dropIndex('idx_imagenes_item_orden');
        });

        Schema::table('item_views', function (Blueprint $table) {
            $table->dropIndex('idx_item_views_item_fecha');
        });

        Schema::table('direcciones', function (Blueprint $table) {
            $table->dropIndex('idx_direcciones_user_predet');
        });
    }
};
