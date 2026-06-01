<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $now = now();

        $games = [
            [
                'id' => 1,
                'name' => 'Parejas',
                'image' => 'parejas.png',
                'isActive' => true,
            ],
            [
                'id' => 2,
                'name' => 'Atrapa al topo',
                'image' => 'mole.png',
                'isActive' => true,
            ],
            [
                'id' => 3,
                'name' => 'Simon',
                'image' => 'simon.png',
                'isActive' => true,
            ],
            [
                'id' => 4,
                'name' => 'Conector de red',
                'image' => 'red.png',
                'isActive' => true,
            ],
            [
                'id' => 5,
                'name' => 'Buscaminas',
                'image' => 'buscaminas.png',
                'isActive' => true,
            ],
            [
                'id' => 6,
                'name' => 'Adivina el numero',
                'image' => 'numeros.png',
                'isActive' => true,
            ],
        ];

        foreach ($games as $game) {
            DB::table('games')->updateOrInsert(
                ['id' => $game['id']],
                [
                    'name' => $game['name'],
                    'image' => $game['image'],
                    'isActive' => $game['isActive'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }

        $users = [
            [
                'nickname' => 'Sergio',
                'mail' => 'sergio@example.com',
                'isAdmin' => false,
            ],
            [
                'nickname' => 'Alexis',
                'mail' => 'alexis@example.com',
                'isAdmin' => false,
            ],
            [
                'nickname' => 'Nacho',
                'mail' => 'nacho@example.com',
                'isAdmin' => false,
            ],
            [
                'nickname' => 'Eduard',
                'mail' => 'eduard@example.com',
                'isAdmin' => true,
            ],
        ];

        foreach ($users as $user) {
            DB::table('users')->updateOrInsert(
                ['mail' => $user['mail']],
                [
                    'nickname' => $user['nickname'],
                    'password' => Hash::make('12345678'),
                    'image' => null,
                    'level' => 0,
                    'general_xp' => 0,
                    'isAdmin' => $user['isAdmin'],
                    'is_disabled' => false,
                    'token' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('users')
            ->whereIn('mail', [
                'sergio@example.com',
                'alexis@example.com',
                'nacho@example.com',
                'eduard@example.com',
            ])
            ->delete();

        DB::table('games')
            ->whereIn('id', [1, 2, 3, 4, 5, 6])
            ->delete();
    }
};
