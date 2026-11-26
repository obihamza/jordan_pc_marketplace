<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Review;
use App\Models\Seller;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

/**
 * Builds a short, plain-text snapshot of the numbers the logged-in person is
 * allowed to see, so the AI can give insight about selling and analysis.
 *
 *  - admin  -> whole website
 *  - seller -> ONLY their own shop
 *  - buyer  -> ONLY their own recent orders
 *  - guest  -> nothing
 *
 * No emails, phones, addresses or ID numbers are ever included.
 * Cached for 60 seconds per user so chatting does not hammer the database.
 */
class ChatbotInsights
{
    /** Ready-made questions shown as tap-to-send chips in the chat. */
    public static function suggestions(?User $user): array
    {
        if (! $user) {
            return ['How do I place an order?', 'How can I become a seller?', 'How does payment work?', 'How do coupons work?'];
        }

        if ($user->isAdmin()) {
            return [
                'How much revenue has the website earned?',
                'Analyze revenue for the last 6 months',
                'Who are the top sellers and best products?',
                'Which orders and payments need attention?',
                'Which products are low on stock?',
                'Give me a full business summary',
            ];
        }

        if ($user->isSeller()) {
            return [
                'How much have I earned so far?',
                'Analyze my sales over the last 6 months',
                'What are my best-selling products?',
                'Which products should I restock?',
                'Which orders should I prepare now?',
                'How can I sell more?',
            ];
        }

        return ['Where are my orders?', 'How do coupons work?', 'How can I become a seller?', 'How do I cancel an order?'];
    }

    public function for(?User $user): string
    {
        if (! $user) {
            return '';
        }

        $kind = $user->isAdmin() ? 'admin' : ($user->isSeller() ? 'seller' : 'buyer');

        return Cache::remember("chatbot_insights_{$kind}_{$user->id}", 60, fn () => match ($kind) {
            'admin' => $this->admin(),
            'seller' => $this->seller($user),
            default => $this->buyer($user),
        });
    }

    private function admin(): string
    {
        $live = Order::where('order_status', '!=', 'cancelled');
        $revenue = (float) (clone $live)->sum('total_amount');
        $orders = Order::count();
        $liveOrders = (clone $live)->count();

        $lines = [
            'LIVE DATA - whole website (today is '.now()->toFormattedDateString().', amounts in JOD)',
            "Users (non-admin): ".User::where('is_admin', false)->count()
                .' | approved sellers: '.Seller::approved()->count()
                .' | pending seller applications: '.Seller::where('verification_status', 'pending')->count(),
            'Products: '.Product::count().' | categories: '.Category::count()
                .' | low stock (<=5): '.Product::where('stock_quantity', '<=', 5)->count()
                .' | out of stock: '.Product::where('stock_quantity', '<=', 0)->count(),
            "Orders: {$orders} (not cancelled: {$liveOrders}) | revenue: ".number_format($revenue, 2)
                .' | average order value: '.number_format($liveOrders ? $revenue / $liveOrders : 0, 2),
            'Orders by status: '.$this->pairs(Order::selectRaw('order_status as k, COUNT(*) as v')->groupBy('order_status')->pluck('v', 'k')),
            'Pending payments: '.Payment::where('payment_status', 'pending')->count()
                .' | reviews approved: '.Review::approved()->count()
                .' | reviews waiting for approval: '.Review::where('status', 'pending')->count()
                .' | average rating: '.round((float) Review::approved()->avg('rating'), 1),
            'Revenue by month (last 6): '.$this->monthly(Order::query(), 'total_amount', 'order_status'),
        ];

        $topSellers = OrderItem::where('item_status', '!=', 'cancelled')
            ->selectRaw('seller_id, SUM(subtotal) as revenue, SUM(quantity) as units')
            ->groupBy('seller_id')->orderByDesc('revenue')->take(5)->get();

        if ($topSellers->isNotEmpty()) {
            $names = User::whereIn('id', $topSellers->pluck('seller_id'))->pluck('name', 'id');
            $lines[] = 'Top sellers by revenue: '.$topSellers->map(fn ($s) => ($names[$s->seller_id] ?? 'Seller #'.$s->seller_id)
                .' '.number_format((float) $s->revenue, 2).' ('.(int) $s->units.' units)')->implode('; ');
        }

        $topProducts = $this->topProducts(Product::query());
        if ($topProducts) {
            $lines[] = 'Best-selling products: '.$topProducts;
        }

        $cats = Category::withCount('products')->orderByDesc('products_count')->take(6)->get();
        if ($cats->isNotEmpty()) {
            $lines[] = 'Products per category: '.$cats->map(fn ($c) => "{$c->name} {$c->products_count}")->implode(', ');
        }

        return implode("\n", $lines);
    }

    private function seller(User $me): string
    {
        $mine = fn () => OrderItem::where('seller_id', $me->id);
        $live = fn () => $mine()->where('item_status', '!=', 'cancelled');
        $reviews = Review::approved()->whereHas('product', fn ($q) => $q->where('seller_id', $me->id));

        $revenue = (float) $live()->sum('subtotal');
        $units = (int) $live()->sum('quantity');
        $orders = $live()->distinct('order_id')->count('order_id');

        $lines = [
            'LIVE DATA - this seller\'s own shop only (today is '.now()->toFormattedDateString().', amounts in JOD)',
            'Revenue: '.number_format($revenue, 2)." | units sold: {$units} | orders: {$orders}"
                .' | average per order: '.number_format($orders ? $revenue / $orders : 0, 2),
            'Order lines by status: '.$this->pairs($mine()->selectRaw('item_status as k, COUNT(*) as v')->groupBy('item_status')->pluck('v', 'k')),
            'Waiting to be prepared (pending/confirmed/processing): '.$mine()->whereIn('item_status', ['pending', 'confirmed', 'processing'])->count(),
            'Products: '.Product::where('seller_id', $me->id)->count()
                .' | available: '.Product::where('seller_id', $me->id)->where('listing_status', 'available')->count()
                .' | approved reviews: '.(clone $reviews)->count()
                .' | average rating: '.round((float) (clone $reviews)->avg('rating'), 1),
            'Revenue by month (last 6): '.$this->monthly($mine(), 'subtotal', 'item_status'),
        ];

        $top = $this->topProducts(Product::where('seller_id', $me->id));
        if ($top) {
            $lines[] = 'Best-selling products: '.$top;
        }

        $low = Product::where('seller_id', $me->id)->where('stock_quantity', '<=', 5)
            ->orderBy('stock_quantity')->take(8)->get(['name', 'stock_quantity']);
        if ($low->isNotEmpty()) {
            $lines[] = 'Low stock: '.$low->map(fn ($p) => "{$p->name} ({$p->stock_quantity} left)")->implode(', ');
        }

        $unsold = Product::where('seller_id', $me->id)->where('listing_status', 'available')
            ->whereDoesntHave('orderItems', fn ($q) => $q->where('item_status', '!=', 'cancelled'))
            ->take(8)->pluck('name');
        if ($unsold->isNotEmpty()) {
            $lines[] = 'Available products that never sold: '.$unsold->implode(', ');
        }

        return implode("\n", $lines);
    }

    private function buyer(User $me): string
    {
        $orders = Order::where('user_id', $me->id)->latest()->take(5)->get(['order_number', 'order_status', 'total_amount', 'created_at']);

        if ($orders->isEmpty()) {
            return 'LIVE DATA - this buyer has not placed any orders yet.';
        }

        return "LIVE DATA - this buyer's last orders (amounts in JOD):\n".$orders->map(fn ($o) => "{$o->order_number}: {$o->order_status}, "
            .number_format((float) $o->total_amount, 2).', placed '.\Illuminate\Support\Carbon::parse($o->created_at)->toFormattedDateString())->implode("\n");
    }

    /** "k1 3, k2 5" */
    private function pairs($collection): string
    {
        return $collection->isEmpty() ? 'none' : $collection->map(fn ($v, $k) => "{$k} {$v}")->implode(', ');
    }

    /** Best sellers by units from a Product query. */
    private function topProducts($query): string
    {
        return $query
            ->withSum(['orderItems as sold_count' => fn ($q) => $q->where('item_status', '!=', 'cancelled')], 'quantity')
            ->orderByDesc('sold_count')->take(5)->get(['id', 'name'])
            ->filter(fn ($p) => (int) $p->sold_count > 0)
            ->map(fn ($p) => "{$p->name} ({$p->sold_count} sold)")->implode(', ');
    }

    /** Sum of $column per month for the last 6 months, skipping rows whose $statusCol is cancelled. */
    private function monthly($query, string $column, string $statusCol): string
    {
        $start = now()->startOfMonth()->subMonths(5);

        $rows = $query->where('created_at', '>=', $start)->where($statusCol, '!=', 'cancelled')
            ->get(['created_at', $column])
            ->groupBy(fn ($r) => \Illuminate\Support\Carbon::parse($r->created_at)->format('Y-m'));

        return collect(range(0, 5))->map(function ($i) use ($start, $rows, $column) {
            $m = $start->copy()->addMonths($i);

            return $m->format('M Y').' '.number_format((float) $rows->get($m->format('Y-m'), collect())->sum($column), 2);
        })->implode(', ');
    }
}
