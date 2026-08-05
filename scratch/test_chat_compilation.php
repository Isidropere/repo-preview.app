<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Negociacion;

try {
    echo "=== Iniciando prueba de compilación de negociaciones.chat ===\n";
    $negociacion = Negociacion::with(['usuario', 'usuarioReceptor', 'item.imagenes'])->first();
    if ($negociacion) {
        echo "Negociación de prueba encontrada: ID {$negociacion->id_negociacion}\n";
        
        $userId = $negociacion->usuario_emisor_id;
        $user = \App\Models\User::find($userId);
        if ($user) {
            auth()->login($user);
            echo "Usuario logueado para prueba: ID {$user->id}\n";
        }
        
        $rol = 'emisor';
        $otroUsuario = $negociacion->usuarioReceptor;
        $mensajesPredefinidos = \App\Models\PredefinedMessage::where('activo', true)->get();
        $accionesPredefinidas = \App\Models\PredefinedMessage::select('tipo')->distinct()->pluck('tipo');
        $mensajes = [];

        $html = view('negociaciones.chat', compact('negociacion', 'rol', 'otroUsuario', 'mensajesPredefinidos', 'accionesPredefinidas', 'mensajes'))->render();
        echo "✅ Compilado y renderizado de chat exitoso! Longitud del HTML: " . strlen($html) . " bytes.\n";
    } else {
        echo "⚠️ No se encontraron negociaciones en la base de datos para realizar la prueba.\n";
    }
} catch (\Throwable $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Línea: " . $e->getLine() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
    exit(1);
}
