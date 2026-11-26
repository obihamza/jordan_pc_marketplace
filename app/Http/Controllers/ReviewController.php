<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Review;
use App\Notifications\AdminEvent;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Validation\ValidationException;

/**
 * Reviews: a buyer can review a product only after it was delivered and paid,
 * and the review is shown on the site once an ADMIN approves it (not the seller).
 */
class ReviewController extends Controller
{
    public function index()
    {
        $me = auth()->user();

        if ($me->isAdmin()) {

            // Pending reviews first: they are waiting for the admin.
            $reviews = Review::with(['user', 'product'])
                ->orderByRaw("status = 'pending' desc")
                ->latest()
                ->paginate(15);

            return view('admin.reviews.index', compact('reviews'));

        } elseif ($me->isSeller()) {

            // Approved reviews on my products...
            $received = Review::approved()
                ->with(['user', 'product'])
                ->whereHas('product', function ($query) use ($me) {
                    $query->where('seller_id', $me->id);
                })
                ->latest()
                ->paginate(15, ['*'], 'received_page');

            // ...and the reviews I wrote as a buyer (with their approval status).
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

        // Only products I bought, paid for and received (and haven't reviewed yet).
        $products = Product::reviewableBy($me)->orderBy('name')->get();

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

        $product = Product::reviewableBy($me)->find($validated['product_id']);

        if (! $product) {
            throw ValidationException::withMessages([
                'product_id' => 'You can only review a product you bought, paid for and received (one review per product).',
            ]);
        }

        $review = Review::create([
            'user_id' => $me->id,
            'product_id' => $product->id,
            'rating' => $validated['rating'],
            'comment' => $validated['comment'],
            'status' => 'pending',
        ]);

        $this->tellAdmins($review, $product->name, $me->name.' wrote a review of '.$product->name.' ('.$validated['rating'].'/5).');

        return redirect()->route('reviews.index')
            ->with('success', 'Thanks! Your review will appear once an admin approves it.');
    }

    public function edit(Review $review)
    {
        $me = auth()->user();

        // Admins can approve, reject or delete reviews but not rewrite what a user said.
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

        $review = Review::with('product')
            ->where('user_id', $me->id)
            ->findOrFail($review->id);

        $validated = $request->validate([
            'rating' => 'required|integer|between:1,5',
            'comment' => 'required|string',
        ]);

        // An edited review goes back to the admin.
        $review->update($validated + ['status' => 'pending', 'reviewed_at' => null]);

        $this->tellAdmins($review, $review->product?->name ?? 'a product', $me->name.' edited their review of '.($review->product?->name ?? 'a product').'.');

        return redirect()->route('reviews.index')
            ->with('success', 'Review updated. It will appear again once an admin approves it.');
    }

    /** Admin: show the review on the site. */
    public function approve(Review $review)
    {
        abort_unless(auth()->user()->isAdmin(), 403);

        $review->update(['status' => 'approved', 'reviewed_at' => now()]);
        $this->clearReviewNotices($review);

        // Now the seller can see it too.
        $review->loadMissing(['product.seller.user', 'user']);
        $sellerUser = $review->product?->seller?->user;

        if ($sellerUser) {
            AdminEvent::sendTo(
                $sellerUser,
                'review',
                'New review on '.$review->product->name,
                ($review->user?->name ?? 'A buyer').' gave '.$review->rating.'/5.',
                route('reviews.index', [], false)
            );
        }

        return back()->with('success', 'Review approved. It is now visible on the product page.');
    }

    /** Admin: keep the review off the site. */
    public function reject(Review $review)
    {
        abort_unless(auth()->user()->isAdmin(), 403);

        $review->update(['status' => 'rejected', 'reviewed_at' => now()]);
        $this->clearReviewNotices($review);

        return back()->with('danger', 'Review rejected.');
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
        $this->clearReviewNotices($review);

        return redirect()->route('reviews.index')
            ->with('danger', 'Review deleted successfully!');
    }

    private function tellAdmins(Review $review, string $productName, string $message): void
    {
        AdminEvent::send('review', 'Review waiting for approval', $message, route('reviews.index', [], false), ['review_id' => $review->id]);
    }

    /** Once decided, the bell stops showing this review as new (for every admin). */
    private function clearReviewNotices(Review $review): void
    {
        DatabaseNotification::whereNull('read_at')
            ->where('type', AdminEvent::class)
            ->get()
            ->filter(fn ($n) => ($n->data['review_id'] ?? null) == $review->id)
            ->each->markAsRead();
    }
}
