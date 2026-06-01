<?php

namespace App\Http\Controllers;

use App\Models\Game;
use Illuminate\Http\Request;
use Throwable;
use Illuminate\Validation\ValidationException;
use Exception;

class GameController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $user = $request->user() ?: auth('sanctum')->user();
            $games = Game::where('isActive', true)->get();

            if ($user && $user->is_disabled != 1) {
                if($user->isAdmin){
                    $games = Game::all();
                }

                $isFavorite = $user->game->mapWithKeys(function ($value, $key) {
                    return [$value->pivot?->game_id => $value->pivot?->isFavorite];
                });

                foreach ($games as $game) {
                    $game['isFavourite'] = (bool) ($isFavorite[$game->id] ?? false);
                }
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

            if ($user && $user->is_disabled != 1) {
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
            $userAuth = $request->user() ?: auth('sanctum')->user();

            if($userAuth->isAdmin !== 1) Throw new Exception('No tienes permisos para realizar estas acciones');

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

            if($user->is_disabled != 0) Throw new Exception();

            $xpToAdd = $validated['score'];

            $user->general_xp = ($user->general_xp ?? 0) + $xpToAdd;

            while ($user->general_xp >= 10000) {
                $user->general_xp -= 10000;
                $user->level = ($user->level ?? 0) + 1;
            }

            $user->save();

            return response()->json([
                'data' => [
                    'general_xp' => $user->general_xp,
                    'level' => $user->level,
                    'xp_gained' => $xpToAdd,
                    'xp_to_next_level' => 10000 - $user->general_xp,
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

    public function saveBestStats(Request $request, Game $game)
    {
        try {
            $validated = $request->validate([
                'score' => 'required|integer|min:0',
                'time' => 'required|date_format:H:i:s',

            ]);

            $user = $request->user() ?: auth('sanctum')->user();
            
            if($user->is_disabled != 0) Throw new Exception();

            $related = $user->game()->where('game_id', $game->id)->first();
            $saved = false;

            $newScore = $validated['score'];
            $newTime = $validated['time'];

            if (! $related) {
                $user->game()->attach($game->id, ['best_score' => $newScore, 'best_time' => $newTime]);
                $saved = true;
            } else {
                $currentScore = $related->pivot?->best_score ?? 0;
                $currentTime = $related->pivot?->best_time ?? '00:00:00';

                if ($newScore > $currentScore) {
                    $user->game()->updateExistingPivot($game->id, ['best_score' => $newScore, 'best_time' => $newTime]);
                    $saved = true;
                    
                } elseif ($newScore == $currentScore) {
                    if ($currentTime === '00:00:00' || $newTime < $currentTime) {
                        $user->game()->updateExistingPivot($game->id, ['best_score' => $newScore, 'best_time' => $newTime]);
                        $saved = true;
                    }
                }
            }

            return response()->json([
                'data' => [
                    'best_saved' => $saved,
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
            $userAuth = $request->user() ?: auth('sanctum')->user();

            if($userAuth->isAdmin !== 1) Throw new Exception('No tienes permisos para realizar estas acciones');

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

            if($user->is_disabled != 0) Throw new Exception();

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
    public function destroy(Game $game, Request $request)
    {
        try {
            $userAuth = $request->user() ?: auth('sanctum')->user();

            if($userAuth->isAdmin !== 1) Throw new Exception('No tienes permisos para realizar estas acciones');

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
                    'best_score' => $u->pivot?->best_score ?? 0,
                    'best_time' => $u->pivot?->best_time ?? null,
                ];
            })
                ->filter(fn ($row) => $row['best_time'] && $row['best_time'] !== '00:00:00')
                ->sortBy('best_time')
                ->values();

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
