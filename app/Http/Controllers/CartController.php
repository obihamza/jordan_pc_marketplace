<?php

namespace App\Http\Controllers;

use App\Models\Cart;

/**
 * A user has one cart (carts.user_id is unique). It is created automatically
 * the first time something is added (see CartItemController@store).
 * Admin sees all carts; every other user (buyer or seller) sees their own.
 */
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

            // Emptying your own cart (the cart row itself stays).
            $cart = Cart::where('user_id', $me->id)
                ->findOrFail($cart->id);

            $cart->items()->delete();
            $cart->update(['total' => 0]);
        }

        return redirect()->route('carts.index')
            ->with('success', 'Cart cleared successfully!');
    }
}
