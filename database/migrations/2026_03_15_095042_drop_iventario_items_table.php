<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Eliminar la tabla con typo — la tabla correcta es inventario_items
        Schema::dropIfExists('iventario_items');
    }

    public function down(): void
    {
        // Recrear la tabla con typo si se hace rollback
        Schema::create('iventario_items', function (Blueprint $table) {
            $table->integer('id_inventario')->autoIncrement();
            $table->integer('id_item');
            $table->integer('cantidad');
            $table->dateTime('fecha')->nullable();
        });
    }
};
