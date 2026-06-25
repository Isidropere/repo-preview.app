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
        Schema::table('pagos_compra', function (Blueprint $table) {
            $table->decimal('impuestos', 10, 2)->default(0.00)->after('total');
            $table->decimal('costo_envio', 10, 2)->default(0.00)->after('impuestos');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pagos_compra', function (Blueprint $table) {
            $table->dropColumn(['impuestos', 'costo_envio']);
        });
    }
};
