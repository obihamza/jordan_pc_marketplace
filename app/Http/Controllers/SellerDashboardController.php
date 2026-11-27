<?php

namespace App\Http\Controllers;

use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Support\Carbon;

class SellerDashboardController extends Controller
{
    public function index()
    {
        $me = auth()->user();

        abort_unless($me->isSeller(), 403);

        $mine = fn () => OrderItem::where('seller_id', $me->id);
        $live = fn () => $mine()->where('item_status', '!=', 'cancelled');

        $reviews = Review::approved()->whereHas('product', fn ($q) => $q->where('seller_id', $me->id));

        $stats = [
            'revenue' => (float) $live()->sum('subtotal'),
            'items_sold' => (int) $live()->sum('quantity'),
            'orders' => $live()->distinct('order_id')->count('order_id'),
            'to_process' => $mine()->whereIn('item_status', ['pending', 'confirmed', 'processing'])->count(),
            'products' => Product::where('seller_id', $me->id)->count(),
            'available' => Product::where('seller_id', $me->id)->where('listing_status', 'available')->count(),
            'low_stock' => Product::where('seller_id', $me->id)->where('stock_quantity', '<=', 5)->count(),
            'reviews' => (clone $reviews)->count(),
            'avg_rating' => round((float) (clone $reviews)->avg('rating'), 1),
        ];

        $start = now()->startOfMonth()->subMonths(5);
        $months = collect(range(0, 5))->map(fn ($i) => $start->copy()->addMonths($i));
        $keys = $months->map(fn (Carbon $m) => $m->format('Y-m'));

        $byMonth = $live()->where('created_at', '>=', $start)
            ->get(['created_at', 'subtotal', 'quantity'])
            ->groupBy(fn ($i) => Carbon::parse($i->created_at)->format('Y-m'));

        $charts = [
            'months' => $months->map(fn (Carbon $m) => $m->format('M Y'))->all(),
            'revenue' => $keys->map(fn ($k) => round((float) $byMonth->get($k, collect())->sum('subtotal'), 2))->all(),
            'items' => $keys->map(fn ($k) => (int) $byMonth->get($k, collect())->sum('quantity'))->all(),
        ];

        $statusCounts = $mine()->selectRaw('item_status, COUNT(*) as total')->groupBy('item_status')->pluck('total', 'item_status');

        $charts['statuses'] = collect(['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'])
            ->map(fn ($s) => ['label' => ucfirst($s), 'value' => (int) ($statusCounts[$s] ?? 0)])
            ->all();

        $topProducts = Product::where('seller_id', $me->id)
            ->withSum(['orderItems as sold_count' => fn ($q) => $q->where('item_status', '!=', 'cancelled')], 'quantity')
            ->orderByDesc('sold_count')
            ->take(5)
            ->get();

        $charts['top'] = [
            'labels' => $topProducts->pluck('name')->map(fn ($n) => \Illuminate\Support\Str::limit($n, 24))->all(),
            'values' => $topProducts->map(fn ($p) => (int) $p->sold_count)->all(),
        ];

        $ratingCounts = (clone $reviews)->selectRaw('rating, COUNT(*) as total')->groupBy('rating')->pluck('total', 'rating');
        $charts['ratings'] = collect(range(1, 5))->map(fn ($r) => (int) ($ratingCounts[$r] ?? 0))->all();

        $toProcess = OrderItem::with('order')
            ->where('seller_id', $me->id)
            ->whereIn('item_status', ['pending', 'confirmed', 'processing'])
            ->latest()
            ->take(6)
            ->get();

        $lowStock = Product::where('seller_id', $me->id)
            ->where('stock_quantity', '<=', 5)
            ->orderBy('stock_quantity')
            ->take(5)
            ->get();

        $latestReviews = Review::approved()->with(['user', 'product'])
            ->whereHas('product', fn ($q) => $q->where('seller_id', $me->id))
            ->latest()
            ->take(4)
            ->get();

        return view('seller.dashboard', compact('stats', 'charts', 'toProcess', 'lowStock', 'latestReviews'));
    }
}
