<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PageSetting extends Model
{
    protected $fillable = [
        'region_id',
        'page_key',
        'is_active',
        'heading',
        'subheading',
        'meta_title',
        'meta_description',
        'og_title',
        'og_description',
        'og_image',
        'robots_index',
        'robots_follow',
        'schema_script',
        'head_start_script',
        'head_end_script',
        'body_start_script',
        'body_end_script',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'robots_index' => 'boolean',
            'robots_follow' => 'boolean',
        ];
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public static function forPage(int $regionId, string $pageKey): ?self
    {
        return static::where('region_id', $regionId)->where('page_key', $pageKey)->first();
    }

    /**
     * Seeds real, editable copy (matching each page's hardcoded fallback
     * text) so the admin form shows actual content instead of blank
     * fields — the fallback strings in each controller remain as a
     * safety net for regions/pages created before this seed ran.
     */
    public static function seedDefaultsFor(Region $region): void
    {
        $defaults = [
            'home' => [
                'heading' => $region->name.' Coupons, Promo Codes & Deals',
                'subheading' => 'Save today with verified coupon codes, promo codes and deals for top stores in '.$region->name.'.',
                'meta_title' => $region->name.' Coupons, Promo Codes & Deals',
                'meta_description' => 'Save today with verified coupon codes, promo codes and deals for top stores in '.$region->name.'.',
                'og_title' => $region->name.' Coupons, Promo Codes & Deals',
                'og_description' => 'Save today with verified coupon codes, promo codes and deals for top stores in '.$region->name.'.',
            ],
            'coupons' => [
                'heading' => "Today's Top Promo Codes & Coupons",
                'subheading' => 'Browse all active promo codes and coupons for stores in '.$region->name.', verified regularly.',
                'meta_title' => "Today's Top Promo Codes & Coupons — ".$region->name,
                'meta_description' => 'Browse all active promo codes and coupons for stores in '.$region->name.', verified regularly.',
                'og_title' => "Today's Top Promo Codes & Coupons — ".$region->name,
                'og_description' => 'Browse all active promo codes and coupons for stores in '.$region->name.', verified regularly.',
            ],
            'stores' => [
                'heading' => 'Find Coupons by Store',
                'subheading' => 'Browse all stores with active coupon codes and deals in '.$region->name.'.',
                'meta_title' => 'Find Coupons by Store — '.$region->name,
                'meta_description' => 'Browse all stores with active coupon codes and deals in '.$region->name.'.',
                'og_title' => 'Find Coupons by Store — '.$region->name,
                'og_description' => 'Browse all stores with active coupon codes and deals in '.$region->name.'.',
            ],
            'blogs' => [
                'heading' => $region->name.' Blog',
                'subheading' => 'Shopping tips, savings guides and the latest deals news for '.$region->name.'.',
                'meta_title' => $region->name.' Blog — Shopping Tips, Deals & Savings Guides',
                'meta_description' => 'Shopping tips, savings guides and the latest deals news for '.$region->name.'.',
                'og_title' => $region->name.' Blog — Shopping Tips, Deals & Savings Guides',
                'og_description' => 'Shopping tips, savings guides and the latest deals news for '.$region->name.'.',
            ],
        ];

        foreach ($defaults as $pageKey => $data) {
            self::firstOrCreate(
                ['region_id' => $region->id, 'page_key' => $pageKey],
                $data + ['is_active' => true]
            );
        }
    }
}
