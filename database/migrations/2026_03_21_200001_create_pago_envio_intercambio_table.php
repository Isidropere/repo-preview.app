<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pago_envio_intercambio', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('id_negociacion');
            $table->unsignedBigInteger('id_user');
            $table->decimal('monto', 10, 2)->default(0.00);
            $table->enum('tipo_pago', ['tarjeta', 'pull']);
            $table->enum('estado', ['pendiente', 'pagado', 'pagado_pull'])->default('pendiente');
            $table->char('id_tarjeta', 36)->nullable();
            $table->string('transaction_id', 255)->nullable();
            $table->string('approval_code', 255)->nullable();
            $table->unsignedBigInteger('id_pago_registro_talento')->nullable();
            $table->timestamps();

            $table->foreign('id_negociacion')
                ->references('id_negociacion')->on('negociaciones')
                ->onDelete('cascade');

            $table->foreign('id_user')
                ->references('id')->on('users')
                ->onDelete('cascade');

            // id_tarjeta: no FK due to collation mismatch with tarjetas_pagos (general_ci)
            // id_pago_registro_talento: FK to pagos_registro_talento
            $table->foreign('id_pago_registro_talento')
                ->references('id')->on('pagos_registro_talento')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pago_envio_intercambio');
    }
};
