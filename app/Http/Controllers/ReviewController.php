<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ReviewController extends Controller
{
    public function index()
    {
        $me = auth()->user();

        if ($me->isAdmin()) {

            $reviews = Review::with(['user', 'product'])
                ->latest()
                ->paginate(15);

            return view('admin.reviews.index', compact('reviews'));

        } elseif ($me->isSeller()) {

            // Reviews on my products...
            $received = Review::with(['user', 'product'])
                ->whereHas('product', function ($query) use ($me) {
                    $query->where('seller_id', $me->id);
                })
                ->latest()
                ->paginate(15, ['*'], 'received_page');

            // ...and the reviews I wrote as a buyer.
            $written = Review::with('product')
                ->where('user_id', $me->id)
                ->latest()
                ->paginate(15, ['*'], 'written_page');

            return view('seller.reviews.index', compact('received', 'written'));

        } else {

            $reviews = Review::with('product')
                ->where('user_id', $me->id)
                ->latest()
                ->paginate(15);

            return view('buyer.reviews.index', compact('reviews'));
        }
    }

    public function create()
    {
        $me = auth()->user();

        if ($me->isAdmin()) {
            abort(403);
        }

        // Products from other sellers (you can't review your own).
        $products = Product::where('seller_id', '!=', $me->id)->get();

        return view('buyer.reviews.create', compact('products'));
    }

    public function store(Request $request)
    {
        $me = auth()->user();

        if ($me->isAdmin()) {
            abort(403);
        }

        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'rating' => 'required|integer|between:1,5',
            'comment' => 'required|string',
        ]);

        $product = Product::findOrFail($validated['product_id']);

        if ($product->seller_id === $me->id) {
            throw ValidationException::withMessages(['product_id' => 'You cannot review your own product.']);
        }

        Review::create([
            'user_id' => $me->id,
            'product_id' => $product->id,
            'rating' => $validated['rating'],
            'comment' => $validated['comment'],
        ]);

        return redirect()->route('reviews.index')
            ->with('success', 'Review added successfully!');
    }

    public function edit(Review $review)
    {
        $me = auth()->user();

        // Admins can delete reviews but not rewrite what a user said.
        if ($me->isAdmin()) {
            abort(403);
        }

        $review = Review::with('product')
            ->where('user_id', $me->id)
            ->findOrFail($review->id);

        return view('buyer.reviews.edit', compact('review'));
    }

    public function update(Request $request, Review $review)
    {
        $me = auth()->user();

        if ($me->isAdmin()) {
            abort(403);
        }

        $review = Review::where('user_id', $me->id)
            ->findOrFail($review->id);

        $validated = $request->validate([
            'rating' => 'required|integer|between:1,5',
            'comment' => 'required|string',
        ]);

        $review->update($validated);

        return redirect()->route('reviews.index')
            ->with('success', 'Review updated successfully!');
    }

    public function destroy(Review $review)
    {
        $me = auth()->user();

        if ($me->isAdmin()) {

            $review = Review::findOrFail($review->id);

        } else {

            // Everyone else can delete only reviews they wrote.
            $review = Review::where('user_id', $me->id)
                ->findOrFail($review->id);
        }

        $review->delete();

        return redirect()->route('reviews.index')
            ->with('success', 'Review deleted successfully!');
    }
}
