<?php

namespace App\Http\Controllers;

use App\Models\OrderItem;
use Illuminate\Http\Request;

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
            $items = OrderItem::with(['order', 'product'])
                ->where('seller_id', $me->id)
                ->latest()
                ->paginate(15, ['*'], 'sold_page');

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
            $orderItem = OrderItem::where('seller_id', $me->id)
                ->findOrFail($orderItem->id);
        } else {
            abort(403);
        }

        $validated = $request->validate([
            'item_status' => 'required|in:pending,confirmed,processing,shipped,delivered,cancelled',
        ]);

        $orderItem->update($validated);

        return redirect()->route('order-items.index')
            ->with('success', 'Item status updated successfully!');
    }
}
