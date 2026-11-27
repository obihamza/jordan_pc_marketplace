<?php

namespace Database\Seeders;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Database\Seeder;

class CartItemSeeder extends Seeder
{
    public function run(): void
    {
        $cartIds = Cart::orderBy('id')->pluck('id')->all();
        $productIds = Product::orderBy('id')->pluck('id')->all();

        CartItem::factory()
            ->count(10)
            ->state(new Sequence(fn (Sequence $s) => [
                'cart_id' => $cartIds[$s->index % count($cartIds)],

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
