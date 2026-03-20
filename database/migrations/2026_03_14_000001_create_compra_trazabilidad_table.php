<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('compra_trazabilidad', function (Blueprint $table) {
            $table->id();
            $table->string('id_pago_compra');
            $table->string('estado_anterior')->nullable();
            $table->string('estado_nuevo');
            $table->text('nota')->nullable();
            $table->unsignedBigInteger('id_admin')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('compra_trazabilidad');
    }
};
