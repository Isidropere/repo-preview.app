<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('solicitudes_servicio')) {
            Schema::create('solicitudes_servicio', function (Blueprint $table) {
                $table->id('id_solicitud');
                $table->unsignedBigInteger('id_comprador');
                $table->unsignedBigInteger('id_proveedor');
                $table->integer('id_item');
                $table->integer('id_carrito');
                $table->unsignedInteger('cantidad')->default(1);
                $table->decimal('monto_total', 10, 2);
                $table->string('estado', 30)->default('pendiente_aprobacion');
                $table->timestamp('fecha_creacion')->useCurrent();
                $table->timestamp('fecha_actualizacion')->nullable();

                $table->index(['id_proveedor', 'estado'], 'idx_proveedor_estado');
                $table->index(['id_comprador', 'estado'], 'idx_comprador_estado');
                $table->index('id_item', 'idx_item');

                $table->foreign('id_comprador')->references('id')->on('users');
                $table->foreign('id_proveedor')->references('id')->on('users');
                $table->foreign('id_item')->references('id_item')->on('items');
                $table->foreign('id_carrito')->references('id_carrito')->on('carritos');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('solicitudes_servicio');
    }
};
