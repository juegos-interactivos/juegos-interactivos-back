<?php

namespace Tests\Feature;

use App\Models\Game;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class GameProgressTest extends TestCase
{
    use RefreshDatabase;

    public function test_level_up_rolls_remaining_xp_after_crossing_threshold(): void
    {
        $user = User::factory()->create([
            'level' => 4,
            'general_xp' => 9900,
            'is_disabled' => false,
        ]);
        $game = Game::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->postJson("/api/games/{$game->id}/level_up", [
            'score' => 200,
        ]);

        $response->assertOk()
            ->assertJsonPath('data.level', 5)
            ->assertJsonPath('data.general_xp', 100)
            ->assertJsonPath('data.xp_gained', 200)
            ->assertJsonPath('data.xp_to_next_level', 9900);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'level' => 5,
            'general_xp' => 100,
        ]);
    }

    public function test_level_up_can_gain_multiple_levels(): void
    {
        $user = User::factory()->create([
            'level' => 1,
            'general_xp' => 0,
            'is_disabled' => false,
        ]);
        $game = Game::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->postJson("/api/games/{$game->id}/level_up", [
            'score' => 25000,
        ]);

        $response->assertOk()
            ->assertJsonPath('data.level', 3)
            ->assertJsonPath('data.general_xp', 5000)
            ->assertJsonPath('data.xp_gained', 25000)
            ->assertJsonPath('data.xp_to_next_level', 5000);
    }

    public function test_save_best_stats_replaces_score_and_time_only_when_score_is_higher(): void
    {
        $user = User::factory()->create(['is_disabled' => false]);
        $game = Game::factory()->create();
        $user->game()->attach($game->id, [
            'best_score' => 100,
            'best_time' => '00:02:00',
        ]);

        Sanctum::actingAs($user);

        $this->postJson("/api/games/{$game->id}/saveBestStats", [
            'score' => 150,
            'time' => '00:03:00',
        ])->assertOk()->assertJsonPath('data.best_saved', true);

        $this->assertDatabaseHas('game_user', [
            'user_id' => $user->id,
            'game_id' => $game->id,
            'best_score' => 150,
            'best_time' => '00:03:00',
        ]);

        $this->postJson("/api/games/{$game->id}/saveBestStats", [
            'score' => 120,
            'time' => '00:01:00',
        ])->assertOk()->assertJsonPath('data.best_saved', false);

        $this->assertDatabaseHas('game_user', [
            'user_id' => $user->id,
            'game_id' => $game->id,
            'best_score' => 150,
            'best_time' => '00:03:00',
        ]);
    }

    public function test_save_best_stats_replaces_time_when_score_is_equal_and_time_is_lower(): void
    {
        $user = User::factory()->create(['is_disabled' => false]);
        $game = Game::factory()->create();
        $user->game()->attach($game->id, [
            'best_score' => 100,
            'best_time' => '00:02:00',
        ]);

        Sanctum::actingAs($user);

        $this->postJson("/api/games/{$game->id}/saveBestStats", [
            'score' => 100,
            'time' => '00:01:30',
        ])->assertOk()->assertJsonPath('data.best_saved', true);

        $this->assertDatabaseHas('game_user', [
            'user_id' => $user->id,
            'game_id' => $game->id,
            'best_score' => 100,
            'best_time' => '00:01:30',
        ]);
    }
}
