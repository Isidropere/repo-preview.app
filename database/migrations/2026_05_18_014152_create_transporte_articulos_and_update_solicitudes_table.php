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
        // 1. Crear tabla de catálogo de artículos
        Schema::create('transporte_articulos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->enum('categoria', ['transporte', 'mudanza', 'ambos'])->default('ambos');
            $table->boolean('estatus')->default(true);
            $table->timestamps();
        });

        // 2. Modificar solicitudes_transporte para añadir tipo_servicio
        Schema::table('solicitudes_transporte', function (Blueprint $table) {
            $table->enum('tipo_servicio', ['transporte', 'mudanza'])->default('transporte')->after('id_usuario');
        });

        // 3. Crear tabla pivot solicitud_transporte_articulo
        Schema::create('solicitud_transporte_articulo', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('solicitud_transporte_id');
            $table->unsignedBigInteger('articulo_id');
            $table->integer('cantidad')->default(1);
            $table->timestamps();

            // Claves foráneas con eliminación en cascada
            $table->foreign('solicitud_transporte_id', 'fk_sol_trans_art_sol')
                  ->references('id')->on('solicitudes_transporte')
                  ->onDelete('cascade');
            
            $table->foreign('articulo_id', 'fk_sol_trans_art_art')
                  ->references('id')->on('transporte_articulos')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('solicitud_transporte_articulo');

        Schema::table('solicitudes_transporte', function (Blueprint $table) {
            $table->dropColumn('tipo_servicio');
        });

        Schema::dropIfExists('transporte_articulos');
    }
};

