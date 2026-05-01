<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Agrega campos de modo de entrega al flujo de intercambio producto↔servicio.
 *
 * modo_entrega:
 *   null      → aún no decidido (solo aplica en producto↔servicio)
 *   'envio'   → el dueño del producto paga envío, admin gestiona
 *   'retiro'  → el que recibe el producto va a retirarlo en persona
 *
 * entrega_confirmada:
 *   false → el receptor aún no confirmó que recibió/retiró el producto
 *   true  → receptor confirmó recepción, intercambio puede completarse
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('negociaciones', function (Blueprint $table) {
            $table->string('modo_entrega', 10)->nullable()->after('pago_receptor');
            $table->boolean('entrega_confirmada')->default(false)->after('modo_entrega');
        });
    }

    public function down(): void
    {
        Schema::table('negociaciones', function (Blueprint $table) {
            $table->dropColumn(['modo_entrega', 'entrega_confirmada']);
        });
    }
};
