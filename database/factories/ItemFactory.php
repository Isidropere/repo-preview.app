<?php

namespace Database\Factories;

use App\Models\Item;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ItemFactory extends Factory
{
    protected $model = Item::class;

    public function definition(): array
    {
        return [
            'item'              => fake()->words(3, true),
            'id_categoria_item' => 1,
            'valor'             => fake()->randomFloat(2, 10, 1000),
            'descuento'         => 0,
            'presentacion'      => fake()->sentence(),
            'condicion'         => 1,
            'tipo_trans'        => 1,
            'id_user'           => User::factory(),
            'estatus'           => 1,
            'fecha'             => now(),
            'peso_lbs'          => 0,
            'alto_cm'           => 0,
            'ancho_cm'          => 0,
            'profundo_cm'       => 0,
            'id_tipo_item'      => 1,
            'tiene_video'       => false,
        ];
    }
}
