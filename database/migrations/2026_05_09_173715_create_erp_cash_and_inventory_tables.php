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
        // 1. Almacenes (Solo uno central por ahora, pero escalable)
        Schema::create('almacenes', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->string('ubicacion', 255)->nullable();
            $table->boolean('estado')->default(true);
            $table->timestamps();
        });

        // 2. Movimientos de Inventario (Kardex)
        Schema::create('inventario_movimientos', function (Blueprint $table) {
            $table->id();
            $table->integer('id_item');
            $table->unsignedBigInteger('id_almacen');
            $table->enum('tipo', ['entrada', 'salida', 'ajuste']);
            $table->decimal('cantidad', 10, 2);
            $table->decimal('costo_unitario', 15, 2)->default(0);
            $table->string('motivo', 255);
            $table->string('referencia_tipo', 50)->nullable(); // ej: 'pago_compra', 'ajuste_manual'
            $table->unsignedBigInteger('referencia_id')->nullable();
            $table->timestamps();

            $table->foreign('id_item')->references('id_item')->on('items');
            $table->foreign('id_almacen')->references('id')->on('almacenes');
        });

        // 3. Sesiones de Caja Única
        Schema::create('caja_sesiones', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_usuario_abre');
            $table->unsignedBigInteger('id_usuario_cierra')->nullable();
            $table->dateTime('fecha_apertura');
            $table->dateTime('fecha_cierre')->nullable();
            $table->decimal('monto_inicial', 15, 2)->default(0);
            $table->decimal('monto_final_esperado', 15, 2)->default(0);
            $table->decimal('monto_final_real', 15, 2)->default(0);
            $table->decimal('diferencia', 15, 2)->default(0);
            $table->text('nota')->nullable();
            $table->enum('estado', ['abierta', 'cerrada'])->default('abierta');
            $table->timestamps();

            $table->foreign('id_usuario_abre')->references('id')->on('users');
            $table->foreign('id_usuario_cierra')->references('id')->on('users');
        });

        // 4. Transacciones de Caja (Ingresos/Egresos directos)
        Schema::create('caja_transacciones', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_sesion');
            $table->enum('tipo', ['ingreso', 'egreso']);
            $table->decimal('monto', 15, 2);
            $table->string('concepto', 255);
            $table->string('referencia_tipo', 50)->nullable(); // ej: 'pago_compra'
            $table->unsignedBigInteger('referencia_id')->nullable();
            $table->timestamps();

            $table->foreign('id_sesion')->references('id')->on('caja_sesiones')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('caja_transacciones');
        Schema::dropIfExists('caja_sesiones');
        Schema::dropIfExists('inventario_movimientos');
        Schema::dropIfExists('almacenes');
    }
};
