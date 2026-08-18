<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pagos_registro_talento', function (Blueprint $table) {
            $table->id();
            $table->integer('id_item');
            $table->unsignedBigInteger('id_user');
            $table->string('transaction_id', 100);
            $table->decimal('monto_pagado', 10, 2);
            $table->string('estatus', 20)->default('aprobado');
            $table->text('notas')->nullable();
            $table->timestamps();

            $table->foreign('id_item')->references('id_item')->on('items')->onDelete('cascade');
            $table->foreign('id_user')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pagos_registro_talento');
    }
};
