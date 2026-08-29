<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Store extends Model
{
    use HasFactory;

    protected $fillable = [
        'region_id',
        'category_id',
        'name',
        'title_prefix',
        'title_suffix',
        'slug',
        'logo_path',
        'about',
        'faqs',
        'banner_heading',
        'banner_text',
        'banner_image',
        'banner_button_text',
        'banner_button_url',
        'curate_right_content',
        'custom_sections',
        'website_url',
        'affiliate_url',
        'expiry_date',
        'star_rating',
        'reviews_count',
        'is_featured',
        'featured_order',
        'is_popular',
        'popular_order',
        'is_active',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'og_image',
        'og_title',
        'og_description',
        'canonical_url',
        'head_start_script',
        'head_end_script',
        'body_start_script',
        'body_end_script',
        'robots_index',
        'robots_follow',
    ];

    protected function casts(): array
    {
        return [
            'faqs' => 'array',
            'custom_sections' => 'array',
            'expiry_date' => 'date',
            'star_rating' => 'decimal:1',
            'is_featured' => 'boolean',
            'is_popular' => 'boolean',
            'is_active' => 'boolean',
            'robots_index' => 'boolean',
            'robots_follow' => 'boolean',
        ];
    }

    /**
     * The full display title, e.g. "20% Off {Prefix }{Name}{ Suffix} Promo Codes".
     */
    public function fullTitle(): string
    {
        return trim(collect([$this->title_prefix, $this->name, $this->title_suffix])->filter()->implode(' '));
    }

    /**
     * Row 6's auto-calculated statistics box — Verified Discount Codes,
     * Total Coupons, Best Discount Today, Average Shopper Savings, Last
     * Coupon Added — all derived from this store's own active offers
     * rather than stored, so they never go stale.
     */
    public function savingsStats(): array
    {
        $activeOffers = $this->offers()->where('is_active', true)->get();
        $percentageOffers = $activeOffers->where('discount_type', 'percentage');

        return [
            'verified_codes' => $activeOffers->filter(fn ($o) => $o->isVerified())->count(),
            'total_coupons' => $activeOffers->where('offer_type', 'coupon')->count(),
            'best_discount_today' => $percentageOffers->isNotEmpty()
                ? rtrim(rtrim(number_format((float) $percentageOffers->max('discount_value'), 2), '0'), '.').'%'
                : '—',
            'average_savings' => $percentageOffers->isNotEmpty()
                ? rtrim(rtrim(number_format((float) $percentageOffers->avg('discount_value'), 2), '0'), '.').'%'
                : '—',
            'last_coupon_added' => optional($this->offers()->latest('created_at')->first())->created_at?->diffForHumans() ?? '—',
        ];
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function offers(): HasMany
    {
        return $this->hasMany(Offer::class);
    }

    public function coupons(): HasMany
    {
        return $this->offers()->where('offer_type', 'coupon');
    }

    public function deals(): HasMany
    {
        return $this->offers()->where('offer_type', 'deal');
    }

    public function relatedStores(): BelongsToMany
    {
        return $this->belongsToMany(Store::class, 'store_related', 'store_id', 'related_store_id')
            ->withPivot('sort_order')
            ->orderBy('store_related.sort_order');
    }

    /**
     * Admin-curated selection shown in the "More Verified {Store} Discount
     * Codes" section — separate from the main paginated offers() list.
     */
    public function featuredOffers(): BelongsToMany
    {
        return $this->belongsToMany(Offer::class, 'store_featured_offer')
            ->withPivot('sort_order')
            ->orderBy('store_featured_offer.sort_order');
    }

    public function blogs(): BelongsToMany
    {
        return $this->belongsToMany(Blog::class, 'blog_store')
            ->withPivot('sort_order');
    }

    public function scriptInjections(): BelongsToMany
    {
        return $this->belongsToMany(ScriptInjection::class, 'script_injection_store');
    }
}
