<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('items')) {
            Schema::table('items', function (Blueprint $table) {
                $table->index(['tipo_trans', 'estatus', 'fecha'], 'idx_items_tipo_estatus_fecha');
                $table->index(['id_categoria_item', 'estatus'], 'idx_items_categoria_estatus');
                $table->index(['id_user', 'estatus'], 'idx_items_user_estatus');
            });
        }

        if (Schema::hasTable('direcciones')) {
            Schema::table('direcciones', function (Blueprint $table) {
                $table->index(['id_user', 'es_predeterminada'], 'idx_direcciones_user_predeterminada');
            });
        }
    }

    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropIndex('idx_items_tipo_estatus_fecha');
            $table->dropIndex('idx_items_categoria_estatus');
            $table->dropIndex('idx_items_user_estatus');
        });

        Schema::table('direcciones', function (Blueprint $table) {
            $table->dropIndex('idx_direcciones_user_predeterminada');
        });
    }
};
