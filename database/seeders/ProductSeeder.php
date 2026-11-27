<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Seller;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $sellerIds = Seller::orderBy('user_id')->pluck('user_id')->all();
        $categoryIds = Category::orderBy('id')->pluck('id')->all();

        Product::factory()
            ->count(10)
            ->state(new Sequence(fn (Sequence $s) => [
                'seller_id' => $sellerIds[$s->index % count($sellerIds)],
                'category_id' => $categoryIds[$s->index % count($categoryIds)],
            ]))
            ->create();
    }
}
