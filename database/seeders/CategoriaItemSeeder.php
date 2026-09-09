<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategoriaItemSeeder extends Seeder
{
    public function run(): void
    {
        $categorias = [
            ['id_categoria_item' => 1,  'categoria' => 'Instrumentos musicales'],
            ['id_categoria_item' => 2,  'categoria' => 'Electrodomésticos'],
            ['id_categoria_item' => 3,  'categoria' => 'Electrónicos'],
            ['id_categoria_item' => 4,  'categoria' => 'Juegos'],
            ['id_categoria_item' => 5,  'categoria' => 'Muebles'],
            ['id_categoria_item' => 6,  'categoria' => 'Vehículos'],
            ['id_categoria_item' => 7,  'categoria' => 'Herramientas'],
            ['id_categoria_item' => 8,  'categoria' => 'Joyas'],
            ['id_categoria_item' => 9,  'categoria' => 'Clases / Lecciones'],
            ['id_categoria_item' => 10, 'categoria' => 'Monetario'],
            ['id_categoria_item' => 11, 'categoria' => 'Adultos'],
            ['id_categoria_item' => 12, 'categoria' => 'Inmuebles'],
            ['id_categoria_item' => 13, 'categoria' => 'Cuidado personal'],
            ['id_categoria_item' => 14, 'categoria' => 'Decoraciones'],
            ['id_categoria_item' => 15, 'categoria' => 'Deportes'],
            ['id_categoria_item' => 16, 'categoria' => 'Hogar'],
            ['id_categoria_item' => 17, 'categoria' => 'Jardín'],
            ['id_categoria_item' => 18, 'categoria' => 'Calzados'],
            ['id_categoria_item' => 19, 'categoria' => 'Teléfonos'],
            ['id_categoria_item' => 20, 'categoria' => 'Niños'],
            ['id_categoria_item' => 21, 'categoria' => 'Antigüedades'],
            ['id_categoria_item' => 22, 'categoria' => 'Niñas'],
            ['id_categoria_item' => 23, 'categoria' => 'Mascotas'],
            ['id_categoria_item' => 24, 'categoria' => 'Tecnología'],
            ['id_categoria_item' => 25, 'categoria' => 'Librería y Papelería'],
            ['id_categoria_item' => 26, 'categoria' => 'Damas'],
            ['id_categoria_item' => 27, 'categoria' => 'Caballeros'],
            ['id_categoria_item' => 28, 'categoria' => 'Oficina'],
            ['id_categoria_item' => 29, 'categoria' => 'Talentos-Servicios'],
        ];

        foreach ($categorias as $cat) {
            DB::table('categorias_item')->updateOrInsert(
                ['id_categoria_item' => $cat['id_categoria_item']],
                ['categoria' => $cat['categoria']]
            );
        }

        echo "✅ " . count($categorias) . " categorías insertadas en la base de datos.\n";
    }
}
