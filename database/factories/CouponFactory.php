<?php

namespace Database\Factories;

use App\Models\Coupon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Coupon>
 */
class CouponFactory extends Factory
{
    public function definition(): array
    {
        $type = fake()->randomElement([Coupon::TYPE_PERCENT, Coupon::TYPE_FIXED]);

        return [
            'code' => strtoupper(fake()->unique()->bothify('SAVE-????-###')),
            'discount_type' => $type,
            'discount_value' => $type === Coupon::TYPE_PERCENT
                ? fake()->randomElement([5, 10, 15, 20])
                : fake()->randomElement([2, 3, 5, 10]),
            'min_order_amount' => fake()->randomElement([0, 10, 20, 50]),
            'usage_limit' => fake()->optional(0.7)->numberBetween(10, 200),
            'used_count' => 0,
            'is_active' => true,
            'starts_at' => now()->subDays(fake()->numberBetween(1, 30)),
            'ends_at' => fake()->boolean(70) ? now()->addDays(fake()->numberBetween(10, 120)) : null,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }
}
