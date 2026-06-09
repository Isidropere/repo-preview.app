<?php
/**
 * Script de Verificación y Sincronización Automática
 * Ejecuta migraciones, limpia caches y asegura que el entorno esté actualizado.
 */

echo "<pre>\n";
echo "=== Iniciando Verificación de Sistema ===\n";

try {
    require __DIR__ . '/../vendor/autoload.php';
    $app = require __DIR__ . '/../bootstrap/app.php';
    $kernel = $app->make('Illuminate\Contracts\Console\Kernel');
    $kernel->bootstrap();
    echo "✅ Entorno Bootstrap cargado\n";
} catch (Throwable $e) {
    die("❌ Error cargando el entorno: " . $e->getMessage() . "\n");
}

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;

// 1. Hotfixes para tablas existentes
echo "\n=== Aplicando Hotfixes de Base de Datos ===\n";

try {
    // Ratings: columna id_user_rated
    if (Schema::hasTable('ratings')) {
        if (!Schema::hasColumn('ratings', 'id_user_rated')) {
            Schema::table('ratings', function ($table) {
                $table->unsignedBigInteger('id_user_rated')->nullable()->after('id_usuario');
            });
            echo "✅ Columna id_user_rated añadida a 'ratings'\n";
        } else {
            echo "✅ Tabla 'ratings' ya tiene id_user_rated\n";
        }
    }

    // Messages: id_emisor nullable
    if (Schema::hasTable('messages')) {
        $col = collect(DB::select("SHOW COLUMNS FROM messages WHERE Field = 'id_emisor'"))->first();
        if ($col && $col->Null === 'NO') {
            DB::statement("ALTER TABLE messages MODIFY id_emisor BIGINT UNSIGNED NULL");
            echo "✅ Columna 'id_emisor' en 'messages' cambiada a NULLABLE\n";
        } else {
            echo "✅ Columna 'id_emisor' ya es NULLABLE\n";
        }
    }

    // Verificación de Motivos de Devolución
    if (Schema::hasTable('motivos_devolucion')) {
        $count = DB::table('motivos_devolucion')->count();
        echo "✅ Tabla 'motivos_devolucion' detectada ($count motivos registrados)\n";
    } else {
        echo "⚠️ Tabla 'motivos_devolucion' NO existe (se ejecutará en la migración)\n";
    }

    // Verificación de columnas en pagos_compra para Devoluciones
    if (Schema::hasTable('pagos_compra')) {
        if (Schema::hasColumn('pagos_compra', 'id_motivo_devolucion')) {
            echo "✅ Columna 'id_motivo_devolucion' presente en pagos_compra\n";
        } else {
            echo "⚠️ Columna 'id_motivo_devolucion' faltante en pagos_compra (se ejecutará en la migración)\n";
        }
    }

    // Verificación de logs_pagos
    if (Schema::hasTable('logs_pagos')) {
        echo "✅ Tabla 'logs_pagos' detectada\n";
    } else {
        echo "⚠️ Tabla 'logs_pagos' NO existe (se ejecutará en la migración)\n";
    }

    // Hotfix: isContable en tabla users
    if (Schema::hasTable('users')) {
        if (!Schema::hasColumn('users', 'isContable')) {
            Schema::table('users', function ($table) {
                $table->boolean('isContable')->default(false)->after('isSuperAdmin');
            });
            echo "✅ Columna isContable añadida a 'users'\n";
        } else {
            echo "✅ Tabla 'users' ya tiene isContable\n";
        }
    }
} catch (Throwable $e) {
    echo "⚠️ Advertencia en Hotfixes: " . $e->getMessage() . " (Posiblemente ya aplicados)\n";
}

// 2. Ejecutar Migraciones
echo "\n=== Ejecutando Migraciones Pendientes ===\n";
try {
    // Usamos --force porque usualmente esto corre en producción
    $exitCode = Artisan::call('migrate', ['--force' => true]);
    echo Artisan::output();
    if ($exitCode === 0) {
        echo "✅ Migraciones ejecutadas con éxito\n";
    } else {
        echo "❌ Error al ejecutar migraciones (Código de salida: $exitCode)\n";
    }
} catch (Throwable $e) {
    echo "❌ Error crítico en migraciones: " . $e->getMessage() . "\n";
}

// 3. Verificar y sincronizar delivery_config
echo "\n=== Verificando Configuración de Delivery ===\n";
try {
    if (Schema::hasTable('delivery_config')) {
        $chequeados = DB::table('delivery_config')->where('clave', 'chequeados')->first();
        if (!$chequeados) {
            DB::table('delivery_config')->insertOrIgnore([
                'clave'                 => 'chequeados',
                'porcentaje'            => 10.00,
                'porcentaje_plataforma' => 10.00,
                'porcentaje_seguro'     => 10.00,
                'porcentaje_manejo'     => 6.00,
                'descripcion'           => 'Bultos chequeados - porcentajes sobre base proveedor',
                'created_at'            => now(),
                'updated_at'            => now(),
            ]);
            echo "✅ Configuración 'chequeados' insertada en delivery_config\n";
        } else {
            echo "✅ Configuración 'chequeados' ya existe en delivery_config\n";
        }
    } else {
        echo "⚠️ Tabla delivery_config no encontrada\n";
    }
} catch (Throwable $e) {
    echo "❌ Error en configuración delivery: " . $e->getMessage() . "\n";
}

// 4. Limpiar Caches
echo "\n=== Limpiando Caches de Aplicación ===\n";
$commands = [
    'cache:clear'  => 'Cache de datos',
    'view:clear'   => 'Plantillas Blade',
    'config:clear' => 'Configuraciones',
    'route:clear'  => 'Rutas'
];

foreach ($commands as $cmd => $desc) {
    try {
        Artisan::call($cmd);
        echo "✅ $desc ($cmd) limpio\n";
    } catch (Throwable $e) {
        echo "❌ Error en $cmd: " . $e->getMessage() . "\n";
    }
}

echo "\n=== Sincronización Finalizada con éxito ===\n";
echo "✅ El sistema está listo para operar.\n";
echo "</pre>";
