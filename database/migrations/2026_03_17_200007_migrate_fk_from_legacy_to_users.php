<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Migra las FK que apuntan a tablas legacy (miembros, usuarios) para que
 * apunten a users.id. Esto es CRÍTICO para la integridad referencial.
 *
 * IMPORTANTE: Ejecutar SOLO después de verificar que los datos en las
 * columnas id_user/id_miembro ya contienen IDs válidos de la tabla users.
 * Si no, primero hacer un mapeo manual de miembros.id_miembro → users.id.
 *
 * Tablas afectadas:
 *   - carritos (id_user → users.id, antes → miembros.id_miembro)
 *   - items (id_user → users.id, antes → miembros.id_miembro)
 *   - direcciones (id_user → users.id, antes → usuarios.id_usuario)
 *   - tarjetas_pagos (id_miembro → renombrar a id_user → users.id)
 *   - paquetes (beneficiario → users.id, antes → miembros.id_miembro)
 *   - ofertas (oferente/beneficiario → users.id, antes → miembros.id_miembro)
 *   - ratings (id_usuario → users.id, antes → usuarios.id_usuario)
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── carritos: drop FK vieja, crear nueva a users ──
        $this->dropFkSafe('carritos', 'carritos_ibfk_1');
        DB::statement('ALTER TABLE `carritos` MODIFY `id_user` BIGINT(20) UNSIGNED NOT NULL');
        Schema::table('carritos', function ($table) {
            $table->foreign('id_user')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
        });

        // ── items: drop FK vieja, crear nueva a users ──
        $this->dropFkSafe('items', 'items_ibfk_1');
        DB::statement('ALTER TABLE `items` MODIFY `id_user` BIGINT(20) UNSIGNED NOT NULL');
        Schema::table('items', function ($table) {
            $table->foreign('id_user')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
        });

        // ── direcciones: drop FK vieja, crear nueva a users ──
        $this->dropFkSafe('direcciones', 'direcciones_ibfk_3');
        DB::statement('ALTER TABLE `direcciones` MODIFY `id_user` BIGINT(20) UNSIGNED NULL');
        Schema::table('direcciones', function ($table) {
            $table->foreign('id_user')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
        });

        // ── tarjetas_pagos: renombrar id_miembro → id_user, nueva FK ──
        $this->dropFkSafe('tarjetas_pagos', 'tarjetas_pagos_ibfk_1');
        if (Schema::hasColumn('tarjetas_pagos', 'id_miembro') && !Schema::hasColumn('tarjetas_pagos', 'id_user')) {
            DB::statement('ALTER TABLE `tarjetas_pagos` CHANGE `id_miembro` `id_user` BIGINT(20) UNSIGNED NOT NULL');
        } elseif (Schema::hasColumn('tarjetas_pagos', 'id_user')) {
            DB::statement('ALTER TABLE `tarjetas_pagos` MODIFY `id_user` BIGINT(20) UNSIGNED NOT NULL');
        }
        Schema::table('tarjetas_pagos', function ($table) {
            $table->foreign('id_user')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
        });

        // ── paquetes: beneficiario → users.id ──
        $this->dropFkSafe('paquetes', 'paquetes_ibfk_1');
        DB::statement('ALTER TABLE `paquetes` MODIFY `beneficiario` BIGINT(20) UNSIGNED NOT NULL');
        Schema::table('paquetes', function ($table) {
            $table->foreign('beneficiario')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
        });

        // ── ofertas: oferente y beneficiario → users.id ──
        $this->dropFkSafe('ofertas', 'ofertas_ibfk_1');
        $this->dropFkSafe('ofertas', 'ofertas_ibfk_2');
        DB::statement('ALTER TABLE `ofertas` MODIFY `oferente` BIGINT(20) UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE `ofertas` MODIFY `beneficiario` BIGINT(20) UNSIGNED NOT NULL');
        Schema::table('ofertas', function ($table) {
            $table->foreign('oferente')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('beneficiario')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
        });

        // ── ratings: id_usuario → users.id, id_miembro → id_user_rated ──
        $this->dropFkSafe('ratings', 'ratings_ibfk_1');
        $this->dropFkSafe('ratings', 'ratings_ibfk_2');
        DB::statement('ALTER TABLE `ratings` MODIFY `id_usuario` BIGINT(20) UNSIGNED NOT NULL');
        Schema::table('ratings', function ($table) {
            $table->foreign('id_usuario')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
        });
        // id_miembro en ratings es nullable, lo renombramos a id_user_rated
        if (Schema::hasColumn('ratings', 'id_miembro')) {
            DB::statement('ALTER TABLE `ratings` CHANGE `id_miembro` `id_user_rated` BIGINT(20) UNSIGNED NULL');
            Schema::table('ratings', function ($table) {
                $table->foreign('id_user_rated')->references('id')->on('users')->onDelete('set null')->onUpdate('cascade');
            });
        }
    }

    public function down(): void
    {
        // Revertir es complejo — solo drop las FK nuevas
        // Las FK originales a miembros/usuarios no se restauran automáticamente
        $fks = [
            'carritos'        => 'carritos_id_user_foreign',
            'items'           => 'items_id_user_foreign',
            'direcciones'     => 'direcciones_id_user_foreign',
            'tarjetas_pagos'  => 'tarjetas_pagos_id_user_foreign',
            'paquetes'        => 'paquetes_beneficiario_foreign',
            'ofertas'         => ['ofertas_oferente_foreign', 'ofertas_beneficiario_foreign'],
            'ratings'         => ['ratings_id_usuario_foreign'],
        ];

        foreach ($fks as $tabla => $nombres) {
            $nombres = (array) $nombres;
            Schema::table($tabla, function ($table) use ($nombres) {
                foreach ($nombres as $fk) {
                    $table->dropForeign($fk);
                }
            });
        }

        if (Schema::hasColumn('ratings', 'id_user_rated')) {
            Schema::table('ratings', function ($table) {
                $table->dropForeign('ratings_id_user_rated_foreign');
            });
            DB::statement('ALTER TABLE `ratings` CHANGE `id_user_rated` `id_miembro` INT(11) NULL');
        }

        // Revertir tipos de columna a int(11)
        DB::statement('ALTER TABLE `carritos` MODIFY `id_user` INT(11) NOT NULL');
        DB::statement('ALTER TABLE `items` MODIFY `id_user` INT(11) NOT NULL');
        DB::statement('ALTER TABLE `direcciones` MODIFY `id_user` INT(11) NULL');
        DB::statement('ALTER TABLE `paquetes` MODIFY `beneficiario` INT(11) NOT NULL');
        DB::statement('ALTER TABLE `ofertas` MODIFY `oferente` INT(11) NOT NULL');
        DB::statement('ALTER TABLE `ofertas` MODIFY `beneficiario` INT(11) NOT NULL');
        DB::statement('ALTER TABLE `ratings` MODIFY `id_usuario` INT(11) NOT NULL');
    }

    /**
     * Drop FK constraint de forma segura (ignora si no existe).
     */
    private function dropFkSafe(string $tabla, string $constraint): void
    {
        try {
            DB::statement("ALTER TABLE `{$tabla}` DROP FOREIGN KEY `{$constraint}`");
        } catch (\Throwable $e) {
            // FK no existe, continuar
        }
    }
};
