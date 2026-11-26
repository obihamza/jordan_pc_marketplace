<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductImage extends Model
{
    use HasFactory;

    // Table only has created_at.
    const UPDATED_AT = null;

    protected $fillable = [
        'product_id',
        'image_path',
        'is_primary',
    ];

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
        ];
    }

    /**
     * Public address of the picture. It goes through a route (not public/storage), so it works
     * even when `php artisan storage:link` was never run (for example on Windows/XAMPP).
     */
    public function url(): string
    {
        return route('product-images.file', $this);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
