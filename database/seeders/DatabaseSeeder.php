<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            CategoriaItemSeeder::class,
            SystemDataSeeder::class,
        ]);

        if (User::where('email', 'test@example.com')->doesntExist()) {
            User::factory()->create([
                'nombres' => 'Test',
                'apellidos' => 'User',
                'email' => 'test@example.com',
            ]);
        }
    }
}
