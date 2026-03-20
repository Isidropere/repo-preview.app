<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('delivery_config', function (Blueprint $table) {
            // 3 campos de gestión del negocio que se suman al precio base del proveedor
            $table->decimal('porcentaje_plataforma', 5, 2)->default(0)->after('porcentaje')
                ->comment('% que cobra la plataforma por gestión del envío');
            $table->decimal('porcentaje_seguro', 5, 2)->default(0)->after('porcentaje_plataforma')
                ->comment('% de seguro sobre el valor del artículo');
            $table->decimal('porcentaje_manejo', 5, 2)->default(0)->after('porcentaje_seguro')
                ->comment('% de manejo y empaque');
        });

        // Valores iniciales razonables
        DB::table('delivery_config')->update([
            'porcentaje_plataforma' => 5.00,
            'porcentaje_seguro'     => 2.00,
            'porcentaje_manejo'     => 3.00,
        ]);
    }

    public function down(): void
    {
        Schema::table('delivery_config', function (Blueprint $table) {
            $table->dropColumn(['porcentaje_plataforma', 'porcentaje_seguro', 'porcentaje_manejo']);
        });
    }
};
