<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('tarjetas_pagos')) {
            return;
        }
        Schema::table('tarjetas_pagos', function (Blueprint $table) {
            if (!Schema::hasColumn('tarjetas_pagos', 'no_tarjeta')) {
                $table->string('no_tarjeta', 19)->nullable()->after('id_tarjeta');
            }

            // payment_method_id puede ser null (CardNet no lo usa)
            $table->string('payment_method_id', 100)->nullable()->change();

            // last4 como string para guardar "0001", "1234", etc.
            $table->string('last4', 4)->nullable()->change();

            // nombre_titular puede ser null si no se provee
            $table->string('nombre_titular', 60)->nullable()->change();

            // usar_esta_tarjeta con default 0
            $table->tinyInteger('usar_esta_tarjeta')->default(0)->change();
        });
    }

    public function down(): void
    {
        Schema::table('tarjetas_pagos', function (Blueprint $table) {
            if (Schema::hasColumn('tarjetas_pagos', 'no_tarjeta')) {
                $table->dropColumn('no_tarjeta');
            }
            $table->string('payment_method_id', 100)->nullable(false)->change();
            $table->integer('last4')->nullable(false)->change();
            $table->string('nombre_titular', 60)->nullable(false)->change();
        });
    }
};
