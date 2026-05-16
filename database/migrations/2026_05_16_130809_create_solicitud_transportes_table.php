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
        Schema::create('solicitudes_transporte', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_usuario')->nullable(); // Si el usuario está logueado
            $table->string('nombre');
            $table->string('apellido');
            $table->string('cedula');
            $table->string('direccion');
            $table->string('telefono');
            $table->string('correo');
            $table->date('fecha_servicio');
            $table->string('ubicacion_geologica')->nullable(); // GPS coords
            $table->text('dimensiones_carga');
            $table->enum('estado', ['pendiente', 'aprobada', 'rechazada'])->default('pendiente');
            $table->timestamps();

            // Opcional: Relación con la tabla users (o ususarios si se llama diferente en este proyecto)
            // Asumo que la tabla de usuarios se llama users. Si es necesario, lo corrijo luego.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('solicitudes_transporte');
    }
};
