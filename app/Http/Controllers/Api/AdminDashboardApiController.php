<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Review;
use App\Models\Seller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;

class AdminDashboardApiController extends Controller
{
    public function stats(): JsonResponse
    {
        $this->authorizeAdmin();

        return response()->json(['data' => [
            'revenue' => (float) Order::where('order_status', '!=', 'cancelled')->sum('total_amount'),
            'users' => User::where('is_admin', false)->count(),
            'sellers' => Seller::approved()->count(),
            'pending_sellers' => Seller::where('verification_status', 'pending')->count(),
            'products' => Product::count(),
            'categories' => Category::count(),
            'orders' => Order::count(),
            'pending_orders' => Order::where('order_status', 'pending')->count(),
            'pending_payments' => Payment::where('payment_status', 'pending')->count(),
            'reviews' => Review::approved()->count(),
            'pending_reviews' => Review::where('status', 'pending')->count(),
            'avg_rating' => round((float) Review::approved()->avg('rating'), 1),
            'low_stock' => Product::where('stock_quantity', '<=', 5)->count(),
        ]]);
    }

    public function charts(): JsonResponse
    {
        $this->authorizeAdmin();

        $start = now()->startOfMonth()->subMonths(5);
        $months = collect(range(0, 5))->map(fn ($i) => $start->copy()->addMonths($i));
        $keys = $months->map(fn (Carbon $m) => $m->format('Y-m'));

        $ordersByMonth = Order::where('created_at', '>=', $start)->get(['created_at', 'total_amount', 'order_status'])
            ->groupBy(fn ($o) => $o->created_at->format('Y-m'));
        $usersByMonth = User::where('created_at', '>=', $start)->get(['created_at'])
            ->groupBy(fn ($u) => $u->created_at->format('Y-m'));

        $statusCounts = Order::selectRaw('order_status, COUNT(*) as total')->groupBy('order_status')->pluck('total', 'order_status');

        $categories = Category::withCount('products')->orderByDesc('products_count')->orderBy('name')->take(6)->get();

        return response()->json(['data' => [
            'months' => $months->map(fn (Carbon $m) => $m->format('M Y'))->all(),
            'orders' => $keys->map(fn ($k) => $ordersByMonth->get($k, collect())->count())->all(),
            'revenue' => $keys->map(fn ($k) => round(
                (float) $ordersByMonth->get($k, collect())->where('order_status', '!=', 'cancelled')->sum('total_amount'), 2
            ))->all(),
            'users' => $keys->map(fn ($k) => $usersByMonth->get($k, collect())->count())->all(),
            'statuses' => collect(['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'])
                ->map(fn ($s) => ['status' => $s, 'label' => ucfirst($s), 'value' => (int) ($statusCounts[$s] ?? 0)])->all(),
            'categories' => [
                'labels' => $categories->pluck('name')->all(),
                'values' => $categories->pluck('products_count')->all(),
            ],
        ]]);
    }

    public function recentOrders(): JsonResponse
    {
        $this->authorizeAdmin();

        $orders = Order::with('user:id,name')->latest()->take(6)->get();

        return response()->json(['data' => $orders->map(fn (Order $o) => [
            'id' => $o->id,
            'order_number' => $o->order_number,
            'buyer' => $o->user?->name,
            'total' => (float) $o->total_amount,
            'status' => $o->order_status,
            'created_at' => $o->created_at?->toIso8601String(),
            'url' => route('orders.show', $o),
        ])->values()]);
    }

    public function topProducts(): JsonResponse
    {
        $this->authorizeAdmin();

        $products = Product::withSum(['orderItems as sold_count' => fn ($q) => $q->where('item_status', '!=', 'cancelled')], 'quantity')
            ->orderByDesc('sold_count')->take(5)->get();

        return response()->json(['data' => $products->map(fn (Product $p) => [
            'id' => $p->id,
            'name' => $p->name,
            'sold' => (int) $p->sold_count,
            'url' => route('products.show', $p),
        ])->values()]);
    }

    public function lowStock(): JsonResponse
    {
        $this->authorizeAdmin();

        $products = Product::where('stock_quantity', '<=', 5)->orderBy('stock_quantity')->take(5)->get();

        return response()->json(['data' => $products->map(fn (Product $p) => [
            'id' => $p->id,
            'name' => $p->name,
            'stock' => (int) $p->stock_quantity,
            'url' => route('products.show', $p),
        ])->values()]);
    }

    private function authorizeAdmin(): void
    {
        abort_unless(auth()->user()?->isAdmin(), 403, 'Admins only.');
    }
}
