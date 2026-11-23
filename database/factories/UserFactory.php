<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * Plain-text default password (hashed by the model's `hashed` cast).
     */
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => '07'.fake()->randomElement([7, 8, 9]).fake()->numerify('#######'),
            'password_hash' => static::$password ??= 'password',
            'user_img' => null,
            'is_admin' => false,
        ];
    }

    public function admin(): static
    {
        return $this->state(fn () => ['is_admin' => true]);
    }
}
