<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            // Hanya huruf, angka, dan underscore agar lolos aturan alpha_dash.
            'username' => fake()->unique()->lexify('user_??????'),
            'email' => fake()->unique()->safeEmail(),
            'role' => User::ROLE_MEMBER,
            'profile_photo_path' => null,
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    public function admin(): static
    {
        return $this->state(fn (array $attributes) => ['role' => User::ROLE_ADMIN]);
    }

    public function member(): static
    {
        return $this->state(fn (array $attributes) => ['role' => User::ROLE_MEMBER]);
    }
}
