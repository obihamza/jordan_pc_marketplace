<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\Cart;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    public function index()
    {
        $me = auth()->user();

        if ($me->isAdmin()) {

            $orders = Order::with(['user', 'payment'])
                ->latest()
                ->paginate(15);

            return view('admin.orders.index', compact('orders'));

        } elseif ($me->isSeller()) {

            // Orders that contain this seller's products (sales)...
            $sales = Order::with(['user', 'payment'])
                ->whereHas('items', function ($query) use ($me) {
                    $query->where('seller_id', $me->id);
                })
                ->latest()
                ->paginate(15, ['*'], 'sales_page');

            // ...and, because a seller is also a buyer, their own purchases.
            $purchases = Order::with('payment')
                ->where('user_id', $me->id)
                ->latest()
                ->paginate(15, ['*'], 'purchases_page');

            return view('seller.orders.index', compact('sales', 'purchases'));

        } else {

            $orders = Order::with('payment')
                ->where('user_id', $me->id)
                ->latest()
                ->paginate(15);

            return view('buyer.orders.index', compact('orders'));
        }
    }

    /** Checkout page: pick an address (cash on delivery is the only payment method). */
    public function create()
    {
        $me = auth()->user();

        if ($me->isAdmin()) {
            abort(403);
        }

        $addresses = Address::where('user_id', $me->id)->get();

        $cart = Cart::with('items.product')
            ->where('user_id', $me->id)
            ->first();

        return view('buyer.orders.create', compact('addresses', 'cart'));
    }

    /** Turns the user's cart into an order (+ order items + COD payment). */
    public function store(Request $request)
    {
        $me = auth()->user();

        if ($me->isAdmin()) {
            abort(403);
        }

        $validated = $request->validate([
            'address_id' => 'required|exists:addresses,id',
            'coupon_code' => 'nullable|string|max:50',
        ]);

        $address = Address::where('user_id', $me->id)
            ->findOrFail($validated['address_id']);

        $cart = Cart::with('items')
            ->where('user_id', $me->id)
            ->first();

        if (! $cart || $cart->items->isEmpty()) {
            throw ValidationException::withMessages(['cart' => 'Your cart is empty.']);
        }

        $order = DB::transaction(function () use ($me, $address, $cart, $validated) {

            $subtotal = 0;
            $lines = [];

            foreach ($cart->items as $item) {

                // Lock the product row so stock can't be oversold.
                $product = Product::lockForUpdate()->find($item->product_id);

                if (! $product || $product->listing_status !== 'available' || $product->stock_quantity < $item->quantity) {
                    throw ValidationException::withMessages([
                        'cart' => 'A product in your cart is no longer available in that quantity.',
                    ]);
                }

                $lineTotal = round($product->price * $item->quantity, 2);
                $subtotal += $lineTotal;
                $lines[] = [$product, $item->quantity, $lineTotal];
            }

            // Optional coupon.
            $coupon = null;
            $discount = 0;

            if (! empty($validated['coupon_code'])) {

                $coupon = Coupon::valid()
                    ->where('code', $validated['coupon_code'])
                    ->first();

                if (! $coupon || $subtotal < $coupon->min_order_amount) {
                    throw ValidationException::withMessages(['coupon_code' => 'This coupon cannot be applied to your order.']);
                }

                $discount = $coupon->discount_type === Coupon::TYPE_PERCENT
                    ? round($subtotal * $coupon->discount_value / 100, 2)
                    : min((float) $coupon->discount_value, $subtotal);

                $coupon->increment('used_count');
            }

            $shipping = 0;

            $order = Order::create([
                'user_id' => $me->id,
                'address_id' => $address->id,
                'coupon_id' => $coupon?->id,
                'order_number' => 'ORD-'.now()->format('Y').'-'.strtoupper(Str::random(8)),
                'subtotal' => $subtotal,
                'discount_amount' => $discount,
                'shipping_cost' => $shipping,
                'total_amount' => round($subtotal - $discount + $shipping, 2),
                'order_status' => 'pending',
                // Snapshot of the address at order time.
                'shipping_address' => $address->address_line,
            ]);

            foreach ($lines as [$product, $quantity, $lineTotal]) {

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'seller_id' => $product->seller_id,
                    // Snapshots, so later product edits don't change past orders.
                    'product_name' => $product->name,
                    'unit_price' => $product->price,
                    'quantity' => $quantity,
                    'subtotal' => $lineTotal,
                    'item_status' => 'pending',
                ]);

                $product->decrement('stock_quantity', $quantity);

                // decrement() already updated the in-memory stock value.
                if ($product->stock_quantity <= 0) {
                    $product->update(['listing_status' => 'sold']);
                }
            }

            Payment::create([
                'order_id' => $order->id,
                'payment_method' => 'cash_on_delivery',
                'amount_jod' => $order->total_amount,
                'payment_status' => 'pending',
            ]);

            // Empty the cart.
            $cart->items()->delete();
            $cart->update(['total' => 0]);

            return $order;
        });

        return redirect()->route('orders.show', $order)
            ->with('success', 'Order placed successfully!');
    }

    public function show(Order $order)
    {
        $me = auth()->user();

        if ($me->isAdmin()) {

            $order = Order::with(['user', 'address', 'coupon', 'items.product', 'payment'])
                ->findOrFail($order->id);

            return view('admin.orders.show', compact('order'));

        } elseif ($me->isSeller()) {

            // An order I placed, or one that contains my products.
            $order = Order::with(['user', 'address', 'coupon', 'items.product', 'payment'])
                ->where(function ($query) use ($me) {
                    $query->where('user_id', $me->id)
                        ->orWhereHas('items', function ($items) use ($me) {
                            $items->where('seller_id', $me->id);
                        });
                })
                ->findOrFail($order->id);

            return view('seller.orders.show', compact('order'));

        } else {

            $order = Order::with(['address', 'coupon', 'items.product', 'payment'])
                ->where('user_id', $me->id)
                ->findOrFail($order->id);

            return view('buyer.orders.show', compact('order'));
        }
    }

    public function update(Request $request, Order $order)
    {
        $me = auth()->user();

        if ($me->isAdmin()) {

            $order = Order::with('items')->findOrFail($order->id);

            $validated = $request->validate([
                'order_status' => 'required|in:pending,confirmed,processing,shipped,delivered,cancelled',
            ]);

            $status = $validated['order_status'];

        } else {

            // A buyer (or a seller as buyer) can only cancel their own pending order.
            $order = Order::with('items')
                ->where('user_id', $me->id)
                ->findOrFail($order->id);

            if ($order->order_status !== 'pending') {
                abort(403);
            }

            $status = 'cancelled';
        }

        DB::transaction(function () use ($order, $status) {

            // Cancelling puts the stock back.
            if ($status === 'cancelled' && $order->order_status !== 'cancelled') {
                foreach ($order->items as $item) {
                    $product = Product::withTrashed()->find($item->product_id);

                    $product?->increment('stock_quantity', $item->quantity);

                    if ($product && $product->listing_status === 'sold') {
                        $product->update(['listing_status' => 'available']);
                    }
                }
            }

            $order->update(['order_status' => $status]);
            $order->items()->update(['item_status' => $status]);
        });

        return redirect()->route('orders.show', $order)
            ->with(
                $status === 'cancelled' ? 'danger' : 'success',
                $status === 'cancelled' ? 'Order cancelled.' : 'Order updated successfully!'
            );
    }

    public function destroy(Order $order)
    {
        $me = auth()->user();

        if (! $me->isAdmin()) {
            abort(403);
        }

        $order = Order::findOrFail($order->id);

        $order->delete();

        return redirect()->route('orders.index')
            ->with('danger', 'Order deleted successfully!');
    }
}
