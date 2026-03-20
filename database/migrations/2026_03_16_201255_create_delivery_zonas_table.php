<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_zonas', function (Blueprint $table) {
            $table->id();
            $table->string('zona');                        // Nombre de la zona (ej: "Cibao Corto")
            $table->string('tipo')->default('corta');      // corta | larga | especial | chequeado
            $table->json('pueblos');                       // Lista de pueblos que cubre
            $table->decimal('precio_empresa', 10, 2);      // Precio para empresas/instituciones
            $table->decimal('precio_persona', 10, 2);      // Precio para personas físicas
            $table->string('dias_entrega')->nullable();    // Días de entrega
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        // Tabla de configuración de porcentajes de ganancia del negocio
        Schema::create('delivery_config', function (Blueprint $table) {
            $table->id();
            $table->string('clave')->unique();             // cortas | largas | especiales
            $table->decimal('porcentaje', 5, 2);           // Porcentaje de ganancia
            $table->string('descripcion')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_config');
        Schema::dropIfExists('delivery_zonas');
    }
};
