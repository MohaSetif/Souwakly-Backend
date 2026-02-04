<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'brand',
        'price',
        'images',
        'merchant_id',
        'status',
    ];

    protected $casts = [
        'images' => 'array',
        'price' => 'decimal:2',
    ];

    /**
     * Get the merchant that owns the product.
     */
    public function merchant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'merchant_id');
    }

    /**
     * Get orders for this product.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Get affiliate links for this product.
     */
    public function affiliateLinks(): HasMany
    {
        return $this->hasMany(AffiliateLink::class);
    }

    /**
     * Scope to get only active products.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope to get products owned by a specific merchant.
     */
    public function scopeOwnedBy(Builder $query, int $merchantId): Builder
    {
        return $query->where('merchant_id', $merchantId);
    }

    /**
     * Check if user owns this product.
     */
    public function isOwnedBy(User $user): bool
    {
        return $this->merchant_id === $user->id;
    }
}
