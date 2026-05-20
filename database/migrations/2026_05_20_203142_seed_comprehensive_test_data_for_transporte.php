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
        // 1. Limpiar datos previos si existen (Opcional, pero recomendado para seeders de prueba)
        DB::table('transporte_articulos')->delete();

        // 2. Insertar Artículos con Precios Base
        $articulos = [
            ['nombre' => 'Sofá de 3 plazas', 'categoria' => 'mudanza', 'precio_base' => 800],
            ['nombre' => 'Sofá de 2 plazas', 'categoria' => 'mudanza', 'precio_base' => 600],
            ['nombre' => 'Cama Matrimonial (Colchón + Box)', 'categoria' => 'mudanza', 'precio_base' => 1200],
            ['nombre' => 'Cama Individual (Colchón + Box)', 'categoria' => 'mudanza', 'precio_base' => 700],
            ['nombre' => 'Mesa de comedor', 'categoria' => 'mudanza', 'precio_base' => 500],
            ['nombre' => 'Silla de comedor', 'categoria' => 'mudanza', 'precio_base' => 100],
            ['nombre' => 'Nevera / Refrigerador', 'categoria' => 'ambos', 'precio_base' => 1000],
            ['nombre' => 'Estufa de cocina', 'categoria' => 'ambos', 'precio_base' => 600],
            ['nombre' => 'Lavadora / Secadora', 'categoria' => 'ambos', 'precio_base' => 900],
            ['nombre' => 'Televisor (Smart TV)', 'categoria' => 'ambos', 'precio_base' => 300],
            ['nombre' => 'Bicicleta', 'categoria' => 'ambos', 'precio_base' => 150],
            ['nombre' => 'Pallet de mercancía estándar', 'categoria' => 'transporte', 'precio_base' => 500],
            ['nombre' => 'Caja de carga general industrial', 'categoria' => 'transporte', 'precio_base' => 400],
            ['nombre' => 'Escritorio de oficina', 'categoria' => 'mudanza', 'precio_base' => 550],
        ];

        foreach ($articulos as $art) {
            DB::table('transporte_articulos')->insert([
                'nombre' => $art['nombre'],
                'categoria' => $art['categoria'],
                'precio_base' => $art['precio_base'],
                'estatus' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 3. Garantizar que las configuraciones existen
        $configs = [
            ['clave' => 'precio_km_transporte', 'valor' => '60'],
            ['clave' => 'precio_km_mudanza', 'valor' => '120'],
            ['clave' => 'limite_articulos_mudanza', 'valor' => '5'],
        ];

        foreach ($configs as $cfg) {
            DB::table('transporte_configuraciones')->updateOrInsert(
                ['clave' => $cfg['clave']],
                ['valor' => $cfg['valor'], 'updated_at' => now()]
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No borraremos todo por seguridad en down, pero podríamos vaciar artículos
        DB::table('transporte_articulos')->truncate();
    }
};
