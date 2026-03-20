<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Elimina tablas legacy que ya no se usan.
 * EJECUTAR SOLO después de migración 200007 (FK migradas a users).
 *
 * - usuarios: reemplazada por users
 * - miembros: datos duplicados de users + direcciones
 *
 * NOTA: Se hace backup de los datos en el down() por si se necesita revertir.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }
        // Primero eliminar FK que apuntan a estas tablas (si quedó alguna)
        $this->dropRemainingFks();

        Schema::dropIfExists('usuarios');
        Schema::dropIfExists('miembros');
    }

    public function down(): void
    {
        // Recrear tabla usuarios (estructura original)
        DB::statement("
            CREATE TABLE `usuarios` (
                `id_usuario` int(11) NOT NULL AUTO_INCREMENT,
                `nombre_usuario` varchar(60) NOT NULL,
                `clave` varchar(60) NOT NULL,
                `estatus` varchar(100) NOT NULL,
                `tipo` smallint(6) NOT NULL,
                `fecha_creacion` datetime DEFAULT current_timestamp(),
                PRIMARY KEY (`id_usuario`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Recrear tabla miembros (estructura original)
        DB::statement("
            CREATE TABLE `miembros` (
                `id_miembro` int(11) NOT NULL AUTO_INCREMENT,
                `nombres` varchar(60) NOT NULL,
                `apellidos` varchar(60) NOT NULL,
                `email` varchar(60) NOT NULL,
                `telefono` varchar(10) NOT NULL,
                `id_plan` int(11) NOT NULL DEFAULT 1,
                `calle` varchar(60) NOT NULL,
                `casa_numero` varchar(15) DEFAULT NULL,
                `apto` varchar(15) DEFAULT NULL,
                `edificio` varchar(15) DEFAULT NULL,
                `id_provincia` varchar(100) NOT NULL,
                `id_municipio` varchar(100) DEFAULT NULL,
                `geolocalizacion` varchar(100) DEFAULT NULL,
                PRIMARY KEY (`id_miembro`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    private function dropRemainingFks(): void
    {
        $fks = [
            ['miembros', 'miembros_ibfk_1'],
            ['miembros', 'miembros_ibfk_2'],
            ['miembros', 'miembros_ibfk_3'],
            ['miembros', 'miembros_ibfk_4'],
            ['facturas_transporte_transaccion', 'facturas_transporte_transaccion_ibfk_3'],
        ];

        foreach ($fks as [$tabla, $constraint]) {
            try {
                DB::statement("ALTER TABLE `{$tabla}` DROP FOREIGN KEY `{$constraint}`");
            } catch (\Throwable $e) {
                // Ya no existe
            }
        }
    }
};
