<?php
echo "<pre>\n";
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

// Verificar y crear columna id_user_rated en ratings
echo "=== Verificar tabla ratings ===\n";
try {
    if (Illuminate\Support\Facades\Schema::hasTable('ratings')) {
        $cols = Illuminate\Support\Facades\Schema::getColumnListing('ratings');
        echo "Columnas: " . implode(', ', $cols) . "\n";
        
        if (!in_array('id_user_rated', $cols)) {
            Illuminate\Support\Facades\Schema::table('ratings', function ($table) {
                $table->unsignedBigInteger('id_user_rated')->nullable()->after('id_usuario');
            });
            echo "✅ Columna id_user_rated creada\n";
        } else {
            echo "✅ id_user_rated ya existe\n";
        }
    } else {
        echo "❌ Tabla ratings no existe\n";
    }
} catch (Throwable $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

// Verificar y hacer id_emisor nullable en messages (para notificaciones del sistema)
echo "\n=== Verificar messages.id_emisor nullable ===\n";
try {
    $col = collect(DB::select("SHOW COLUMNS FROM messages WHERE Field = 'id_emisor'"))->first();
    if ($col && $col->Null === 'NO') {
        DB::statement("ALTER TABLE messages MODIFY id_emisor BIGINT UNSIGNED NULL");
        echo "✅ id_emisor cambiado a nullable\n";
    } else {
        echo "✅ id_emisor ya es nullable\n";
    }
} catch (Throwable $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

// Verificar y crear tabla solicitudes_servicio (flujo de aprobación de servicios)
echo "\n=== Verificar tabla solicitudes_servicio ===\n";
try {
    if (!Illuminate\Support\Facades\Schema::hasTable('solicitudes_servicio')) {
        DB::statement("CREATE TABLE solicitudes_servicio (
            id_solicitud BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            id_comprador BIGINT UNSIGNED NOT NULL,
            id_proveedor BIGINT UNSIGNED NOT NULL,
            id_item INT NOT NULL,
            id_carrito INT NOT NULL,
            cantidad INT UNSIGNED NOT NULL DEFAULT 1,
            monto_total DECIMAL(10,2) NOT NULL,
            estado VARCHAR(30) NOT NULL DEFAULT 'pendiente_aprobacion',
            fecha_creacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            fecha_actualizacion TIMESTAMP NULL,
            INDEX idx_proveedor_estado (id_proveedor, estado),
            INDEX idx_comprador_estado (id_comprador, estado),
            INDEX idx_item (id_item),
            FOREIGN KEY (id_comprador) REFERENCES users(id),
            FOREIGN KEY (id_proveedor) REFERENCES users(id),
            FOREIGN KEY (id_item) REFERENCES items(id_item),
            FOREIGN KEY (id_carrito) REFERENCES carritos(id_carrito)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        echo "✅ Tabla solicitudes_servicio creada\n";
    } else {
        echo "✅ solicitudes_servicio ya existe\n";
    }
} catch (Throwable $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

// Verificar y crear tabla hojas_vida (hoja de vida de talentos)
echo "\n=== Verificar tabla hojas_vida ===\n";
try {
    if (!Illuminate\Support\Facades\Schema::hasTable('hojas_vida')) {
        DB::statement("CREATE TABLE hojas_vida (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            id_user BIGINT UNSIGNED NOT NULL,
            nombres VARCHAR(100) NOT NULL,
            apellidos VARCHAR(100) NOT NULL,
            titulo_profesional VARCHAR(150) NOT NULL,
            descripcion_bio TEXT NOT NULL,
            habilidades TEXT NOT NULL,
            experiencia TEXT NOT NULL,
            ubicacion VARCHAR(200) NOT NULL,
            created_at TIMESTAMP NULL,
            updated_at TIMESTAMP NULL,
            UNIQUE KEY uk_id_user (id_user),
            FOREIGN KEY (id_user) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        echo "✅ Tabla hojas_vida creada\n";
    } else {
        echo "✅ hojas_vida ya existe\n";
    }
} catch (Throwable $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

// Ejecutar migraciones pendientes
echo "\n=== Ejecutando Migraciones ===\n";
try {
    Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
    echo nl2br(Illuminate\Support\Facades\Artisan::output());
    echo "✅ Migraciones completadas\n";
} catch (Throwable $e) {
    echo "❌ Error en migraciones: " . $e->getMessage() . "\n";
}

// Limpiar caches
$cmds = ['view:clear', 'config:clear', 'route:clear', 'cache:clear'];
foreach ($cmds as $cmd) {
    try {
        Illuminate\Support\Facades\Artisan::call($cmd);
        echo "✅ $cmd\n";
    } catch (Throwable $e) {
        echo "❌ $cmd: " . $e->getMessage() . "\n";
    }
}

echo "\n✅ Listo\n</pre>";
