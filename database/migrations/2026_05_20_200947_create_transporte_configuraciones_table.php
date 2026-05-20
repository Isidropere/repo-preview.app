<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transporte_configuraciones', function (Blueprint $table) {
            $table->id();
            $table->string('clave')->unique();
            $table->string('valor');
            $table->timestamps();
        });

        // Insertar valores por defecto
        DB::table('transporte_configuraciones')->insert([
            ['clave' => 'precio_km_transporte', 'valor' => '50', 'created_at' => now(), 'updated_at' => now()],
            ['clave' => 'precio_km_mudanza', 'valor' => '100', 'created_at' => now(), 'updated_at' => now()],
            ['clave' => 'limite_articulos_mudanza', 'valor' => '5', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transporte_configuraciones');
    }
};
