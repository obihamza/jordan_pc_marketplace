<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Database\Seeder;

class PaymentSeeder extends Seeder
{
    public function run(): void
    {
        $orders = Order::orderBy('id')->get();

        Payment::factory()
            ->count(10)
            ->state(new Sequence(function (Sequence $s) use ($orders) {
                $order = $orders[$s->index % $orders->count()];

                return [
                    'order_id' => $order->id,
                    'amount_jod' => $order->total_amount,
                    'payment_status' => match ($order->order_status) {
                        'delivered' => 'paid',
                        'cancelled' => 'failed',
                        default => 'pending',
                    },
                ];
            }))
            ->create();
    }
}
