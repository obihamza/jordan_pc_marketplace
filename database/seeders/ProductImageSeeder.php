<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Database\Seeder;

class ProductImageSeeder extends Seeder
{
    /** 10 images: one primary image per product. */
    public function run(): void
    {
        $productIds = Product::orderBy('id')->pluck('id')->all();

        ProductImage::factory()
            ->count(10)
            ->primary()
            ->state(new Sequence(fn (Sequence $s) => ['product_id' => $productIds[$s->index % count($productIds)]]))
            ->create();
    }
}
