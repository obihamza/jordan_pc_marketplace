<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'status',
        'total',
    ];

    protected function casts(): array
    {
        return [
            'total' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    /** Re-sum the cart from its items (quantity x current product price). */
    public function recalculateTotal(): void
    {
        $this->load('items.product');

        $this->update([
            'total' => $this->items->sum(fn ($item) => $item->quantity * ($item->product?->price ?? 0)),
        ]);
    }

    public function products(): HasManyThrough
    {
        return $this->hasManyThrough(Product::class, CartItem::class, 'cart_id', 'id', 'id', 'product_id');
    }
}
