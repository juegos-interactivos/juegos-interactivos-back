<?php

namespace Database\Seeders;

use App\Models\Game;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class GameUserSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the game_user pivot table.
     */
    public function run(): void
    {
        $users = User::all();
        $games = Game::all();

        // Asignar juegos a usuarios de forma aleatoria
        foreach ($users as $user) {
            // Cada usuario juega entre 2 y 8 juegos
            $randomGames = $games->random(rand(2, min(8, $games->count())));
            
            foreach ($randomGames as $game) {
                $user->games()->attach($game->id, [
                    'best_score' => rand(100, 10000),
                    'best_time' => sprintf('%02d:%02d:%02d', rand(0, 23), rand(0, 59), rand(0, 59)),
                    'isFavorite' => rand(0, 1),
                ]);
            }
        }
    }
}
