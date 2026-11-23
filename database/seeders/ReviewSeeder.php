<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    /** 10 reviews spread across buyers and products. */
    public function run(): void
    {
        $userIds = User::buyersOnly()->orderBy('id')->pluck('id')->all();
        $productIds = Product::orderBy('id')->pluck('id')->all();

        Review::factory()
            ->count(10)
            ->state(new Sequence(fn (Sequence $s) => [
                'user_id' => $userIds[$s->index % count($userIds)],
                'product_id' => $productIds[($s->index + 2) % count($productIds)],
            ]))
            ->create();
    }
}
