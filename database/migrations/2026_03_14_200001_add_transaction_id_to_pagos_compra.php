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
        Schema::table('pagos_compra', function (Blueprint $table) {
            // ID de transacción del proveedor (pnRef en CardNet, pi_xxx en Stripe)
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
