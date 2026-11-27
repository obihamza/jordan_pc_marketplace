<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use App\Models\Seller;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    public function definition(): array
    {
        return [
            'seller_id' => Seller::factory(),
            'category_id' => Category::factory(),
            'name' => fake()->words(3, true),
            'brand' => fake()->optional(0.8)->company(),
            'description' => fake()->paragraph(),
            'price' => fake()->randomFloat(2, 3, 500),
            'stock_quantity' => fake()->numberBetween(1, 50),
            'condition_type' => fake()->randomElement(['new', 'used']),
            'listing_status' => 'available',
            'product_variant' => fake()->optional(0.5)->randomElement(['Red', 'Blue', 'Black', 'Large', 'Medium', 'Small']),
        ];
    }

    public function hidden(): static
    {
        return $this->state(fn () => ['listing_status' => 'hidden']);
    }
}
