<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('config_tarifa_categoria29', function (Blueprint $table) {
            $table->id();
            $table->decimal('monto_registro', 10, 2)->default(0.00);
            $table->decimal('descuento_venta_masiva', 5, 2)->default(0.00);
            $table->unsignedInteger('cantidad_minima_descuento')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('config_tarifa_categoria29');
    }
};
