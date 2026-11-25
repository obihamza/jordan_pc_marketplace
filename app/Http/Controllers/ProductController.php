<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Seller;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $me = auth()->user();

        // For the category dropdown in the filter bar.
        $categories = Category::orderBy('name')->get();

        // Not logged in: the public product list.
        if (! $me) {

            $products = $this->applyFilters(
                Product::with(['category', 'primaryImage'])->available(),
                $request
            )->paginate(15)->withQueryString();

            return view('guest.products.index', compact('products', 'categories'));
        }

        if ($me->isAdmin()) {

            $products = $this->applyFilters(
                Product::with(['seller', 'category', 'primaryImage']),
                $request
            )->paginate(15)->withQueryString();

            return view('admin.products.index', compact('products', 'categories'));

        } elseif ($me->isSeller()) {

            // The seller's own listings...
            $products = $this->applyFilters(
                Product::with(['category', 'primaryImage'])->where('seller_id', $me->id),
                $request
            )->paginate(15)->withQueryString();

            // ...and, because a seller is also a buyer, the marketplace
            // (other sellers' available products). Same filters apply.
            $marketplace = $this->applyFilters(
                Product::with(['category', 'primaryImage'])
                    ->available()
                    ->where('seller_id', '!=', $me->id),
                $request
            )->paginate(15, ['*'], 'market_page')->withQueryString();

            return view('seller.products.index', compact('products', 'marketplace', 'categories'));

        } else {

            $products = $this->applyFilters(
                Product::with(['category', 'primaryImage'])->available(),
                $request
            )->paginate(15)->withQueryString();

            return view('buyer.products.index', compact('products', 'categories'));
        }
    }

    /**
     * The product filter bar (?q=, ?category_id=, ?min_price=, ?max_price=, ?sort=).
     * Used by every role's product list, so they all filter the same way.
     */
    private function applyFilters($query, Request $request)
    {
        // Search by name
        if ($request->filled('q')) {
            $query->where('name', 'like', '%'.trim($request->input('q')).'%');
        }

        // Category
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        // Price range (ignored when not a number; swapped if min > max)
        $min = is_numeric($request->input('min_price')) ? (float) $request->input('min_price') : null;
        $max = is_numeric($request->input('max_price')) ? (float) $request->input('max_price') : null;

        if ($min !== null && $max !== null && $min > $max) {
            [$min, $max] = [$max, $min];
        }
        if ($min !== null) {
            $query->where('price', '>=', $min);
        }
        if ($max !== null) {
            $query->where('price', '<=', $max);
        }

        // Newest (default) or oldest first
        $direction = $request->input('sort') === 'oldest' ? 'asc' : 'desc';

        return $query->orderBy('created_at', $direction)->orderBy('id', $direction);
    }

    public function create()
    {
        $me = auth()->user();

        if ($me->isAdmin()) {

            $categories = Category::all();
            $sellers = Seller::approved()->with('user')->get();

            return view('admin.products.create', compact('categories', 'sellers'));

        } elseif ($me->isSeller()) {

            $categories = Category::all();

            return view('seller.products.create', compact('categories'));
        }

        // A plain buyer can't list products.
        abort(403);
    }

    public function store(Request $request)
    {
        $me = auth()->user();

        if (! $me->isAdmin() && ! $me->isSeller()) {
            abort(403);
        }

        $rules = [
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:180',
            'brand' => 'nullable|string|max:100',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'condition_type' => 'required|in:new,used',
            'product_variant' => 'nullable|string|max:250',
        ];

        if ($me->isAdmin()) {
            $rules['seller_id'] = 'required|exists:sellers,user_id';
        }

        $validated = $request->validate($rules);

        Product::create(array_merge($validated, [
            'seller_id' => $me->isAdmin() ? $validated['seller_id'] : $me->id,
            'listing_status' => 'available',
        ]));

        return redirect()->route('products.index')
            ->with('success', 'Product created successfully!');
    }

    public function show(Product $product)
    {
        $me = auth()->user();

        // Not logged in: only available products are visible.
        if (! $me) {

            $product = Product::with(['seller', 'category', 'images', 'reviews.user'])
                ->available()
                ->findOrFail($product->id);

            return view('guest.products.show', compact('product'));
        }

        if ($me->isAdmin()) {

            $product = Product::with(['seller', 'category', 'images', 'reviews.user'])
                ->findOrFail($product->id);

            return view('admin.products.show', compact('product'));

        } elseif ($me->isSeller()) {

            // Own products (any status) or anyone's available product.
            $product = Product::with(['seller', 'category', 'images', 'reviews.user'])
                ->where(function ($query) use ($me) {
                    $query->where('seller_id', $me->id)
                        ->orWhere('listing_status', 'available');
                })
                ->findOrFail($product->id);

            return view('seller.products.show', compact('product'));

        } else {

            $product = Product::with(['seller', 'category', 'images', 'reviews.user'])
                ->available()
                ->findOrFail($product->id);

            return view('buyer.products.show', compact('product'));
        }
    }

    public function edit(Product $product)
    {
        $me = auth()->user();

        if ($me->isAdmin()) {

            $product = Product::findOrFail($product->id);
            $categories = Category::all();

            return view('admin.products.edit', compact('product', 'categories'));

        } elseif ($me->isSeller()) {

            $product = Product::where('seller_id', $me->id)
                ->findOrFail($product->id);
            $categories = Category::all();

            return view('seller.products.edit', compact('product', 'categories'));
        }

        abort(403);
    }

    public function update(Request $request, Product $product)
    {
        $me = auth()->user();

        if ($me->isAdmin()) {

            $product = Product::findOrFail($product->id);

        } elseif ($me->isSeller()) {

            $product = Product::where('seller_id', $me->id)
                ->findOrFail($product->id);

        } else {
            abort(403);
        }

        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:180',
            'brand' => 'nullable|string|max:100',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'condition_type' => 'required|in:new,used',
            'listing_status' => 'required|in:available,sold,hidden,reserved',
            'product_variant' => 'nullable|string|max:250',
        ]);

        $product->update($validated);

        return redirect()->route('products.index')
            ->with('success', 'Product updated successfully!');
    }

    public function destroy(Product $product)
    {
        $me = auth()->user();

        if ($me->isAdmin()) {

            $product = Product::findOrFail($product->id);

        } elseif ($me->isSeller()) {

            $product = Product::where('seller_id', $me->id)
                ->findOrFail($product->id);

        } else {
            abort(403);
        }

        $product->delete();

        return redirect()->route('products.index')
            ->with('danger', 'Product deleted successfully!');
    }
}
