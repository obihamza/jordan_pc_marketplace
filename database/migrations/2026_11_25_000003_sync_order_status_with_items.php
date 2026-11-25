<?php

use App\Models\Order;
use Illuminate\Database\Migrations\Migration;

/** One-off fix: orders whose status was left behind when sellers updated their order lines. */
return new class extends Migration
{
    public function up(): void
    {
        Order::with('items')->chunkById(100, function ($orders) {
            foreach ($orders as $order) {
                $order->syncStatusFromItems();
            }
        });
    }

    public function down(): void
    {
        // Nothing to undo.
    }
};
