<?php

namespace App\Http\Controllers;

use App\Models\OrderItem;
use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Order items are created at checkout (OrderController@store), so here we only
 * list them and let the seller (or admin) update the status of a line.
 */
class OrderItemController extends Controller
{
    public function index()
    {
        $me = auth()->user();

        if ($me->isAdmin()) {

            $items = OrderItem::with(['order', 'product', 'seller'])
                ->latest()
                ->paginate(15);

            return view('admin.order-items.index', compact('items'));

        } elseif ($me->isSeller()) {

            // Lines I have to fulfil...
            $items = OrderItem::with(['order', 'product'])
                ->where('seller_id', $me->id)
                ->latest()
                ->paginate(15, ['*'], 'sold_page');

            // ...and lines of orders I placed as a buyer.
            $purchasedItems = OrderItem::with(['order', 'product'])
                ->whereHas('order', function ($query) use ($me) {
                    $query->where('user_id', $me->id);
                })
                ->latest()
                ->paginate(15, ['*'], 'purchased_page');

            return view('seller.order-items.index', compact('items', 'purchasedItems'));

        } else {

            $items = OrderItem::with(['order', 'product'])
                ->whereHas('order', function ($query) use ($me) {
                    $query->where('user_id', $me->id);
                })
                ->latest()
                ->paginate(15);

            return view('buyer.order-items.index', compact('items'));
        }
    }

    public function update(Request $request, OrderItem $orderItem)
    {
        $me = auth()->user();

        if ($me->isAdmin()) {

            $orderItem = OrderItem::findOrFail($orderItem->id);

        } elseif ($me->isSeller()) {

            // Only lines that belong to me as the seller.
            $orderItem = OrderItem::where('seller_id', $me->id)
                ->findOrFail($orderItem->id);

        } else {
            abort(403);
        }

        $validated = $request->validate([
            'item_status' => 'required|in:pending,confirmed,processing,shipped,delivered,cancelled',
        ]);

        $new = $validated['item_status'];

        DB::transaction(function () use ($orderItem, $new) {
            $old = $orderItem->item_status;
            $product = Product::withTrashed()->lockForUpdate()->find($orderItem->product_id);

            if ($old !== 'cancelled' && $new === 'cancelled') {

                // Cancelling a line puts its stock back.
                $product?->increment('stock_quantity', $orderItem->quantity);

                if ($product && $product->listing_status === 'sold') {
                    $product->update(['listing_status' => 'available']);
                }

            } elseif ($old === 'cancelled' && $new !== 'cancelled' && $product) {

                // Re-activating a cancelled line takes the stock again.
                if ($product->stock_quantity < $orderItem->quantity) {
                    throw ValidationException::withMessages(['item_status' => 'Not enough stock to re-activate this item.']);
                }

                $product->decrement('stock_quantity', $orderItem->quantity);

                if ($product->stock_quantity <= 0) {
                    $product->update(['listing_status' => 'sold']);
                }
            }

            $orderItem->update(['item_status' => $new]);

            // The order status follows its lines.
            $orderItem->order->syncStatusFromItems();
        });

        return redirect()->route('order-items.index')
            ->with('success', 'Item status updated successfully!');
    }
}
