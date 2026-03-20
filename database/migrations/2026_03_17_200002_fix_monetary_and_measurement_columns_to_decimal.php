<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Cambia columnas double/float a decimal para evitar errores de redondeo
 * en montos monetarios y medidas físicas.
 */
return new class extends Migration
{
    public function up(): void
    {
        // items: valor monetario y dimensiones físicas
        Schema::table('items', function (Blueprint $table) {
            $table->decimal('valor', 12, 2)->nullable()->change();
            $table->decimal('peso_lbs', 8, 2)->default(0)->change();
            $table->decimal('alto_cm', 8, 2)->default(0)->change();
            $table->decimal('ancho_cm', 8, 2)->default(0)->change();
            $table->decimal('profundo_cm', 8, 2)->default(0)->change();
        });

        // facturas_transporte_transaccion: monto de factura
        Schema::table('facturas_transporte_transaccion', function (Blueprint $table) {
            $table->decimal('valor', 10, 2)->change();
        });

        // planes: precio del plan
        Schema::table('planes', function (Blueprint $table) {
            $table->decimal('valor', 10, 2)->change();
        });
    }

    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->double('valor')->nullable()->change();
            $table->float('peso_lbs')->change();
            $table->float('alto_cm')->change();
            $table->float('ancho_cm')->change();
            $table->float('profundo_cm')->change();
        });

        Schema::table('facturas_transporte_transaccion', function (Blueprint $table) {
            $table->double('valor')->change();
        });

        Schema::table('planes', function (Blueprint $table) {
            $table->double('valor')->change();
        });
    }
};
