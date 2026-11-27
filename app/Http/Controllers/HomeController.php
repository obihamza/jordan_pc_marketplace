<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Seller;

class HomeController extends Controller
{
    public function index()
    {
        $bestSellers = Product::with(['category', 'primaryImage'])
            ->available()
            ->withSum(['orderItems as sold_count' => function ($query) {
                $query->where('item_status', '!=', 'cancelled');
            }], 'quantity')
            ->orderByDesc('sold_count')
            ->latest()
            ->take(4)
            ->get();

        $categories = Category::withCount(['products' => fn ($query) => $query->available()])
            ->orderByDesc('products_count')
            ->orderBy('name')
            ->take(8)
            ->get();

        $selected = Product::with(['category', 'primaryImage'])
            ->available()
            ->whereNotIn('id', $bestSellers->pluck('id'))
            ->withAvg(['reviews' => fn ($query) => $query->approved()], 'rating')
            ->orderByDesc('reviews_avg_rating')
            ->latest()
            ->take(10)
            ->get();

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
