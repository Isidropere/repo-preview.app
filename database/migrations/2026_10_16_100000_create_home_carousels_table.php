<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Tabla de Diapositivas / Imágenes del Carrusel
        Schema::create('home_carousels', function (Blueprint $table) {
            $table->id();
            $table->string('imagen'); // URL o path en storage
            $table->string('titulo')->nullable();
            $table->string('subtitulo')->nullable();
            $table->string('enlace')->nullable();
            $table->integer('orden')->default(0);
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        // 2. Tabla de Configuraciones del Carrusel (Velocidad, Autoplay, etc.)
        Schema::create('home_carousel_configs', function (Blueprint $table) {
            $table->id();
            $table->string('clave')->unique();
            $table->text('valor')->nullable();
            $table->timestamps();
        });

        // Configuraciones iniciales
        DB::table('home_carousel_configs')->insert([
            [
                'clave' => 'velocidad_segundos',
                'valor' => '4',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'clave' => 'is_autoplay',
                'valor' => '1',
                'created_at' => now(),
                'updated_at' => now()
            ],
        ]);

        // Cargar los 3 banners actuales por defecto para que aparezcan en el admin listos para gestionar
        DB::table('home_carousels')->insert([
            [
                'imagen' => '/imgs/1.jpg',
                'titulo' => 'Intercambia y vende en CámbialoRD',
                'subtitulo' => 'Promoción oficial',
                'enlace' => null,
                'orden' => 1,
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'imagen' => '/imgs/2.jpg',
                'titulo' => 'Ofertas destacadas',
                'subtitulo' => 'Explora los mejores productos',
                'enlace' => null,
                'orden' => 2,
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'imagen' => '/imgs/3.jpg',
                'titulo' => 'Encuentra lo que necesitas',
                'subtitulo' => 'Trueques seguros y rápidos',
                'enlace' => null,
                'orden' => 3,
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('home_carousels');
        Schema::dropIfExists('home_carousel_configs');
    }
};
