<?php

namespace Database\Seeders;

use App\Models\Game;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class GameSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the games table.
     */
    public function run(): void
    {
        // Crear juegos con datos más realistas
        $games = [
            [
                'name' => 'Aventura en la Jungla',
                'image' => 'https://via.placeholder.com/400x300.png?text=Jungle+Adventure',
                'isActive' => true,
            ],
            [
                'name' => 'Carreras Espaciales',
                'image' => 'https://via.placeholder.com/400x300.png?text=Space+Races',
                'isActive' => true,
            ],
            [
                'name' => 'Puzzle Maestro',
                'image' => 'https://via.placeholder.com/400x300.png?text=Puzzle+Master',
                'isActive' => true,
            ],
            [
                'name' => 'Batalla de Dragones',
                'image' => 'https://via.placeholder.com/400x300.png?text=Dragon+Battle',
                'isActive' => true,
            ],
            [
                'name' => 'Salto a las Nubes',
                'image' => 'https://via.placeholder.com/400x300.png?text=Cloud+Jump',
                'isActive' => true,
            ],
            [
                'name' => 'Misterio en el Castillo',
                'image' => 'https://via.placeholder.com/400x300.png?text=Castle+Mystery',
                'isActive' => true,
            ],
            [
                'name' => 'Carrera Submarina',
                'image' => 'https://via.placeholder.com/400x300.png?text=Submarine+Race',
                'isActive' => false,
            ],
            [
                'name' => 'Desafío Matemático',
                'image' => 'https://via.placeholder.com/400x300.png?text=Math+Challenge',
                'isActive' => true,
            ],
        ];

        foreach ($games as $game) {
            Game::create($game);
        }

        // Crear 5 juegos adicionales generados aleatoriamente
        Game::factory(5)->create();
    }
}
