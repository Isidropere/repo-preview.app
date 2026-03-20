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
        Schema::table('direcciones', function (Blueprint $table) {
            $table->string('id_provincia', 5)->change();   // era varchar(100), real: '01'-'32'
            $table->string('id_municipio', 10)->nullable()->change(); // era varchar(100), real: '01-01'
        });
    }

    public function down(): void
    {
        Schema::table('direcciones', function (Blueprint $table) {
            $table->string('id_provincia', 100)->change();
            $table->string('id_municipio', 100)->nullable()->change();
        });
    }
};
