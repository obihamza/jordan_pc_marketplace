<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Seller;

/**
 * The public landing page (works for guests and logged-in users).
 */
class HomeController extends Controller
{
    public function index()
    {
        // Best sellers: most units sold (cancelled lines don't count).
        // Products that never sold sort last, then newest first.
        $bestSellers = Product::with(['category', 'primaryImage'])
            ->available()
            ->withSum(['orderItems as sold_count' => function ($query) {
                $query->where('item_status', '!=', 'cancelled');
            }], 'quantity')
            ->orderByDesc('sold_count')
            ->latest()
            ->take(4)
            ->get();

        // Shop by category: busiest categories first (only products that can be bought).
        $categories = Category::withCount(['products' => fn ($query) => $query->available()])
            ->orderByDesc('products_count')
            ->orderBy('name')
            ->take(8)
            ->get();

        // Selected items: best rated first, then newest; skips the best sellers shown above.
        $selected = Product::with(['category', 'primaryImage'])
            ->available()
            ->whereNotIn('id', $bestSellers->pluck('id'))
            ->withAvg(['reviews' => fn ($query) => $query->approved()], 'rating')
            ->orderByDesc('reviews_avg_rating')
            ->latest()
            ->take(10)
            ->get();

        // "Grab the best deal on ..." rows: the two busiest categories, 5 newest products each.
        $categoryRows = $categories->take(2)->map(function ($category) {
            $category->setRelation('latestProducts', Product::with(['category', 'primaryImage'])
                ->available()
                ->where('category_id', $category->id)
                ->latest()
                ->take(5)
                ->get());

            return $category;
        })->filter(fn ($category) => $category->latestProducts->isNotEmpty());

        $stats = [
            'products' => Product::available()->count(),
            'sellers' => Seller::approved()->count(),
            'categories' => Category::count(),
        ];

        return view('home', compact('bestSellers', 'categories', 'categoryRows', 'selected', 'stats'));
    }
}
