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
                $games = Game::all();

                $isFavorite = $user->game->mapWithKeys(function ($value, $key) {
                    return [$value->pivot?->game_id => $value->pivot?->isFavorite];
                });

                foreach ($games as $game) {
                    $game['isFavourite'] = $isFavorite[$game->id] ?? false;
                }
            } else {
                $games = Game::all();
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
}
