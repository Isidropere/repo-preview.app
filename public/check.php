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

// Verificar y crear tabla solicitudes_transporte
echo "\n=== Verificar tabla solicitudes_transporte ===\n";
try {
    if (!Illuminate\Support\Facades\Schema::hasTable('solicitudes_transporte')) {
        DB::statement("CREATE TABLE solicitudes_transporte (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            id_usuario BIGINT UNSIGNED NULL,
            tipo_servicio ENUM('transporte', 'mudanza') NOT NULL DEFAULT 'transporte',
            nombre VARCHAR(255) NOT NULL,
            apellido VARCHAR(255) NOT NULL,
            cedula VARCHAR(20) NOT NULL,
            direccion VARCHAR(500) NOT NULL,
            telefono VARCHAR(20) NOT NULL,
            correo VARCHAR(255) NOT NULL,
            fecha_servicio DATE NOT NULL,
            ubicacion_geologica VARCHAR(255) NULL,
            dimensiones_carga TEXT NOT NULL,
            estado ENUM('pendiente', 'aprobada', 'rechazada') NOT NULL DEFAULT 'pendiente',
            created_at TIMESTAMP NULL,
            updated_at TIMESTAMP NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        echo "✅ Tabla solicitudes_transporte creada\n";
    } else {
        echo "✅ solicitudes_transporte ya existe\n";
        // Verificar si tiene tipo_servicio
        $cols = Illuminate\Support\Facades\Schema::getColumnListing('solicitudes_transporte');
        if (!in_array('tipo_servicio', $cols)) {
            DB::statement("ALTER TABLE solicitudes_transporte ADD tipo_servicio ENUM('transporte', 'mudanza') NOT NULL DEFAULT 'transporte' AFTER id_usuario");
            echo "✅ Columna tipo_servicio agregada a solicitudes_transporte\n";
        }
    }
} catch (Throwable $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

// Verificar y crear tabla transporte_articulos y poblar catálogo
echo "\n=== Verificar tabla transporte_articulos ===\n";
try {
    if (!Illuminate\Support\Facades\Schema::hasTable('transporte_articulos')) {
        DB::statement("CREATE TABLE transporte_articulos (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            nombre VARCHAR(255) NOT NULL,
            categoria ENUM('transporte', 'mudanza', 'ambos') NOT NULL DEFAULT 'ambos',
            estatus TINYINT(1) NOT NULL DEFAULT 1,
            created_at TIMESTAMP NULL,
            updated_at TIMESTAMP NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        echo "✅ Tabla transporte_articulos creada\n";
    } else {
        echo "✅ transporte_articulos ya existe\n";
    }

    // Poblar catálogo de artículos si está vacío
    $count = DB::table('transporte_articulos')->count();
    if ($count === 0) {
        $articulos = [
            ['nombre' => 'Sofá de 3 plazas', 'categoria' => 'mudanza'],
            ['nombre' => 'Sofá de 2 plazas', 'categoria' => 'mudanza'],
            ['nombre' => 'Sillón individual', 'categoria' => 'mudanza'],
            ['nombre' => 'Cama Matrimonial (Colchón + Box)', 'categoria' => 'mudanza'],
            ['nombre' => 'Cama Individual (Colchón + Box)', 'categoria' => 'mudanza'],
            ['nombre' => 'Mesa de comedor', 'categoria' => 'mudanza'],
            ['nombre' => 'Silla de comedor', 'categoria' => 'mudanza'],
            ['nombre' => 'Cajonera / Cómoda', 'categoria' => 'mudanza'],
            ['nombre' => 'Armario / Ropero grande', 'categoria' => 'mudanza'],
            ['nombre' => 'Escritorio de oficina', 'categoria' => 'mudanza'],
            ['nombre' => 'Caja de mudanza grande', 'categoria' => 'mudanza'],
            ['nombre' => 'Caja de mudanza mediana', 'categoria' => 'mudanza'],
            ['nombre' => 'Caja de mudanza pequeña', 'categoria' => 'mudanza'],
            ['nombre' => 'Espejo grande / Cuadro', 'categoria' => 'mudanza'],
            ['nombre' => 'Pallet de mercancía estándar', 'categoria' => 'transporte'],
            ['nombre' => 'Caja de herramientas industrial', 'categoria' => 'transporte'],
            ['nombre' => 'Sacos de cemento / arena', 'categoria' => 'transporte'],
            ['nombre' => 'Varillas / Tubos de metal (lote)', 'categoria' => 'transporte'],
            ['nombre' => 'Equipaje / Maletas de carga pesada', 'categoria' => 'transporte'],
            ['nombre' => 'Caja de carga general industrial', 'categoria' => 'transporte'],
            ['nombre' => 'Nevera / Refrigerador', 'categoria' => 'ambos'],
            ['nombre' => 'Estufa de cocina', 'categoria' => 'ambos'],
            ['nombre' => 'Lavadora / Secadora', 'categoria' => 'ambos'],
            ['nombre' => 'Microondas / Hornito', 'categoria' => 'ambos'],
            ['nombre' => 'Televisor (Smart TV)', 'categoria' => 'ambos'],
            ['nombre' => 'Bicicleta', 'categoria' => 'ambos'],
            ['nombre' => 'Caja de cartón / Artículos varios', 'categoria' => 'ambos'],
            ['nombre' => 'Planta eléctrica portátil', 'categoria' => 'ambos'],
        ];
        foreach ($articulos as $art) {
            DB::table('transporte_articulos')->insert([
                'nombre' => $art['nombre'],
                'categoria' => $art['categoria'],
                'estatus' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        echo "✅ Catálogo de transporte_articulos poblado con éxito\n";
    }
} catch (Throwable $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

// Verificar y crear tabla pivot solicitud_transporte_articulo
echo "\n=== Verificar tabla solicitud_transporte_articulo ===\n";
try {
    if (!Illuminate\Support\Facades\Schema::hasTable('solicitud_transporte_articulo')) {
        DB::statement("CREATE TABLE solicitud_transporte_articulo (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            solicitud_transporte_id BIGINT UNSIGNED NOT NULL,
            articulo_id BIGINT UNSIGNED NOT NULL,
            cantidad INT NOT NULL DEFAULT 1,
            created_at TIMESTAMP NULL,
            updated_at TIMESTAMP NULL,
            FOREIGN KEY (solicitud_transporte_id) REFERENCES solicitudes_transporte(id) ON DELETE CASCADE,
            FOREIGN KEY (articulo_id) REFERENCES transporte_articulos(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        echo "✅ Tabla solicitud_transporte_articulo creada\n";
    } else {
        echo "✅ solicitud_transporte_articulo ya existe\n";
    }
} catch (Throwable $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
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
