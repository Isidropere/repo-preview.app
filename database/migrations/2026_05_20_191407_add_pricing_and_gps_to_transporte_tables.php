<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('transporte_articulos')) {
            Schema::table('transporte_articulos', function (Blueprint $table) {
                if (!Schema::hasColumn('transporte_articulos', 'precio_base')) {
                    $table->decimal('precio_base', 10, 2)->default(0)->after('categoria');
                }
            });
        }

        if (Schema::hasTable('solicitudes_transporte')) {
            Schema::table('solicitudes_transporte', function (Blueprint $table) {
                if (!Schema::hasColumn('solicitudes_transporte', 'punto_recogida')) {
                    $table->string('punto_recogida')->nullable()->after('ubicacion_geologica');
                }
                if (!Schema::hasColumn('solicitudes_transporte', 'punto_entrega')) {
                    $table->string('punto_entrega')->nullable()->after('punto_recogida');
                }
                if (!Schema::hasColumn('solicitudes_transporte', 'distancia_km')) {
                    $table->decimal('distancia_km', 8, 2)->nullable()->after('punto_entrega');
                }
                if (!Schema::hasColumn('solicitudes_transporte', 'precio_estimado_total')) {
                    $table->decimal('precio_estimado_total', 10, 2)->nullable()->after('distancia_km');
                }
            });
        }

        if (Schema::hasTable('solicitud_transporte_articulo')) {
            Schema::table('solicitud_transporte_articulo', function (Blueprint $table) {
                if (!Schema::hasColumn('solicitud_transporte_articulo', 'dimensiones')) {
                    $table->string('dimensiones')->nullable()->after('cantidad');
                }
                if (!Schema::hasColumn('solicitud_transporte_articulo', 'peso')) {
                    $table->decimal('peso', 8, 2)->nullable()->after('dimensiones');
                }
                if (!Schema::hasColumn('solicitud_transporte_articulo', 'precio_unitario')) {
                    $table->decimal('precio_unitario', 10, 2)->nullable()->after('peso');
                }
                if (!Schema::hasColumn('solicitud_transporte_articulo', 'subtotal')) {
                    $table->decimal('subtotal', 10, 2)->nullable()->after('precio_unitario');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('solicitud_transporte_articulo', function (Blueprint $table) {
            $table->dropColumn(['dimensiones', 'peso', 'precio_unitario', 'subtotal']);
        });

        Schema::table('solicitudes_transporte', function (Blueprint $table) {
            $table->dropColumn(['punto_recogida', 'punto_entrega', 'distancia_km', 'precio_estimado_total']);
        });

        Schema::table('transporte_articulos', function (Blueprint $table) {
            $table->dropColumn('precio_base');
        });
    }
};
