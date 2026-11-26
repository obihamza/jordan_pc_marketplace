<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'seller_id',
        'category_id',
        'name',
        'brand',
        'description',
        'price',
        'stock_quantity',
        'condition_type',
        'listing_status',
        'product_variant',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'stock_quantity' => 'integer',
        ];
    }

    /** seller_id points at sellers.user_id */
    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class, 'seller_id', 'user_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }

    public function primaryImage(): HasOne
    {
        // The primary picture; when none is marked primary, the oldest picture is used.
        return $this->hasOne(ProductImage::class)->ofMany(['is_primary' => 'max', 'id' => 'min']);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Products this user may review: bought from someone else, the line is delivered,
     * the order is paid, and they haven't reviewed it yet.
     */
    public function scopeReviewableBy(Builder $query, User $user): Builder
    {
        return $query->where('seller_id', '!=', $user->id)
            ->whereHas('orderItems', function ($items) use ($user) {
                $items->where('item_status', 'delivered')
                    ->whereHas('order', function ($order) use ($user) {
                        $order->where('user_id', $user->id)
                            ->whereHas('payment', fn ($payment) => $payment->where('payment_status', 'paid'));
                    });
            })
            ->whereDoesntHave('reviews', fn ($reviews) => $reviews->where('user_id', $user->id));
    }

    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('listing_status', 'available');
    }
}
