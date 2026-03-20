<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Restructura predefined_messages:
 *
 * ROLES:
 *   emisor   → mensajes que usa quien PROPONE el intercambio
 *   receptor → mensajes que usa quien RECIBE la propuesta
 *   general  → disponible para ambos
 *
 * TIPOS (categoría del mensaje):
 *   saludo        → presentación inicial
 *   oferta        → propuesta de precio/paquete
 *   contraoferta  → respuesta con nueva propuesta
 *   aceptar       → confirmar acuerdo
 *   rechazar      → declinar
 *   pregunta      → consulta sobre el artículo
 *   respuesta     → respuesta genérica
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('predefined_messages')) {
            return;
        }
        // 1. Ampliar el ENUM de 'tipo' para incluir los nuevos valores
        DB::statement("ALTER TABLE `predefined_messages`
            MODIFY `tipo` VARCHAR(50) NULL DEFAULT 'general'");

        // 2. Hacer 'Clasificador' nullable para no requerir valor en inserts
        DB::statement("ALTER TABLE `predefined_messages`
            MODIFY `Clasificador` INT NULL DEFAULT NULL");

        // 3. Agregar columna 'rol' si no existe
        if (!Schema::hasColumn('predefined_messages', 'rol')) {
            Schema::table('predefined_messages', function (Blueprint $table) {
                $table->string('rol')->default('general')->after('tipo')
                    ->comment('emisor | receptor | general');
            });
        }

        // 4. Limpiar TODOS los registros actuales (tienen duplicados y estructura inconsistente)
        DB::table('predefined_messages')->truncate();

        // 5. Insertar mensajes limpios y estructurados
        $now = now();
        DB::table('predefined_messages')->insert([

            // ── EMISOR (quien propone el intercambio) ──────────────
            [
                'titulo'     => 'Saludo inicial',
                'mensaje'    => 'Hola, estoy interesado en tu artículo. ¿Estarías dispuesto a intercambiarlo?',
                'tipo'       => 'saludo',
                'rol'        => 'emisor',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'titulo'     => 'Proponer paquete',
                'mensaje'    => 'Te ofrezco mi paquete de artículos a cambio. ¿Te interesa?',
                'tipo'       => 'oferta',
                'rol'        => 'emisor',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'titulo'     => 'Oferta con monto',
                'mensaje'    => 'Puedo agregar un monto adicional para compensar la diferencia de valor.',
                'tipo'       => 'oferta',
                'rol'        => 'emisor',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'titulo'     => 'Consulta sobre estado',
                'mensaje'    => '¿Cuál es el estado actual del artículo? ¿Tiene algún defecto?',
                'tipo'       => 'pregunta',
                'rol'        => 'emisor',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'titulo'     => 'Aceptar contraoferta',
                'mensaje'    => 'Acepto tu contraoferta. ¿Cómo coordinamos el intercambio?',
                'tipo'       => 'aceptar',
                'rol'        => 'emisor',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'titulo'     => 'Rechazar contraoferta',
                'mensaje'    => 'Gracias por tu respuesta, pero no puedo aceptar esas condiciones.',
                'tipo'       => 'rechazar',
                'rol'        => 'emisor',
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // ── RECEPTOR (dueño del artículo) ──────────────────────
            [
                'titulo'     => 'Interesado en intercambio',
                'mensaje'    => 'Hola, me interesa tu propuesta. Cuéntame más sobre los artículos que ofreces.',
                'tipo'       => 'respuesta',
                'rol'        => 'receptor',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'titulo'     => 'Aceptar propuesta',
                'mensaje'    => 'Acepto el intercambio. ¿Cómo coordinamos la entrega?',
                'tipo'       => 'aceptar',
                'rol'        => 'receptor',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'titulo'     => 'Hacer contraoferta',
                'mensaje'    => 'Me interesa pero necesito un ajuste en las condiciones. ¿Podemos negociar?',
                'tipo'       => 'contraoferta',
                'rol'        => 'receptor',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'titulo'     => 'Rechazar propuesta',
                'mensaje'    => 'Gracias por tu interés, pero en este momento no puedo aceptar esta oferta.',
                'tipo'       => 'rechazar',
                'rol'        => 'receptor',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'titulo'     => 'Pedir más información',
                'mensaje'    => 'Antes de decidir, ¿podrías darme más detalles sobre los artículos del paquete?',
                'tipo'       => 'pregunta',
                'rol'        => 'receptor',
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // ── GENERAL (ambos roles) ───────────────────────────────
            [
                'titulo'     => 'Valor no razonable',
                'mensaje'    => 'Considero que el valor ofrecido no es proporcional al artículo.',
                'tipo'       => 'general',
                'rol'        => 'general',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'titulo'     => 'Coordinar entrega',
                'mensaje'    => '¿Cómo prefieres coordinar la entrega o el envío?',
                'tipo'       => 'general',
                'rol'        => 'general',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        // 4. Agregar índice único para evitar duplicados futuros
        // Primero verificar si ya existe
        try {
            Schema::table('predefined_messages', function (Blueprint $table) {
                $table->unique(['tipo', 'rol', 'titulo'], 'uq_predefined_tipo_rol_titulo');
            });
        } catch (\Exception $e) {
            // El índice ya existe, ignorar
        }
    }

    public function down(): void
    {
        Schema::table('predefined_messages', function (Blueprint $table) {
            try { $table->dropUnique('uq_predefined_tipo_rol_titulo'); } catch (\Exception $e) {}
            try { $table->dropColumn('rol'); } catch (\Exception $e) {}
        });
    }
};
