<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('empleos', function (Blueprint $table) {
            $table->id();
            $table->string('titulo');
            $table->text('descripcion');
            $table->text('requisitos');
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        // Insert initial default values
        DB::table('empleos')->insert([
            [
                'titulo' => 'Asistente de Operaciones y Logística',
                'descripcion' => 'Buscamos a una persona organizada y proactiva para coordinar el flujo de entrega de paquetes y dar soporte en la comunicación con nuestros partners de mensajería. El candidato ideal asegurará que las compras e intercambios se entreguen a tiempo y en óptimas condiciones.',
                'requisitos' => "Requisitos del puesto:\n- Experiencia previa en puestos de logística o atención al cliente (deseable).\n- Habilidades avanzadas de comunicación y resolución de conflictos.\n- Manejo intermedio de herramientas de oficina (Excel, Google Sheets).\n\n¿Cómo aplicar?\nEnvía tu currículum (CV) actualizado a cambialord.com@gmail.com con el asunto \"Vacante - Asistente de Operaciones\".",
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'titulo' => 'Especialista en Marketing Digital y Comunidad',
                'descripcion' => '¿Te apasiona la sostenibilidad y el consumo consciente? Buscamos un creador de contenido y gestor de comunidades para manejar nuestras redes sociales (Instagram, Facebook) y potenciar nuestra misión de economía circular \"Si no puedes venderlo, ¡cámbialo!\".',
                'requisitos' => "Requisitos del puesto:\n- Experiencia en gestión de comunidades (Community Management).\n- Habilidades básicas de diseño gráfico o edición de video.\n- Creatividad e interés genuino en temas de ecología y reciclaje.\n\n¿Cómo aplicar?\nEnvía tu portafolio de trabajo o CV a cambialord.com@gmail.com con el asunto \"Vacante - Especialista de Marketing\".",
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('empleos');
    }
};
