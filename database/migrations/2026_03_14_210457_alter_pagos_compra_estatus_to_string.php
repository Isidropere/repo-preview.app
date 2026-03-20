<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Cambiar estatus de tinyint a varchar con valor por defecto 'pendiente'
        DB::statement("ALTER TABLE pagos_compra MODIFY COLUMN estatus VARCHAR(30) NOT NULL DEFAULT 'pendiente'");

        // 2. Convertir valores numéricos existentes a strings legibles
        DB::statement("UPDATE pagos_compra SET estatus = CASE
            WHEN estatus = '0' THEN 'pendiente'
            WHEN estatus = '1' THEN 'aprobado'
            WHEN estatus = '2' THEN 'rechazado'
            WHEN estatus = '3' THEN 'enviado'
            WHEN estatus = '4' THEN 'entregado'
            WHEN estatus = '5' THEN 'cancelado'
            ELSE 'pendiente'
        END");

        // 3. Agregar columnas para preservar datos del pedido al momento del pago
        Schema::table('pagos_compra', function (Blueprint $table) {
            if (!Schema::hasColumn('pagos_compra', 'total')) {
                $table->decimal('total', 10, 2)->nullable()->after('estatus');
            }
            if (!Schema::hasColumn('pagos_compra', 'cantidad_items')) {
                $table->unsignedSmallInteger('cantidad_items')->default(0)->after('total');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pagos_compra', function (Blueprint $table) {
            if (Schema::hasColumn('pagos_compra', 'total')) {
                $table->dropColumn('total');
            }
            if (Schema::hasColumn('pagos_compra', 'cantidad_items')) {
                $table->dropColumn('cantidad_items');
            }
        });

        DB::statement("ALTER TABLE pagos_compra MODIFY COLUMN estatus TINYINT(3) DEFAULT NULL");
    }
};
