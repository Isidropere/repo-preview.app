<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Corrige typo en PK: id_item_itencion_compra → id_item_intencion_compra
 */
return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }
        // Renombrar columna con typo
        DB::statement('ALTER TABLE `items_intencion_compra` CHANGE `id_item_itencion_compra` `id_item_intencion_compra` INT(11) NOT NULL AUTO_INCREMENT');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE `items_intencion_compra` CHANGE `id_item_intencion_compra` `id_item_itencion_compra` INT(11) NOT NULL AUTO_INCREMENT');
    }
};
