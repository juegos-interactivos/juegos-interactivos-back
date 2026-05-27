<?php

use Illuminate\Http\Request;
use App\Http\Controllers\GameController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

// Rutas públicas de autenticación
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login'])->name('login');

// Rutas Juegos
Route::get('/games', [GameController::class, 'index']);
Route::get('/games/{game}', [GameController::class, 'show']);



// Rutas protegidas
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    // Rutas de juegos protegidas 
    Route::get('/games/{game}/score_time', [GameController::class, 'getScoreTime']);
    Route::get('/games/{game}/gameScores', [GameController::class, 'gameScores']);
    Route::post('/games/{game}', [GameController::class, 'update']);
    Route::post('/games/{game}/level_up', [GameController::class, 'updateLevelXp']);
    Route::delete('/games/{game}', [GameController::class, 'destroy']);
    Route::post('/games/{game}/toggle', [GameController::class, 'toggle']);
    Route::post('/games/{game}/favourite', [GameController::class, 'updateFavourite']);
    Route::post('/games/{game}/saveBestStats', [GameController::class, 'saveBestStats']);

    // Rutas Usuarios
    Route::get('/users', [UserController::class, 'index']);
    Route::get('/users/{user}', [UserController::class, 'show']);
    Route::get('/users/{user}/Games', [UserController::class, 'userGames']);
    Route::post('/users/{user}', [UserController::class, 'update']);
    Route::post('/users/{user}/toggleBan', [UserController::class, 'toggleBan']);
    Route::get('/users/{user}/UserScores', [UserController::class, 'UserScores']);
    Route::delete('/users/{user}', [UserController::class, 'destroy']);
});

