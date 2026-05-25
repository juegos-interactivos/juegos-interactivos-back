<?php

namespace App\Http\Controllers;

use App\Models\Game;
use Illuminate\Http\Request;
use Throwable;
use Illuminate\Validation\ValidationException;

class GameController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $user = $request->user() ?: auth('sanctum')->user();

            if ($user) {
                if($user->isAdmin){
                    $games = Game::all();
                }else{
                    $games = Game::where('isActive', true)->get();
                }

                $isFavorite = $user->game->mapWithKeys(function ($value, $key) {
                    return [$value->pivot?->game_id => $value->pivot?->isFavorite];
                });

                foreach ($games as $game) {
                    $game['isFavourite'] = (bool) ($isFavorite[$game->id] ?? false);
                }
            } else {
                $games = Game::where('isActive', true)->get();
            }

            return response()->json([
                'data' => $games,
            ], 200);
        } catch (Throwable $e) {
            return response()->json([
                'error' => 'No se han podido obtener los juegos',
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Game $game)
    {
        try {
            $user = $request->user() ?: auth('sanctum')->user();

            if ($user) {
                $extraData = $user->game->where('pivot.game_id', $game->id)->mapWithKeys(function ($value, $key) {
                    return ['best_score' => $value->pivot?->best_score , 'best_time' => $value->pivot?->best_time];
                });

                $game['best_score'] = $extraData['best_score'] ?? 0;
                $game['best_time'] = $extraData['best_time'] ?? '00:00:00';
            }

            return response()->json([
                'data' => $game,
            ], 200);
        } catch (Throwable $e) {
            return response()->json([
                'error' => 'No se ha podido obtener el juego',
            ], 500);
        }
    }
    
    public function getScoreTime(Request $request, Game $game)
    {
        try {
            $user = $request->user() ?: auth('sanctum')->user();

            if (! $user) {
                return response()->json([
                    'error' => 'No autorizado',
                ], 401);
            }

            $related = $user->game()->where('game_id', $game->id)->first();

            $best_score = $related?->pivot?->best_score ?? 0;
            $best_time = $related?->pivot?->best_time ?? '00:00:00';

            return response()->json([
                'data' => [
                    'best_score' => $best_score,
                    'best_time' => $best_time,
                ],
            ], 200);
        } catch (Throwable $e) {
            return response()->json([
                'error' => 'No se ha podido obtener la puntuacion y tiempo',
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Game $game)
    {
        try {
            $validated = $request->validate([
                'name' => 'sometimes|string',
                'image' => 'sometimes|string',
                'isActive' => 'sometimes|boolean',
            ]);

            $game->update($validated);

            return response()->json([
                'data' => $game,
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'error' => 'No cumple la validación',
            ], 422);
        } catch (Throwable $e) {
            return response()->json([
                'error' => 'No se ha podido actualizar el juego',
            ], 500);
        }
    }

    public function updateLevelXp(Request $request, Game $game)
    {
        try {
            $validated = $request->validate([
                'score' => 'required|integer|min:0',
            ]);

            $user = $request->user() ?: auth('sanctum')->user();

            if (! $user) {
                return response()->json([
                    'error' => 'No autorizado',
                ], 401);
            }

            $xpToAdd = (int) $validated['score'] * 0.1;

            $user->general_xp = (int) ($user->general_xp ?? 0) + $xpToAdd;
            $user->level = intdiv($user->general_xp, 200);
            $user->save();

            return response()->json([
                'data' => [
                    'general_xp' => $user->general_xp,
                    'level' => $user->level,
                    'xp_gained' => $xpToAdd,
                ],
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'error' => 'No cumple la validación',
            ], 422);
        } catch (Throwable $e) {
            return response()->json([
                'error' => 'No se ha podido actualizar XP y nivel',
            ], 500);
        }
    }

    public function toggle(Request $request, Game $game)
    {
        try {
            $game->isActive = !$game->isActive;
            $game->save();

            return response()->json([
                'data' => $game,
            ], 200);
        } catch (Throwable $e) {
            return response()->json([
                'error' => 'No se ha podido actualizar el estado del juego',
            ], 500);
        }
    }

    public function updateFavourite(Request $request, Game $game)
    {
        try {
            $user = $request->user() ?: auth('sanctum')->user();

            if (! $user) {
                return response()->json([
                    'error' => 'No autorizado',
                ], 401);
            }

            $related = $user->game()->where('game_id', $game->id)->first();

            if (! $related) {
                $user->game()->attach($game->id, ['isFavorite' => true]);
                $newValue = true;
            } else {
                $current = (bool) ($related->pivot?->isFavorite ?? false);
                $newValue = ! $current;
                $user->game()->updateExistingPivot($game->id, ['isFavorite' => $newValue]);
            }

            return response()->json([
                'data' => ['isFavourite' => $newValue],
            ], 200);
        } catch (Throwable $e) {
            return response()->json([
                'error' => 'No se ha podido marcar como favorito',
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Game $game)
    {
        try {
            $game->delete();

            return response()->json([
                'data' => true,
            ], 200);
        } catch (Throwable $e) {
            return response()->json([
                'error' => 'No se ha podido eliminar el juego',
            ], 500);
        }
    }

    /**
     * Return all pivot rows for a given game (users + pivot limited fields).
     */
    public function gameScores(Request $request, Game $game)
    {
        try {
            $users = $game->user()->get();

            $rows = $users->map(function ($u) {
                return [
                    'nickname' => $u->nickname,
                    'level' => $u->level,
                    'best_score' => $u->pivot?->best_score ?? 0,
                    'best_time' => $u->pivot?->best_time ?? null,
                ];
            })->sortByDesc('best_score')->values();

            $gameData = $game->toArray();

            return response()->json([
                'game' => $gameData,
                'data' => $rows,
            ], 200);
        } catch (Throwable $e) {
            return response()->json([
                'error' => 'No se han podido obtener las pivots del juego',
            ], 500);
        }
    }

}
