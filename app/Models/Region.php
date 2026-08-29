<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Region extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'favicon_path',
        'currency_id',
        'conversion_rate_to_usd',
        'head_start_script',
        'head_end_script',
        'body_start_script',
        'body_end_script',
        'is_active',
        'is_default',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'conversion_rate_to_usd' => 'decimal:4',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
        ];
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    /**
     * Route-model binding resolves public {region} route segments by `code`
     * (e.g. "us"), restricted to active regions, instead of by primary key.
     * Admin routes (Route::resource('regions', ...) and friends) also bind
     * a parameter literally named {region} — those pass a numeric ID and
     * must resolve by primary key regardless of is_active, since disabling/
     * enabling a region is exactly what those routes are for.
     */
    public function resolveRouteBinding($value, $field = null)
    {
        if (is_numeric($value)) {
            return $this->where('id', $value)->firstOrFail();
        }

        return $this->where('code', $value)->where('is_active', true)->firstOrFail();
    }

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    public function stores(): HasMany
    {
        return $this->hasMany(Store::class);
    }

    public function blogCategories(): HasMany
    {
        return $this->hasMany(BlogCategory::class);
    }

    public function blogs(): HasMany
    {
        return $this->hasMany(Blog::class);
    }

    public function staticPages(): HasMany
    {
        return $this->hasMany(StaticPage::class);
    }

    public function contactMessages(): HasMany
    {
        return $this->hasMany(ContactMessage::class);
    }

    public function affiliateNetworks(): HasMany
    {
        return $this->hasMany(AffiliateNetwork::class);
    }

    public function scriptInjections(): HasMany
    {
        return $this->hasMany(ScriptInjection::class);
    }

    public function homepageSections(): HasMany
    {
        return $this->hasMany(HomepageSection::class);
    }

    public function pageSettings(): HasMany
    {
        return $this->hasMany(PageSetting::class);
    }

    public function menus(): HasMany
    {
        return $this->hasMany(Menu::class);
    }

    public function generalSetting(): HasOne
    {
        return $this->hasOne(GeneralSetting::class);
    }

    /**
     * Convert a base-USD amount into this region's currency.
     */
    public function convertFromUsd(float $usdAmount): float
    {
        return round($usdAmount * (float) $this->conversion_rate_to_usd, 2);
    }
}
