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
    use HasFactory, Notifiable, SoftDeletes;

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

    public function avatarUrl(): ?string
    {
        if (! $this->user_img) {
            return null;
        }

        return str_starts_with($this->user_img, 'http') ? $this->user_img : route('users.avatar', $this);
    }

    public function getAuthPasswordName(): string
    {
        return 'password_hash';
    }

    public function getRememberTokenName(): string
    {
        return '';
    }

    public function getAuthPassword(): string
    {
        return $this->password_hash;
    }

    public function seller(): HasOne
    {
        return $this->hasOne(Seller::class, 'user_id');
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class);
    }

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

    public function isAdmin(): bool
    {
        return (bool) $this->is_admin;
    }

    public function isSeller(): bool
    {
        return $this->seller?->isApproved() ?? false;
    }

    public function sellerStatus(): ?string
    {
        return $this->seller?->verification_status;
    }

    public function scopeBuyersOnly(Builder $query): Builder
    {
        return $query->where('is_admin', false)->doesntHave('seller');
    }
}
