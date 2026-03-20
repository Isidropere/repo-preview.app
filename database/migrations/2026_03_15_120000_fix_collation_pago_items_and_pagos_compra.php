<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }
        // Unificar collation de la FK id_pago_compra en ambas tablas
        // a utf8mb4_unicode_ci (estándar de Laravel)
        DB::statement("ALTER TABLE `pagos_compra`
            MODIFY `id_pago_compra` VARCHAR(255)
            CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL");

        DB::statement("ALTER TABLE `pago_items`
            MODIFY `id_pago_compra` VARCHAR(255)
            CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL");

        // También arreglar compra_trazabilidad si existe la columna
        $cols = DB::select("SHOW COLUMNS FROM `compra_trazabilidad` LIKE 'id_pago_compra'");
        if (!empty($cols)) {
            DB::statement("ALTER TABLE `compra_trazabilidad`
                MODIFY `id_pago_compra` VARCHAR(255)
                CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL");
        }
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE `pagos_compra`
            MODIFY `id_pago_compra` VARCHAR(255)
            CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL");

        DB::statement("ALTER TABLE `pago_items`
            MODIFY `id_pago_compra` VARCHAR(255)
            CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL");
    }
};
