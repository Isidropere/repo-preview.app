<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Reduce varchar(100) a tamaños reales en direcciones.
 * id_provincia es código de 2 chars, id_municipio es código de 5 chars.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('direcciones')) {
            return;
        }
        \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=0');
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE `direcciones` MODIFY `id_provincia` varchar(5) NOT NULL");
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE `direcciones` MODIFY `id_municipio` varchar(10) NULL");
        \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    public function down(): void
    {
        \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=0');
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE `direcciones` MODIFY `id_provincia` varchar(100) NOT NULL");
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE `direcciones` MODIFY `id_municipio` varchar(100) NULL");
        \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
};
