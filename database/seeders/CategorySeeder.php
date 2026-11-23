<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $names = [
            'Electronics', 'Fashion', 'Home & Garden', 'Books', 'Sports & Outdoors',
            'Toys & Games', 'Beauty', 'Automotive', 'Music & Instruments', 'Art & Collectibles',
        ];

        foreach ($names as $name) {
            Category::factory()->create(['name' => $name]);
        }
    }
}
