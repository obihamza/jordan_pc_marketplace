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

    /** Streams the picture to anyone (guests included): product pictures are public. */
    public function file(ProductImage $productImage)
    {
        $disk = Storage::disk('public');

        abort_unless($disk->exists($productImage->image_path), 404);

        return $disk->response($productImage->image_path, null, ['Cache-Control' => 'public, max-age=86400']);
    }

    /** Picture settings (the pop-up): primary or not. */
    public function update(Request $request, ProductImage $productImage)
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

        $makePrimary = $request->boolean('is_primary');

        if ($makePrimary) {

            // Only one primary picture per product.
            $productImage->product->images()->update(['is_primary' => false]);
            $productImage->update(['is_primary' => true]);

        } elseif ($productImage->is_primary) {

            // A product always keeps a primary picture: hand the role to another one.
            $other = $productImage->product->images()->where('id', '!=', $productImage->id)->orderBy('id')->first();

            if (! $other) {
                return back()->with('danger', 'This is the only picture of the product, so it stays primary. Upload another picture first.');
            }

            $productImage->update(['is_primary' => false]);
            $other->update(['is_primary' => true]);
        }

        return back()->with('success', 'Picture settings saved.');
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
            ->with('danger', 'Image deleted successfully!');
    }
}
