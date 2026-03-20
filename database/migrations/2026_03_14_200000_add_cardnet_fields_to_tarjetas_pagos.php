<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Agrega campos necesarios para la integración con CardNet/Ztrans.
 *
 * - cvv_hash:          CVV hasheado (nunca en texto plano por PCI-DSS)
 * - payment_method_id: pm_xxx de Stripe (retrocompatibilidad)
 * - last4:             Últimos 4 dígitos visibles
 * - nombre_titular:    Nombre en la tarjeta
 * - usar_esta_tarjeta: Tarjeta predeterminada del usuario
 * - id_user:           FK al usuario (la tabla original usa id_miembro)
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('tarjetas_pagos')) {
            return;
        }
        Schema::table('tarjetas_pagos', function (Blueprint $table) {
            if (!Schema::hasColumn('tarjetas_pagos', 'payment_method_id')) {
                $table->string('payment_method_id')->nullable();
            }
            if (!Schema::hasColumn('tarjetas_pagos', 'last4')) {
                $table->string('last4', 4)->nullable();
            }
            if (!Schema::hasColumn('tarjetas_pagos', 'nombre_titular')) {
                $table->string('nombre_titular')->nullable();
            }
            if (!Schema::hasColumn('tarjetas_pagos', 'usar_esta_tarjeta')) {
                $table->boolean('usar_esta_tarjeta')->default(false);
            }
            if (!Schema::hasColumn('tarjetas_pagos', 'id_user')) {
                $table->unsignedBigInteger('id_user')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('tarjetas_pagos', function (Blueprint $table) {
            $table->dropColumn([
                'payment_method_id',
                'last4',
                'nombre_titular',
                'usar_esta_tarjeta',
                'id_user',
            ]);
        });
    }
};
