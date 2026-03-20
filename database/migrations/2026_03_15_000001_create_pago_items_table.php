<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Snapshot de los artículos comprados al momento del pago.
 * Los items_intencion_compra se eliminan del carrito al procesar,
 * por lo que necesitamos guardar una copia inmutable aquí.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pago_items', function (Blueprint $table) {
            $table->id();
            $table->string('id_pago_compra');
            $table->unsignedBigInteger('id_item')->nullable(); // nullable por si el item se elimina
            $table->string('nombre_item');                     // snapshot del nombre
            $table->decimal('precio_unitario', 10, 2);        // snapshot del precio
            $table->unsignedSmallInteger('cantidad')->default(1);
            $table->decimal('descuento', 10, 2)->default(0);
            $table->decimal('subtotal', 10, 2);
            $table->string('imagen_url')->nullable();          // snapshot de la URL de imagen
            $table->timestamps();

            $table->index('id_pago_compra');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pago_items');
    }
};
