<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    /**
     * 10 orders, one per address (the order's user and shipping_address are
     * derived from the address by OrderFactory). Totals are filled in by
     * OrderItemSeeder once the items exist.
     */
    public function run(): void
    {
        $addressIds = Address::orderBy('id')->pluck('id')->all();

        Order::factory()
            ->count(10)
            ->state(new Sequence(fn (Sequence $s) => ['address_id' => $addressIds[$s->index % count($addressIds)]]))
            ->create();
    }
}
