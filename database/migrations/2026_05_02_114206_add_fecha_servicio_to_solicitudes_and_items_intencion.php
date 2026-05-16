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
        Schema::table('solicitudes_servicio', function (Blueprint $table) {
            $table->dateTime('fecha_servicio')->nullable()->after('cantidad');
        });

        Schema::table('items_intencion_compra', function (Blueprint $table) {
            $table->dateTime('fecha_servicio')->nullable()->after('cantidad');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('solicitudes_servicio', function (Blueprint $table) {
            $table->dropColumn('fecha_servicio');
        });

        Schema::table('items_intencion_compra', function (Blueprint $table) {
            $table->dropColumn('fecha_servicio');
        });
    }
};
