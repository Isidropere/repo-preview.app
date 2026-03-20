<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Elimina carritos.cantidad — es redundante porque la cantidad real
 * está en items_intencion_compra.cantidad por cada item.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('carritos', function (Blueprint $table) {
            $table->dropColumn('cantidad');
        });
    }

    public function down(): void
    {
        Schema::table('carritos', function (Blueprint $table) {
            $table->integer('cantidad')->default(1)->after('fecha_actualizacion');
        });
    }
};
