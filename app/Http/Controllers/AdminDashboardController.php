<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Review;
use App\Models\Seller;
use App\Models\User;
use Illuminate\Support\Carbon;

/**
 * Admin home: numbers + charts about the whole website.
 * Only admins may open it.
 */
class AdminDashboardController extends Controller
{
    public function index()
    {
        abort_unless(auth()->user()->isAdmin(), 403);

        // ---- Summary cards ----
        $revenue = (float) Order::where('order_status', '!=', 'cancelled')->sum('total_amount');

        $stats = [
            'users' => User::where('is_admin', false)->count(),
            'sellers' => Seller::count(),
            'products' => Product::count(),
            'categories' => Category::count(),
            'orders' => Order::count(),
            'pending_orders' => Order::where('order_status', 'pending')->count(),
            'revenue' => $revenue,
            'pending_payments' => Payment::where('payment_status', 'pending')->count(),
            'reviews' => Review::count(),
            'avg_rating' => round((float) Review::avg('rating'), 1),
            'low_stock' => Product::where('stock_quantity', '<=', 5)->count(),
        ];

        // ---- Last 6 months (grouped in PHP so it works on any database) ----
        $start = now()->startOfMonth()->subMonths(5);
        $months = collect(range(0, 5))->map(fn ($i) => $start->copy()->addMonths($i));
        $keys = $months->map(fn (Carbon $m) => $m->format('Y-m'));

        $orders = Order::where('created_at', '>=', $start)->get(['created_at', 'total_amount', 'order_status']);
        $newUsers = User::where('created_at', '>=', $start)->get(['created_at']);

        $ordersByMonth = $orders->groupBy(fn ($o) => $o->created_at->format('Y-m'));
        $usersByMonth = $newUsers->groupBy(fn ($u) => $u->created_at->format('Y-m'));

        $charts = [
            'months' => $months->map(fn (Carbon $m) => $m->format('M Y'))->all(),
            'orders' => $keys->map(fn ($k) => $ordersByMonth->get($k, collect())->count())->all(),
            'revenue' => $keys->map(fn ($k) => round(
                (float) $ordersByMonth->get($k, collect())->where('order_status', '!=', 'cancelled')->sum('total_amount'), 2
            ))->all(),
            'users' => $keys->map(fn ($k) => $usersByMonth->get($k, collect())->count())->all(),
        ];

        // ---- Orders by status ----
        $statusCounts = Order::selectRaw('order_status, COUNT(*) as total')
            ->groupBy('order_status')
            ->pluck('total', 'order_status');

        $charts['statuses'] = collect(['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'])
            ->map(fn ($s) => ['label' => ucfirst($s), 'value' => (int) ($statusCounts[$s] ?? 0)])
            ->all();

        // ---- Products per category ----
        $topCategories = Category::withCount('products')
            ->orderByDesc('products_count')
            ->orderBy('name')
            ->take(6)
            ->get();

        $charts['categories'] = [
            'labels' => $topCategories->pluck('name')->all(),
            'values' => $topCategories->pluck('products_count')->all(),
        ];

        // ---- Lists ----
        $topProducts = Product::withSum(['orderItems as sold_count' => function ($query) {
                $query->where('item_status', '!=', 'cancelled');
            }], 'quantity')
            ->orderByDesc('sold_count')
            ->take(5)
            ->get();

        $recentOrders = Order::with(['user', 'payment'])->latest()->take(6)->get();

        $lowStock = Product::where('stock_quantity', '<=', 5)
            ->orderBy('stock_quantity')
            ->take(5)
            ->get();

        return view('admin.dashboard', compact('stats', 'charts', 'topProducts', 'recentOrders', 'lowStock'));
    }
}
