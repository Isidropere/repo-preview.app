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
            $table->string('piso_origen')->nullable()->after('punto_recogida');
            $table->string('piso_destino')->nullable()->after('punto_entrega');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('solicitudes_transporte', function (Blueprint $table) {
            $table->dropColumn(['piso_origen', 'piso_destino']);
        });
    }
};
