<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Registrar un nuevo usuario
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'nickname' => 'required|string|unique:users|max:255',
            'mail' => 'required|email|unique:users',
            'password' => 'required|string|min:8',
            'image' => 'required|string',
        ]);

        try {
            $user = User::create([
                'nickname' => $validated['nickname'],
                'mail' => $validated['mail'],
                'password' => Hash::make($validated['password']),
                'image' => $validated['image'] ?? null,
                'level' => 0,
                'general_xp' => 0,
                'isAdmin' => false,
            ]);

            $token = $user->createToken('auth_token')->plainTextToken;
            $user->token = $token;
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Usuario registrado correctamente',
                'user' => $user,
                'token' => $token,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar el usuario',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Iniciar sesión
     */
    public function login(Request $request)
    {
        $request->validate([
            'mail' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('mail', $request->mail)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'mail' => 'Las credenciales proporcionadas no son correctas.',
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;
        $user->token = $token;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Sesión iniciada correctamente',
            'user' => $user,
            'token' => $token,
        ], 200);
    }

    /**
     * Cerrar sesión
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        $user = $request->user();
        if ($user) {
            $user->token = null;
            $user->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Sesión cerrada correctamente',
        ], 200);
    }

    /**
     * Obtener usuario autenticado
     */
    public function me(Request $request)
    {
        return response()->json([
            'success' => true,
            'user' => $request->user(),
        ], 200);
    }
}
