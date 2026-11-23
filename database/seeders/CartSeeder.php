<?php

namespace Database\Seeders;

use App\Models\Cart;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Database\Seeder;

class CartSeeder extends Seeder
{
    /** 10 carts: one per buyer (carts.user_id is unique). */
    public function run(): void
    {
        $userIds = User::buyersOnly()->orderBy('id')->pluck('id')->all();

        Cart::factory()
            ->count(10)
            ->state(new Sequence(fn (Sequence $s) => ['user_id' => $userIds[$s->index % count($userIds)]]))
            ->create();
    }
}
