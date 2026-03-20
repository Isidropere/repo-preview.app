<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Agrega transaction_id a pagos_compra para guardar el pnRef/txToken
 * del proveedor de pagos, necesario para anulaciones y reembolsos.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('pagos_compra')) {
            return;
        }
        Schema::table('pagos_compra', function (Blueprint $table) {
            $table->string('transaction_id')->nullable()->after('autorizacion_pago');
        });
    }

    public function down(): void
    {
        Schema::table('pagos_compra', function (Blueprint $table) {
            $table->dropColumn('transaction_id');
        });
    }
};
