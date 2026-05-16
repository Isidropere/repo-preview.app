<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Optimiza el rendimiento de la base de datos en producción agregando
     * índices faltantes en columnas frecuentemente consultadas.
     */
    public function up(): void
    {
        // 1. Tabla: items (Búsqueda por nombre)
        if (!Schema::hasIndex('items', 'idx_items_nombre')) {
            Schema::table('items', function (Blueprint $table) {
                $table->index('item', 'idx_items_nombre');
            });
        }

        // 2. Tabla: negociaciones (Filtro por estado)
        if (!Schema::hasIndex('negociaciones', 'idx_negociaciones_estado')) {
            Schema::table('negociaciones', function (Blueprint $table) {
                $table->index('estado', 'idx_negociaciones_estado');
            });
        }


        // 3. Tabla: messages (Ordenamiento por fecha)
        if (!Schema::hasIndex('messages', 'idx_messages_created_at')) {
            Schema::table('messages', function (Blueprint $table) {
                $table->index('created_at', 'idx_messages_created_at');
            });
        }

        // 4. Tabla: solicitudes_servicio (Filtro por estado y fecha)
        if (!Schema::hasIndex('solicitudes_servicio', 'idx_solicitudes_estado_fecha')) {
            Schema::table('solicitudes_servicio', function (Blueprint $table) {
                $table->index(['estado', 'fecha_creacion'], 'idx_solicitudes_estado_fecha');
            });
        }

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('items', function (Blueprint $blueprint) {
            $blueprint->dropIndex('idx_items_nombre');
        });

        Schema::table('negociaciones', function (Blueprint $blueprint) {
            $blueprint->dropIndex('idx_negociaciones_estado');
        });

        Schema::table('messages', function (Blueprint $blueprint) {
            $blueprint->dropIndex('idx_messages_created_at');
        });

        Schema::table('solicitudes_servicio', function (Blueprint $blueprint) {
            $blueprint->dropIndex('idx_solicitudes_estado_fecha');
        });

    }
};
