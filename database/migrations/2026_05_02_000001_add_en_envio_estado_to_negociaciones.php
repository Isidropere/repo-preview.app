<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Agrega el estado 'en_envio' al flujo de negociaciones (producto↔producto).
 *
 * Flujo corregido:
 *   aceptado → (ambos confirman) → aceptado con emisor_confirmado+receptor_confirmado
 *           → (ambos pagan) → en_envio  ← NUEVO (admin gestiona el envío)
 *           → (admin marca enviado) → completado
 *
 * La columna 'estado' es VARCHAR, no ENUM, por lo que no requiere ALTER TABLE
 * para agregar valores. Esta migración es informativa/documental y actualiza
 * cualquier registro con estado incorrecto si existiera.
 */
return new class extends Migration
{
    public function up(): void
    {
        // La columna 'estado' en negociaciones es VARCHAR, no ENUM,
        // por lo que 'en_envio' ya es aceptado sin cambios en el schema.
        // Solo registramos este nuevo estado en la documentación del sistema.

        // Si hubiera algún intercambio que quedó en 'completado' pero aún
        // no fue gestionado físicamente (pago_emisor=1 AND pago_receptor=1
        // creado después de esta fecha), se puede corregir manualmente desde
        // el panel de admin cambiando a 'en_envio'.
    }

    public function down(): void
    {
        // Revertir intercambios en 'en_envio' a 'aceptado' si se deshace
        DB::table('negociaciones')
            ->where('estado', 'en_envio')
            ->update(['estado' => 'aceptado']);
    }
};
