<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductImageController extends Controller
{
    public function index()
    {
        $me = auth()->user();

        if ($me->isAdmin()) {

            $images = ProductImage::with('product')
                ->latest()
                ->paginate(15);

            return view('admin.product-images.index', compact('images'));

        } elseif ($me->isSeller()) {

            $images = ProductImage::with('product')
                ->whereHas('product', function ($query) use ($me) {
                    $query->where('seller_id', $me->id);
                })
                ->latest()
                ->paginate(15);

            return view('seller.product-images.index', compact('images'));
        }

        // Buyers see images on the product page.
        abort(403);
    }

    public function create()
    {
        $me = auth()->user();

        if ($me->isAdmin()) {

            $products = Product::all();

            return view('admin.product-images.create', compact('products'));

        } elseif ($me->isSeller()) {

            $products = Product::where('seller_id', $me->id)->get();

            return view('seller.product-images.create', compact('products'));
        }

        abort(403);
    }

    public function store(Request $request)
    {
        $me = auth()->user();

        if (! $me->isAdmin() && ! $me->isSeller()) {
            abort(403);
        }

        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'image' => 'required|image|max:2048',
            'is_primary' => 'boolean',
        ]);

        $product = Product::findOrFail($validated['product_id']);

        // A seller can only add images to their own products.
        if (! $me->isAdmin() && $product->seller_id !== $me->id) {
            abort(403);
        }

        $isPrimary = $request->boolean('is_primary');

        // Only one primary image per product.
        if ($isPrimary) {
            $product->images()->update(['is_primary' => false]);
        }

        ProductImage::create([
            'product_id' => $product->id,
            'image_path' => $request->file('image')->store('products', 'public'),
            'is_primary' => $isPrimary,
        ]);

        return redirect()->route('product-images.index')
            ->with('success', 'Image uploaded successfully!');
    }

    public function destroy(ProductImage $productImage)
    {
        $me = auth()->user();

        if ($me->isAdmin()) {

            $productImage = ProductImage::findOrFail($productImage->id);

        } elseif ($me->isSeller()) {

            $productImage = ProductImage::whereHas('product', function ($query) use ($me) {
                $query->where('seller_id', $me->id);
            })->findOrFail($productImage->id);

        } else {
            abort(403);
        }

        Storage::disk('public')->delete($productImage->image_path);

        $productImage->delete();

        return redirect()->route('product-images.index')
            ->with('success', 'Image deleted successfully!');
    }
}
