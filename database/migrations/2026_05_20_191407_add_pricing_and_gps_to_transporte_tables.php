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
        Schema::table('transporte_articulos', function (Blueprint $table) {
            $table->decimal('precio_base', 10, 2)->default(0)->after('categoria');
        });

        Schema::table('solicitudes_transporte', function (Blueprint $table) {
            $table->string('punto_recogida')->nullable()->after('ubicacion_geologica');
            $table->string('punto_entrega')->nullable()->after('punto_recogida');
            $table->decimal('distancia_km', 8, 2)->nullable()->after('punto_entrega');
            $table->decimal('precio_estimado_total', 10, 2)->nullable()->after('distancia_km');
        });

        Schema::table('solicitud_transporte_articulo', function (Blueprint $table) {
            $table->string('dimensiones')->nullable()->after('cantidad');
            $table->decimal('peso', 8, 2)->nullable()->after('dimensiones');
            $table->decimal('precio_unitario', 10, 2)->nullable()->after('peso');
            $table->decimal('subtotal', 10, 2)->nullable()->after('precio_unitario');
        });
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
