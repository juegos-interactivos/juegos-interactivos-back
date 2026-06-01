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

    public function test_game_scores_include_score_time_and_pivot_update_date_sorted_by_score(): void
    {
        $game = Game::factory()->create();
        $firstUser = User::factory()->create(['is_disabled' => false]);
        $secondUser = User::factory()->create(['is_disabled' => false]);
        $admin = User::factory()->create(['isAdmin' => true, 'is_disabled' => false]);

        $firstUser->game()->attach($game->id, [
            'best_score' => 100,
            'best_time' => '00:01:00',
        ]);
        $secondUser->game()->attach($game->id, [
            'best_score' => 300,
            'best_time' => '00:02:00',
        ]);

        Sanctum::actingAs($admin);

        $response = $this->getJson("/api/games/{$game->id}/gameScores");

        $response->assertOk()
            ->assertJsonPath('data.0.nickname', $secondUser->nickname)
            ->assertJsonPath('data.0.best_score', 300)
            ->assertJsonPath('data.0.best_time', '00:02:00')
            ->assertJsonPath('data.1.nickname', $firstUser->nickname)
            ->assertJsonStructure([
                'data' => [
                    ['nickname', 'best_score', 'best_time', 'updated_at'],
                ],
            ]);
    }

    public function test_user_games_include_game_data_score_time_and_pivot_update_date(): void
    {
        $user = User::factory()->create(['is_disabled' => false]);
        $game = Game::factory()->create();
        $admin = User::factory()->create(['isAdmin' => true, 'is_disabled' => false]);

        $user->game()->attach($game->id, [
            'best_score' => 450,
            'best_time' => '00:01:45',
        ]);

        Sanctum::actingAs($admin);

        $response = $this->getJson("/api/users/{$user->id}/Games");

        $response->assertOk()
            ->assertJsonPath('data.0.game.id', $game->id)
            ->assertJsonPath('data.0.pivot.best_score', 450)
            ->assertJsonPath('data.0.pivot.best_time', '00:01:45')
            ->assertJsonStructure([
                'data' => [
                    [
                        'game',
                        'pivot' => ['best_score', 'best_time', 'updated_at'],
                    ],
                ],
            ]);
    }

    public function test_admin_can_update_user_with_partial_payload_without_image(): void
    {
        $admin = User::factory()->create(['isAdmin' => true, 'is_disabled' => false]);
        $user = User::factory()->create([
            'nickname' => 'OldName',
            'mail' => 'old@example.com',
            'image' => null,
            'is_disabled' => false,
        ]);

        Sanctum::actingAs($admin);

        $response = $this->postJson("/api/users/{$user->id}", [
            'nickname' => 'NewName',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.nickname', 'NewName')
            ->assertJsonPath('data.mail', 'old@example.com');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'nickname' => 'NewName',
            'mail' => 'old@example.com',
            'image' => null,
        ]);
    }
}
