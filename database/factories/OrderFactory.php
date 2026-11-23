<?php

namespace Database\Factories;

use App\Models\Address;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Totals start at 0; OrderItemSeeder recalculates them from the order's items.
 *
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    public function definition(): array
    {
        return [
            // Address owns the user, so the order's user and address always match.
            'address_id' => Address::factory(),
            'user_id' => fn (array $attributes) => Address::find($attributes['address_id'])->user_id,
            'coupon_id' => null,
            'order_number' => fake()->unique()->numerify('ORD-2026-######'),
            'subtotal' => 0,
            'discount_amount' => 0,
            'shipping_cost' => fake()->randomElement([0, 2, 3, 5]),
            'total_amount' => 0,
            'order_status' => fake()->randomElement(['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled']),
            'shipping_address' => fn (array $attributes) => Address::find($attributes['address_id'])->address_line,
        ];
    }
}
