<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PromotionType extends Model
{
    use HasFactory;

    /**
     * Protected per SRS §7 — these 3 system defaults can never be deleted.
     * Keyed by slug so seeding is idempotent (firstOrCreate per region).
     * "Special Types" is deliberately NOT a fixed system default: the admin
     * creates as many named custom types as needed (Shipping, BOGO, etc.),
     * each a normal deletable PromotionType row with discount_format
     * hardcoded to 'custom_text' — see PromotionTypeController.
     */
    public const SYSTEM_DEFAULTS = [
        'percentage-discount' => ['title' => 'Coupons (Percentage Discounts)', 'discount_format' => 'percentage'],
        'flat-rate-discount' => ['title' => 'Coupons (Flat Rate Discounts)', 'discount_format' => 'flat'],
        'deals' => ['title' => 'Deals', 'discount_format' => 'deal'],
    ];

    protected $fillable = [
        'region_id',
        'title',
        'slug',
        'discount_format',
        'is_system_default',
    ];

    protected function casts(): array
    {
        return [
            'is_system_default' => 'boolean',
        ];
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function offers(): HasMany
    {
        return $this->hasMany(Offer::class);
    }

    public static function seedSystemDefaultsFor(Region $region): void
    {
        foreach (self::SYSTEM_DEFAULTS as $slug => $attrs) {
            self::firstOrCreate(
                ['region_id' => $region->id, 'slug' => $slug],
                $attrs + ['is_system_default' => true]
            );
        }
    }
}
