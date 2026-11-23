<?php

namespace Database\Seeders;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Database\Seeder;

class CartItemSeeder extends Seeder
{
    /** 10 cart items: one per cart, then each cart's total is recalculated. */
    public function run(): void
    {
        $cartIds = Cart::orderBy('id')->pluck('id')->all();
        $productIds = Product::orderBy('id')->pluck('id')->all();

        CartItem::factory()
            ->count(10)
            ->state(new Sequence(fn (Sequence $s) => [
                'cart_id' => $cartIds[$s->index % count($cartIds)],
                // Offset so carts don't always hold the "same index" product.
                'product_id' => $productIds[($s->index + 3) % count($productIds)],
            ]))
            ->create();

        Cart::with('items.product')->get()->each(function (Cart $cart) {
            $cart->update([
                'total' => $cart->items->sum(fn ($i) => $i->quantity * $i->product->price),
            ]);
        });
    }
}
