<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Ejecuta las migraciones.
     */
    public function up(): void
    {
        // Tabla para almacenar trabajos en cola
        Schema::create('jobs', function (Blueprint $table) {
            $table->id(); // ID único del trabajo (clave primaria)
            $table->string('queue')->index(); // Nombre de la cola a la que pertenece el trabajo (indexado para búsquedas rápidas)
            $table->longText('payload'); // Datos serializados del trabajo (información necesaria para ejecutarlo)
            $table->unsignedTinyInteger('attempts'); // Número de intentos realizados para procesar el trabajo
            $table->unsignedInteger('reserved_at')->nullable(); // Marca de tiempo de cuándo el trabajo fue reservado (puede ser nulo)
            $table->unsignedInteger('available_at'); // Marca de tiempo de cuándo el trabajo estará disponible para ser procesado
            $table->unsignedInteger('created_at'); // Marca de tiempo de cuándo se creó el trabajo
        });

        // Tabla para gestionar lotes de trabajos (batches)
        Schema::create('job_batches', function (Blueprint $table) {
            $table->string('id')->primary(); // ID único del lote (clave primaria)
            $table->string('name'); // Nombre descriptivo del lote de trabajos
            $table->integer('total_jobs'); // Número total de trabajos en el lote
            $table->integer('pending_jobs'); // Número de trabajos pendientes en el lote
            $table->integer('failed_jobs'); // Número de trabajos fallidos en el lote
            $table->longText('failed_job_ids'); // Lista de IDs de trabajos fallidos (serializados)
            $table->mediumText('options')->nullable(); // Opciones adicionales del lote (puede ser nulo)
            $table->integer('cancelled_at')->nullable(); // Marca de tiempo de cuándo se canceló el lote (puede ser nulo)
            $table->integer('created_at'); // Marca de tiempo de cuándo se creó el lote
            $table->integer('finished_at')->nullable(); // Marca de tiempo de cuándo se finalizó el lote (puede ser nulo)
        });

        // Tabla para almacenar trabajos fallidos
        Schema::create('failed_jobs', function (Blueprint $table) {
            $table->id(); // ID único del trabajo fallido (clave primaria)
            $table->string('uuid')->unique(); // UUID único del trabajo fallido
            $table->text('connection'); // Nombre de la conexión utilizada para el trabajo
            $table->text('queue'); // Nombre de la cola a la que pertenecía el trabajo
            $table->longText('payload'); // Datos serializados del trabajo (información necesaria para ejecutarlo)
            $table->longText('exception'); // Detalles de la excepción que causó el fallo
            $table->timestamp('failed_at')->useCurrent(); // Marca de tiempo de cuándo falló el trabajo (se usa la fecha/hora actual por defecto)
        });
    }

    /**
     * Revierte las migraciones.
     */
    public function down(): void
    {
        // Elimina las tablas si existen
        Schema::dropIfExists('jobs');
        Schema::dropIfExists('job_batches');
        Schema::dropIfExists('failed_jobs');
    }
};
