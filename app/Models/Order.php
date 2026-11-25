<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_SHIPPED = 'shipped';
    public const STATUS_DELIVERED = 'delivered';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'user_id',
        'address_id',
        'coupon_id',
        'order_number',
        'subtotal',
        'discount_amount',
        'shipping_cost',
        'total_amount',
        'order_status',
        'shipping_address',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'shipping_cost' => 'decimal:2',
            'total_amount' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class)->withTrashed();
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class)->withTrashed();
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    /**
     * Keep the order status in step with its lines (each seller updates only their own lines).
     * The order is only as far along as its slowest active line; it is cancelled when every line is.
     */
    public function syncStatusFromItems(): void
    {
        $statuses = $this->items()->pluck('item_status');

        if ($statuses->isEmpty()) {
            return;
        }

        $active = $statuses->reject(fn ($status) => $status === 'cancelled');

        if ($active->isEmpty()) {
            $status = 'cancelled';
        } else {
            $rank = ['pending' => 0, 'confirmed' => 1, 'processing' => 2, 'shipped' => 3, 'delivered' => 4];
            $status = $active->sortBy(fn ($s) => $rank[$s] ?? 0)->first();
        }

        if ($this->order_status !== $status) {
            $this->update(['order_status' => $status]);
        }
    }
}
