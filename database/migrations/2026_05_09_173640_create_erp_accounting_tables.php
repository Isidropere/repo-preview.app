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
        // 1. Catálogo de Cuentas
        Schema::create('cont_cuentas', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 20)->unique();
            $table->string('nombre', 100);
            $table->enum('tipo', ['activo', 'pasivo', 'capital', 'ingreso', 'gasto', 'costo']);
            $table->integer('nivel')->default(1);
            $table->unsignedBigInteger('id_padre')->nullable();
            $table->boolean('permite_movimiento')->default(true);
            $table->timestamps();

            $table->foreign('id_padre')->references('id')->on('cont_cuentas')->onDelete('cascade');
        });

        // 2. Libro Diario (Encabezado)
        Schema::create('cont_diario', function (Blueprint $table) {
            $table->id();
            $table->date('fecha');
            $table->string('concepto', 255);
            $table->decimal('total_debe', 15, 2)->default(0);
            $table->decimal('total_haber', 15, 2)->default(0);
            $table->string('referencia_tipo', 50)->nullable(); // ej: 'pago_compra', 'pago_envio'
            $table->unsignedBigInteger('referencia_id')->nullable();
            $table->enum('estado', ['borrador', 'asentado', 'anulado'])->default('asentado');
            $table->unsignedBigInteger('id_usuario_crea');
            $table->timestamps();

            $table->foreign('id_usuario_crea')->references('id')->on('users');
        });

        // 3. Detalle de Asiento Diario
        Schema::create('cont_diario_detalles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_diario');
            $table->unsignedBigInteger('id_cuenta');
            $table->decimal('debe', 15, 2)->default(0);
            $table->decimal('haber', 15, 2)->default(0);
            $table->string('nota', 255)->nullable();
            $table->timestamps();

            $table->foreign('id_diario')->references('id')->on('cont_diario')->onDelete('cascade');
            $table->foreign('id_cuenta')->references('id')->on('cont_cuentas');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cont_diario_detalles');
        Schema::dropIfExists('cont_diario');
        Schema::dropIfExists('cont_cuentas');
    }
};
