<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Direcciones;
use App\Models\TarjetaPago;
use Illuminate\Support\Facades\Hash;

// Configurar argumentos de línea de comandos o usar valores por defecto
$email = $argv[1] ?? 'azul.auditor@cambialo.com.do';
$password = $argv[2] ?? 'Azul2026*';

echo "\n==================================================\n";
echo "CREADOR DE USUARIO DE PRUEBAS PARA AUDITORÍA AZUL\n";
echo "==================================================\n";

// 1. Buscar o crear el usuario de prueba
$user = User::where('email', $email)->first();

if ($user) {
    echo "ℹ️ El usuario con correo '{$email}' ya existe. Actualizando contraseña y estatus...\n";
    $user->password = Hash::make($password);
    $user->estatus = 1;
    $user->email_verified_at = now();
    $user->save();
} else {
    echo "🆕 Creando nuevo usuario: {$email}...\n";
    $user = User::create([
        'nombres' => 'Azul',
        'apellidos' => 'Auditor',
        'telefono' => '(829) 963-4839',
        'nombre_usuario' => 'azul_auditor',
        'email' => $email,
        'password' => Hash::make($password),
        'estatus' => 1,
        'id_tipo_usuario' => 1, // Comprador
        'email_verified_at' => now(),
    ]);
}

// 2. Buscar o crear dirección de envío predeterminada para el checkout
$direccion = Direcciones::where('id_user', $user->id)->first();
if (!$direccion) {
    echo "📍 Creando dirección de envío predeterminada (Napoleón Bonaparte)...\n";
    $maxId = (int) (Direcciones::max('id_direccion') ?? 0);
    $direccion = Direcciones::create([
        'id_direccion' => $maxId + 1,
        'id_user' => $user->id,
        'calle' => 'Napoleón Bonaparte',
        'N_casa_edificio' => 'Manzana T, Edificio 21',
        'sector' => 'Res. Pablo Mella Morales II',
        'id_provincia' => 1, // Santo Domingo
        'id_municipio' => 1,
        'es_predeterminada' => 1,
        'telefono_contacto' => '(829) 963-4839',
    ]);
} else {
    echo "📍 El usuario ya cuenta con dirección. Asegurando predeterminación...\n";
    Direcciones::where('id_user', $user->id)->update(['es_predeterminada' => 0]);
    Direcciones::where('id_direccion', $direccion->id_direccion)->update(['es_predeterminada' => 1]);
}

// 3. Registrar tarjeta de pruebas oficial de AZUL (Visa)
TarjetaPago::where('id_user', $user->id)->delete();
echo "💳 Registrando tarjeta de pruebas AZUL (Visa **** 9010)...\n";
$tarjeta = new TarjetaPago();
$tarjeta->id_user = $user->id;
$tarjeta->no_tarjeta = '4000123456789010'; // Tarjeta de prueba oficial de Azul (Visa)
$tarjeta->nombre_titular = 'AZUL AUDITOR';
$tarjeta->mes_expiracion = 12;
$tarjeta->{'año_expiracion'} = 2028;
$tarjeta->tipo_tarjeta = 'Visa';
$tarjeta->banco_tarjeta = 'AZUL TEST';
$tarjeta->last4 = '9010';
$tarjeta->usar_esta_tarjeta = 1;
$tarjeta->estatus = 1;
$tarjeta->save();

echo "\n==================================================\n";
echo "🎉 PROCESO COMPLETADO CON ÉXITO\n";
echo "==================================================\n";
echo "📧 Usuario (Email): {$email}\n";
echo "🔑 Contraseña (Password): {$password}\n";
echo "📍 Dirección: Napoleón Bonaparte, Manzana T, Edificio 21, Res. Pablo Mella Morales II, Santo Domingo\n";
echo "💳 Tarjeta guardada: Visa **** **** **** 9010 (Exp: 12/2028, CVV: 123 o cualquier número)\n";
echo "==================================================\n";
