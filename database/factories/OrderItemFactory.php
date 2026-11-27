<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderItemFactory extends Factory
{
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'product_id' => Product::factory(),

            'seller_id' => fn (array $a) => Product::find($a['product_id'])->seller_id,
            'product_name' => fn (array $a) => Product::find($a['product_id'])->name,
            'unit_price' => fn (array $a) => Product::find($a['product_id'])->price,
            'quantity' => fake()->numberBetween(1, 3),
            'subtotal' => fn (array $a) => round($a['unit_price'] * $a['quantity'], 2),
            'item_status' => 'pending',
        ];
    }
}
