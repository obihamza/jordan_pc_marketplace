<?php

namespace Database\Seeders;

use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Database\Seeder;

class OrderItemSeeder extends Seeder
{
    /**
     * 10 order items (one per order), then recalculates each order's
     * subtotal / discount / total and applies a coupon where it is eligible.
     */
    public function run(): void
    {
        $orderIds = Order::orderBy('id')->pluck('id')->all();
        $productIds = Product::orderBy('id')->pluck('id')->all();

        OrderItem::factory()
            ->count(10)
            ->state(new Sequence(fn (Sequence $s) => [
                'order_id' => $orderIds[$s->index % count($orderIds)],
                'product_id' => $productIds[($s->index + 5) % count($productIds)],
            ]))
            ->create();

        $coupons = Coupon::valid()->orderBy('id')->take(3)->get();

        Order::with('items')->orderBy('id')->get()->each(function (Order $order, int $i) use ($coupons) {
            $subtotal = (float) $order->items->sum('subtotal');
            $discount = 0.0;
            $couponId = null;

            // Give the first few orders a coupon, if the order meets its minimum.
            $coupon = $coupons->get($i);
            if ($coupon && $subtotal >= (float) $coupon->min_order_amount) {
                $discount = $coupon->discount_type === Coupon::TYPE_PERCENT
                    ? round($subtotal * $coupon->discount_value / 100, 2)
                    : min((float) $coupon->discount_value, $subtotal);
                $couponId = $coupon->id;
                $coupon->increment('used_count');
            }

            $order->update([
                'coupon_id' => $couponId,
                'subtotal' => $subtotal,
                'discount_amount' => $discount,
                'total_amount' => round($subtotal - $discount + (float) $order->shipping_cost, 2),
            ]);

            // Line status follows the order's status.
            $order->items()->update(['item_status' => $order->order_status]);
        });
    }
}
