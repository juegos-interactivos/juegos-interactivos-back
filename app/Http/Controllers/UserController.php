<?php

namespace App\Http\Controllers;

use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Throwable;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $userAuth = $request->user() ?: auth('sanctum')->user();

            if($userAuth->isAdmin !== 1) Throw new Exception();

            $Users = User::all();

            return response()->json([
                'data' => $Users,
            ], 200);
        } catch (Throwable $e) {
            return response()->json([
                'error' => 'No se han podido obtener los usuarios',
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, User $User)
    {
        try {
            $userAuth = $request->user() ?: auth('sanctum')->user();

            if($userAuth->isAdmin !== 1 && $userAuth->id !== $User->id) Throw new Exception();

            return response()->json([
                'data' => $User,
            ], 200);
        } catch (Throwable $e) {
            return response()->json([
                'error' => 'No se ha podido obtener el usuario',
            ], 500);
        }
    }

    /**
     * Devuelve la tabla entre usuarios y juegos.
     */
    public function UserGames(Request $request, User $User)
    {
        try {
            $userAuth = $request->user() ?: auth('sanctum')->user();

            if($userAuth->isAdmin !== 1 && $userAuth->id !== $User->id) Throw new Exception();

            $games = $User->game()->get();

            $result = $games->map(function ($g) {
                $gameData = $g->toArray();
                // remove nested pivot from game data to avoid duplication
                if (isset($gameData['pivot'])) {
                    unset($gameData['pivot']);
                }

                $pivot = $g->pivot ? [
                    'best_score' => $g->pivot->best_score ?? null,
                    'best_time' => $g->pivot->best_time ?? null,
                ] : ['best_score' => null, 'best_time' => null];

                return [
                    'game' => $gameData,
                    'pivot' => $pivot,
                ];
            })->values();

            return response()->json([
                'data' => $result,
            ], 200);
        } catch (Throwable $e) {
            return response()->json([
                'error' => 'No se han podido obtener las pivots del usuario',
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $User)
    {
        try {
            $userAuth = $request->user() ?: auth('sanctum')->user();

            if($userAuth->isAdmin !== 1 && $userAuth->id !== $User->id) Throw new Exception('No tienes permisos para realizar estas acciones');

            $validated = $request->validate([
                'nickname' => 'sometimes|string',
                'password' => 'sometimes|string',
                'mail' => 'sometimes|email',
                'image' => 'sometimes|string',
            ]);

            $userExists = false;
            $mailExists = false;

            if (isset($validated['nickname']) && $User->nickname === $validated['nickname']) {
                unset($validated['nickname']);
            }

            if (isset($validated['mail']) && $User->mail === $validated['mail']) {
                unset($validated['mail']);
            }


            if (isset($validated['nickname'])) {
                $userExists = User::where('nickname', $validated['nickname'])->exists();
            }

            if (isset($validated['mail'])) {
                $mailExists = User::where('mail', $validated['mail'])->exists();
            }
            if ($userExists) {
                throw ValidationException::withMessages([
                    'username' => ['El usuario ya existe']
                ]);
            }

            if ($mailExists) {
                throw ValidationException::withMessages([
                    'email' => ['El mail ya existe']
                ]);
            }

            $User->update($validated);

            return response()->json([
                'data' => $User,
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'errors' => $e->errors(), 
            ], 422);
        } catch (Throwable $e) {
            return response()->json([
                'error' => $e ?? 'No se ha podido actualizar el usuario',
            ], 500);
        }
    }

    public function toggleBan(Request $request, User $user)
    {
        try {
            $userAuth = $request->user() ?: auth('sanctum')->user();

            if($userAuth->isAdmin !== 1) Throw new Exception('No tienes permisos para realizar estas acciones');


            $user->is_disabled = !$user->is_disabled;
            $user->save();

            return response()->json([
                'data' => $user,
            ], 200);
        } catch (Throwable $e) {
            return response()->json([
                'error' => 'No se ha podido actualizar el estado del usuario',
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, User $User)
    {
        try {
            $userAuth = $request->user() ?: auth('sanctum')->user();

            if($userAuth->isAdmin !== 1 && $userAuth->id !== $User->id) Throw new Exception();
        
            $User->delete();

            return response()->json([
                'data' => true,
            ], 200);
        } catch (Throwable $e) {
            return response()->json([
                'error' => 'No se ha podido eliminar el usuario',
            ], 500);
        }
    }
}
