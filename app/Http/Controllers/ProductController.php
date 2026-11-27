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

        $categories = Category::orderBy('name')->get();

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
            $products = $this->applyFilters(
                Product::with(['category', 'primaryImage'])->where('seller_id', $me->id),
                $request
            )->paginate(15)->withQueryString();

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

    private function applyFilters($query, Request $request)
    {
        if ($request->filled('q')) {
            $query->where('name', 'like', '%'.trim($request->input('q')).'%');
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

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

        $direction = $request->input('sort') === 'oldest' ? 'asc' : 'desc';

        return $query->orderBy('created_at', $direction)->orderBy('id', $direction);
    }

    public function create()
    {
        $me = auth()->user();

        if ($me->isAdmin()) {
            return redirect()->route('products.index')->with('open_product_modal', true);
        }

        if ($me->isSeller()) {
            return redirect()->route('seller.dashboard')->with('open_product_modal', true);
        }

        abort(403);
    }

    public function store(Request $request)
    {
        $me = auth()->user();

        if (! $me->isAdmin() && ! $me->isSeller()) {
            abort(403);
        }

        $rules = [
            'category_id' => 'required_without:new_category|nullable|exists:categories,id',
            'new_category' => 'nullable|string|max:120',
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

        if (filled($validated['new_category'] ?? null)) {
            $name = trim($validated['new_category']);

            $category = Category::whereRaw('LOWER(name) = ?', [mb_strtolower($name)])->first()
                ?? Category::create(['name' => $name]);

            $validated['category_id'] = $category->id;
        }
        unset($validated['new_category']);

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

        if (! $me) {
            $product = Product::with(['seller', 'category', 'images', 'reviews' => fn ($q) => $q->approved()->with('user')])
                ->available()
                ->findOrFail($product->id);

            return view('guest.products.show', compact('product'));
        }

        if ($me->isAdmin()) {
            $product = Product::with(['seller', 'category', 'images', 'reviews' => fn ($q) => $q->approved()->with('user')])
                ->findOrFail($product->id);

            return view('admin.products.show', compact('product'));
        } elseif ($me->isSeller()) {
            $product = Product::with(['seller', 'category', 'images', 'reviews' => fn ($q) => $q->approved()->with('user')])
                ->where(function ($query) use ($me) {
                    $query->where('seller_id', $me->id)
                        ->orWhere('listing_status', 'available');
                })
                ->findOrFail($product->id);

            return view('seller.products.show', compact('product'));
        } else {
            $product = Product::with(['seller', 'category', 'images', 'reviews' => fn ($q) => $q->approved()->with('user')])
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
