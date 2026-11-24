<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    // The schema has created_at / updated_at only (no remember_token).
    protected $fillable = [
        'name',
        'email',
        'password_hash',
        'phone',
        'user_img',
        'is_admin',
    ];

    protected $hidden = [
        'password_hash',
    ];

    protected function casts(): array
    {
        return [
            'password_hash' => 'hashed',
            'is_admin' => 'boolean',
        ];
    }

    /**
     * The password column is `password_hash`, not `password`.
     */
    public function getAuthPasswordName(): string
    {
        return 'password_hash';
    }

    /**
     * The users table has no remember_token column. An empty name makes Laravel
     * skip "remember me" instead of failing with an SQL error.
     */
    public function getRememberTokenName(): string
    {
        return '';
    }

    public function getAuthPassword(): string
    {
        return $this->password_hash;
    }

    /* ----------------------------- Relations ----------------------------- */

    /** Seller profile (only for users who sell). */
    public function seller(): HasOne
    {
        return $this->hasOne(Seller::class, 'user_id');
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class);
    }

    /** A user has a single cart (carts.user_id is unique). */
    public function cart(): HasOne
    {
        return $this->hasOne(Cart::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /* ------------------------------- Roles -------------------------------- */
    /*
     * There is no `role` column. Roles are derived:
     *   admin  -> users.is_admin = 1
     *   seller -> the user has a row in `sellers`
     *   buyer  -> every non-admin user (so EVERY SELLER IS ALSO A BUYER,
     *             but not every buyer is a seller)
     * In controllers: isAdmin() / isSeller(); anything else is a plain buyer,
     * and "can act as a buyer" simply means !isAdmin().
     */

    public function isAdmin(): bool
    {
        return (bool) $this->is_admin;
    }

    /** Uses the loaded relation, so repeated calls in one request cost one query. */
    public function isSeller(): bool
    {
        return $this->seller !== null;
    }

    /** Users that are only buyers (not admins, not sellers). Handy for seeding. */
    public function scopeBuyersOnly(Builder $query): Builder
    {
        return $query->where('is_admin', false)->doesntHave('seller');
    }
}
