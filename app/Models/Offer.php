<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Offer extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_id',
        'promotion_type_id',
        'offer_type',
        'code',
        'destination_url',
        'title',
        'description',
        'terms',
        'discount_type',
        'discount_value',
        'badge_label',
        'image_path',
        'is_active',
        'start_date',
        'expiry_date',
        'sort_order',
        'clicks',
        'last_used_at',
    ];

    protected function casts(): array
    {
        return [
            'discount_value' => 'decimal:2',
            'is_active' => 'boolean',
            'start_date' => 'date',
            'expiry_date' => 'datetime',
            'last_used_at' => 'datetime',
        ];
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    /**
     * A promotion's category is inherited from its store (SRS §9: "Promotions
     * inherit their Category classification directly from the assigned
     * Store/Brand entity") — filter/search by `$offer->store->category`,
     * there is no independent category on the offer itself.
     */
    public function promotionType(): BelongsTo
    {
        return $this->belongsTo(PromotionType::class);
    }

    /**
     * Max 2 enforced at data-entry time (admin form), not by a DB
     * constraint — see BadgeController/OfferController validation.
     */
    public function badges(): BelongsToMany
    {
        return $this->belongsToMany(Badge::class, 'offer_badge');
    }

    public function isCoupon(): bool
    {
        return $this->offer_type === 'coupon';
    }

    /**
     * Where a click on this offer's CTA should ultimately land: the
     * admin-configured destination URL if set, else the store's
     * affiliate URL as a sane fallback.
     */
    public function redirectUrl(): string
    {
        return $this->destination_url ?: $this->store->affiliate_url;
    }

    public function isExpired(): bool
    {
        return $this->expiry_date !== null && $this->expiry_date->isPast();
    }

    /**
     * Record a CTA click: bumps the usage counter and stamps last_used_at,
     * so both are current the instant a shopper reveals/uses this offer.
     */
    public function recordClick(): void
    {
        $this->increment('clicks');
        $this->update(['last_used_at' => now()]);
    }

    /**
     * "{N} uses · Last used {X ago}" — shown on every offer card everywhere.
     * The "Last used" clause is dropped once it's more than a day stale, so
     * cards don't advertise obviously-cold codes as recently used.
     */
    public function usageLabel(): string
    {
        $label = number_format($this->clicks).' '.($this->clicks === 1 ? 'use' : 'uses');

        if ($this->last_used_at && $this->last_used_at->isAfter(now()->subDay())) {
            $label .= ' · Last used '.$this->last_used_at->diffForHumans();
        }

        return $label;
    }

    /**
     * Region-aware display label: flat USD values are converted to the
     * region's currency, percentage/other discounts render unchanged.
     */
    public function displayLabelFor(Region $region): string
    {
        if ($this->badge_label) {
            return $this->badge_label;
        }

        return match ($this->discount_type) {
            'flat' => $region->currency->symbol.number_format($region->convertFromUsd((float) $this->discount_value), 0).' Off',
            'percentage' => rtrim(rtrim(number_format((float) $this->discount_value, 2), '0'), '.').'% Off',
            default => $this->title,
        };
    }

    /**
     * Whether this offer carries the "Verified" badge — replaces the old
     * is_verified boolean; checked by name since badges are now a plain
     * admin-managed list rather than fixed flags.
     */
    public function isVerified(): bool
    {
        return $this->relationLoaded('badges')
            ? $this->badges->contains('name', 'Verified')
            : $this->badges()->where('name', 'Verified')->exists();
    }
}
