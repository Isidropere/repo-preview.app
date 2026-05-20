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
        // Se ejecuta si la tabla existe y está vacía
        if (Schema::hasTable('transporte_articulos')) {
            $count = DB::table('transporte_articulos')->count();
            if ($count === 0) {
                $articulos = [
                    // Mudanza Residencial/Comercial
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

                    // Transporte de Carga / Mercancía
                    ['nombre' => 'Pallet de mercancía estándar', 'categoria' => 'transporte'],
                    ['nombre' => 'Caja de herramientas industrial', 'categoria' => 'transporte'],
                    ['nombre' => 'Sacos de cemento / arena', 'categoria' => 'transporte'],
                    ['nombre' => 'Varillas / Tubos de metal (lote)', 'categoria' => 'transporte'],
                    ['nombre' => 'Equipaje / Maletas de carga pesada', 'categoria' => 'transporte'],
                    ['nombre' => 'Caja de carga general industrial', 'categoria' => 'transporte'],

                    // Ambos / Mixtos
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
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('transporte_articulos')->truncate();
    }
};
