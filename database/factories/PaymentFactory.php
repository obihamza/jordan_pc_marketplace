<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'payment_method' => 'cash_on_delivery',
            'amount_jod' => fn (array $a) => Order::find($a['order_id'])->total_amount,
            'payment_status' => fake()->randomElement(['pending', 'authorized', 'paid', 'failed', 'refunded']),
        ];
    }
}
