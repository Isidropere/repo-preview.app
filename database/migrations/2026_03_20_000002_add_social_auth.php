<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración: autenticación social (OAuth)
 *
 * 1. Agrega facebook_id e instagram_id a la tabla users
 * 2. Crea la tabla oauth_providers para guardar configuración
 *    de cada proveedor (client_id, client_secret, activo)
 *    desde la base de datos — sin depender solo del .env
 */
return new class extends Migration
{
    public function up(): void
    {
        // Columnas en users para cada proveedor
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'facebook_id')) {
                $table->string('facebook_id')->nullable()->after('google_id');
            }
            if (!Schema::hasColumn('users', 'instagram_id')) {
                $table->string('instagram_id')->nullable()->after('facebook_id');
            }
        });

        // Tabla de configuración de proveedores OAuth
        Schema::create('oauth_providers', function (Blueprint $table) {
            $table->id();
            $table->string('provider', 30)->unique(); // google | facebook | instagram
            $table->string('client_id')->nullable();
            $table->string('client_secret')->nullable();
            $table->string('redirect_uri')->nullable();
            $table->boolean('activo')->default(false);
            $table->timestamps();
        });

        // Insertar los tres proveedores con valores vacíos (se llenan desde el panel admin)
        DB::table('oauth_providers')->insert([
            ['provider' => 'google',    'client_id' => null, 'client_secret' => null, 'redirect_uri' => 'http://localhost:8080/auth/google/callback',    'activo' => false, 'created_at' => now(), 'updated_at' => now()],
            ['provider' => 'facebook',  'client_id' => null, 'client_secret' => null, 'redirect_uri' => 'http://localhost:8080/auth/facebook/callback',  'activo' => false, 'created_at' => now(), 'updated_at' => now()],
            ['provider' => 'instagram', 'client_id' => null, 'client_secret' => null, 'redirect_uri' => 'http://localhost:8080/auth/instagram/callback', 'activo' => false, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['facebook_id', 'instagram_id']);
        });

        Schema::dropIfExists('oauth_providers');
    }
};
