<?php

namespace Database\Factories;

use App\Models\Seller;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Seller>
 */
class SellerFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'national_id' => fake()->unique()->numerify('99########'),
            'official_name' => fake()->unique()->company(),
            'national_id_image' => null,
            'verification_status' => 'approved',
            'reviewed_at' => now(),
        ];
    }

    /** An application waiting for the admin. */
    public function pending(): static
    {
        return $this->state(['verification_status' => 'pending', 'reviewed_at' => null]);
    }
}
