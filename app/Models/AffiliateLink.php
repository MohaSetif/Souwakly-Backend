<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class AffiliateLink extends Model
{
    use HasFactory;

    protected $fillable = [
        'affiliate_id',
        'product_id',
        'code',
        'clicks',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($affiliateLink) {
            if (empty($affiliateLink->code)) {
                $affiliateLink->code = static::generateUniqueCode();
            }
        });
    }

    /**
     * Generate a unique referral code.
     */
    public static function generateUniqueCode(): string
    {
        do {
            $code = Str::random(8);
        } while (static::where('code', $code)->exists());

        return $code;
    }

    /**
     * Get the affiliate who owns this link.
     */
    public function affiliate(): BelongsTo
    {
        return $this->belongsTo(User::class, 'affiliate_id');
    }

    /**
     * Get the product for this link.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Increment click count.
     */
    public function incrementClicks(): void
    {
        $this->increment('clicks');
    }

    /**
     * Generate the full referral URL.
     */
    public function getReferralUrl(): string
    {
        return url("/ref/{$this->code}");
    }

    /**
     * Generate WhatsApp deep link with product info.
     */
    public function getWhatsAppLink(?string $phoneNumber = null): string
    {
        $product = $this->product;
        $message = urlencode(
            "Hi! I'd like to inquire about: {$product->name}\n" .
            "Referral Link: {$this->getReferralUrl()}"
        );

        if ($phoneNumber) {
            return "https://wa.me/{$phoneNumber}?text={$message}";
        }

        return "https://wa.me/?text={$message}";
    }
}
