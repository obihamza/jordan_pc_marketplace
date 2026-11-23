<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * Buyers (and sellers acting as buyers) manage the items in their own cart.
 * Admins don't shop, so they get 403.
 */
class CartItemController extends Controller
{
    public function store(Request $request)
    {
        $me = auth()->user();

        if ($me->isAdmin()) {
            abort(403);
        }

        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $product = Product::available()->findOrFail($validated['product_id']);

        // A seller can't buy their own product.
        if ($product->seller_id === $me->id) {
            throw ValidationException::withMessages(['product_id' => 'You cannot buy your own product.']);
        }

        $cart = Cart::firstOrCreate(['user_id' => $me->id]);

        $item = CartItem::where('cart_id', $cart->id)
            ->where('product_id', $product->id)
            ->first();

        $quantity = ($item->quantity ?? 0) + $validated['quantity'];

        if ($quantity > $product->stock_quantity) {
            throw ValidationException::withMessages(['quantity' => 'Not enough stock available.']);
        }

        if ($item) {
            $item->update(['quantity' => $quantity]);
        } else {
            CartItem::create([
                'cart_id' => $cart->id,
                'product_id' => $product->id,
                'quantity' => $quantity,
            ]);
        }

        $cart->recalculateTotal();

        return redirect()->route('carts.index')
            ->with('success', 'Product added to cart!');
    }

    public function update(Request $request, CartItem $cartItem)
    {
        $me = auth()->user();

        if ($me->isAdmin()) {
            abort(403);
        }

        // Only items in my own cart.
        $cartItem = CartItem::whereHas('cart', function ($query) use ($me) {
            $query->where('user_id', $me->id);
        })->findOrFail($cartItem->id);

        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        if ($validated['quantity'] > $cartItem->product->stock_quantity) {
            throw ValidationException::withMessages(['quantity' => 'Not enough stock available.']);
        }

        $cartItem->update($validated);
        $cartItem->cart->recalculateTotal();

        return redirect()->route('carts.index')
            ->with('success', 'Cart updated successfully!');
    }

    public function destroy(CartItem $cartItem)
    {
        $me = auth()->user();

        if ($me->isAdmin()) {
            abort(403);
        }

        $cartItem = CartItem::whereHas('cart', function ($query) use ($me) {
            $query->where('user_id', $me->id);
        })->findOrFail($cartItem->id);

        $cart = $cartItem->cart;

        $cartItem->delete();
        $cart->recalculateTotal();

        return redirect()->route('carts.index')
            ->with('success', 'Item removed from cart!');
    }
}
