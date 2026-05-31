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
        $users = DB::table('users')
            ->whereIn('mail', [
                'sergio@example.com',
                'alexis@example.com',
                'nacho@example.com',
                'eduard@example.com',
            ])
            ->pluck('id', 'mail');

        if ($users->count() < 4) {
            return;
        }

        $rows = [
            'sergio@example.com' => [
                1 => ['score' => 1180, 'time' => '00:01:34'],
                2 => ['score' => 1540, 'time' => '00:00:58'],
                3 => ['score' => 920, 'time' => '00:02:12'],
                4 => ['score' => 1320, 'time' => '00:01:46'],
                5 => ['score' => 1660, 'time' => '00:03:28'],
                6 => ['score' => 1040, 'time' => '00:01:09'],
            ],
            'alexis@example.com' => [
                1 => ['score' => 1260, 'time' => '00:01:21'],
                2 => ['score' => 1480, 'time' => '00:01:04'],
                3 => ['score' => 980, 'time' => '00:02:01'],
                4 => ['score' => 1400, 'time' => '00:01:32'],
                5 => ['score' => 1720, 'time' => '00:03:09'],
                6 => ['score' => 1110, 'time' => '00:01:02'],
            ],
            'nacho@example.com' => [
                1 => ['score' => 1210, 'time' => '00:01:28'],
                2 => ['score' => 1610, 'time' => '00:00:52'],
                3 => ['score' => 1010, 'time' => '00:01:54'],
                4 => ['score' => 1350, 'time' => '00:01:40'],
                5 => ['score' => 1580, 'time' => '00:03:42'],
                6 => ['score' => 1190, 'time' => '00:00:56'],
            ],
            'eduard@example.com' => [
                1 => ['score' => 1300, 'time' => '00:01:16'],
                2 => ['score' => 1700, 'time' => '00:00:49'],
                3 => ['score' => 1080, 'time' => '00:01:48'],
                4 => ['score' => 1490, 'time' => '00:01:23'],
                5 => ['score' => 1800, 'time' => '00:02:58'],
                6 => ['score' => 1230, 'time' => '00:00:51'],
            ],
        ];

        foreach ($rows as $mail => $games) {
            foreach ($games as $gameId => $stats) {
                DB::table('game_user')->updateOrInsert(
                    [
                        'user_id' => $users[$mail],
                        'game_id' => $gameId,
                    ],
                    [
                        'best_score' => $stats['score'],
                        'best_time' => $stats['time'],
                        'isFavorite' => false,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]
                );
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $userIds = DB::table('users')
            ->whereIn('mail', [
                'sergio@example.com',
                'alexis@example.com',
                'nacho@example.com',
                'eduard@example.com',
            ])
            ->pluck('id');

        DB::table('game_user')
            ->whereIn('user_id', $userIds)
            ->whereIn('game_id', [1, 2, 3, 4, 5, 6])
            ->delete();
    }
};
