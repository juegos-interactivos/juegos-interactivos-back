<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nickname' => fake()->unique()->username(),
            'mail' => fake()->unique()->safeEmail(),
            'password' => static::$password ??= Hash::make('password123'),
            'image' => fake()->imageUrl(200, 200, 'people'),
            'level' => fake()->numberBetween(0, 100),
            'general_xp' => fake()->numberBetween(0, 10000),
            'isAdmin' => fake()->boolean(10),
        ];
    }
}

