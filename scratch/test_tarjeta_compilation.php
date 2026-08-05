<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Item;

try {
    echo "=== Iniciando prueba de compilación de tarjeta-producto ===\n";
    $item = Item::with(['imagenes', 'inventarios', 'categoria'])->first();
    if ($item) {
        echo "Item de prueba encontrado: ID {$item->id_item} - {$item->item}\n";
        // Simular inicio de sesión con el primer usuario para probar lógica auth
        $user = \App\Models\User::first();
        if ($user) {
            auth()->login($user);
            echo "Usuario logueado para prueba: ID {$user->id}\n";
        }
        
        $html = view('components.tarjeta-producto', compact('item'))->render();
        echo "✅ Compilado y renderizado exitoso! Longitud del HTML: " . strlen($html) . " bytes.\n";
    } else {
        echo "⚠️ No se encontraron items en la base de datos para realizar la prueba.\n";
    }
} catch (\Throwable $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Línea: " . $e->getLine() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
    exit(1);
}
