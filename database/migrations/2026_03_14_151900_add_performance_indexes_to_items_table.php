<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('items', function (Blueprint $table) {
            // Índice compuesto para la query de la home (tipo_trans + estatus + fecha)
            $table->index(['tipo_trans', 'estatus', 'fecha'], 'idx_items_tipo_estatus_fecha');
            // Índice para filtrar por categoría
            $table->index(['id_categoria_item', 'estatus'], 'idx_items_categoria_estatus');
            // Índice para los items del usuario
            $table->index(['id_user', 'estatus'], 'idx_items_user_estatus');
        });

        // Índice en direcciones para buscar por usuario rápido
        Schema::table('direcciones', function (Blueprint $table) {
            $table->index(['id_user', 'es_predeterminada'], 'idx_direcciones_user_predeterminada');
        });
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
