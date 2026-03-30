<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Crear un usuario de prueba
        User::create([
            'nickname' => 'admin',
            'mail' => 'admin@example.com',
            'password' => bcrypt('password123'),
            'image' => 'https://via.placeholder.com/200x200.png?text=Admin',
            'level' => 100,
            'general_xp' => 50000,
            'isAdmin' => true,
        ]);

        // Crear usuarios adicionales
        User::factory(10)->create();

        // Ejecutar los otros seeders
        $this->call([
            GameSeeder::class,
            GameUserSeeder::class,
        ]);
    }
}

