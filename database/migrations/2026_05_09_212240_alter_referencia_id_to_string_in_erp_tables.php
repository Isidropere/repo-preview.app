<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('cont_diario', function (Blueprint $table) {
            $table->string('referencia_id', 50)->nullable()->change();
        });

        Schema::table('inventario_movimientos', function (Blueprint $table) {
            $table->string('referencia_id', 50)->nullable()->change();
        });

        Schema::table('caja_transacciones', function (Blueprint $table) {
            $table->string('referencia_id', 50)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cont_diario', function (Blueprint $table) {
            $table->unsignedBigInteger('referencia_id')->nullable()->change();
        });

        Schema::table('inventario_movimientos', function (Blueprint $table) {
            $table->unsignedBigInteger('referencia_id')->nullable()->change();
        });

        Schema::table('caja_transacciones', function (Blueprint $table) {
            $table->unsignedBigInteger('referencia_id')->nullable()->change();
        });
    }
};
