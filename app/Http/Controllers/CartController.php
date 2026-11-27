<?php

namespace App\Http\Controllers;

use App\Models\Cart;

class CartController extends Controller
{
    public function index()
    {
        $me = auth()->user();

        if ($me->isAdmin()) {
            $carts = Cart::with(['user', 'items.product'])
                ->latest()
                ->paginate(15);

            return view('admin.carts.index', compact('carts'));
        } else {
            $cart = Cart::with('items.product')
                ->firstOrCreate(['user_id' => $me->id]);

            return view('buyer.carts.index', compact('cart'));
        }
    }

    public function destroy(Cart $cart)
    {
        $me = auth()->user();

        if ($me->isAdmin()) {
            $cart = Cart::findOrFail($cart->id);

            $cart->delete();
        } else {
            $cart = Cart::where('user_id', $me->id)
                ->findOrFail($cart->id);

            $cart->items()->delete();
            $cart->update(['total' => 0]);
        }

        return redirect()->route('carts.index')
            ->with('danger', 'Cart cleared successfully!');
    }
}
