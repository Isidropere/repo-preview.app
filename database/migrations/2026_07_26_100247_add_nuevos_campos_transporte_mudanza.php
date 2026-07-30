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
        Schema::table('solicitudes_transporte', function (Blueprint $table) {
            $table->string('camion_tamano')->nullable()->after('tipo_servicio');
            $table->integer('cantidad_personas')->nullable()->after('camion_tamano');
            $table->decimal('distancia_base_a_origen', 10, 2)->nullable()->after('distancia_km');
            $table->integer('cantidad_productos_transporte')->nullable()->after('dimensiones_carga');
            $table->decimal('peso_carga', 10, 2)->nullable()->after('cantidad_productos_transporte');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('solicitudes_transporte', function (Blueprint $table) {
            $table->dropColumn([
                'camion_tamano',
                'cantidad_personas',
                'distancia_base_a_origen',
                'cantidad_productos_transporte',
                'peso_carga'
            ]);
        });
    }
};
