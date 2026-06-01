<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $now = now();
        $images = [
            1 => 'parejas.png',
            2 => 'mole.png',
            3 => 'simon.png',
            4 => 'red.png',
            5 => 'buscaminas.png',
            6 => 'numeros.png',
        ];

        foreach ($images as $gameId => $image) {
            DB::table('games')
                ->where('id', $gameId)
                ->update([
                    'image' => $image,
                    'updated_at' => $now,
                ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('games')
            ->where('id', 5)
            ->update([
                'image' => 'buscaminas.jpeg',
                'updated_at' => now(),
            ]);
    }
};
