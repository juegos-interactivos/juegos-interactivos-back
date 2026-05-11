<?php

namespace App\Http\Controllers;

use App\Models\Game;
use Illuminate\Http\Request;

class GameController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $games = Game::all();
        return response($games, 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(Game $game)
    {
        return $game;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Game $game)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string',
            'image' => 'sometimes|string',
            'isActive' => 'sometimes|boolean'
        ]);

        $game->update($validated);
        return $game;
    }

    public function disable(Request $request, Game $game)
    {
        $validated = $request->validate([
            'isActive' => 'required|boolean'
        ]);

        $game->update($validated);
        return $game;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Game $game)
    {
        //
    }
}
