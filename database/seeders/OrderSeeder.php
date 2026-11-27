<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $addressIds = Address::orderBy('id')->pluck('id')->all();

        Order::factory()
            ->count(10)
            ->state(new Sequence(fn (Sequence $s) => ['address_id' => $addressIds[$s->index % count($addressIds)]]))
            ->create();
    }
}
