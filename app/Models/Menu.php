<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Menu extends Model
{
    use HasFactory;

    public const SLOT_HEADER = 'header';
    public const SLOT_FOOTER_ABOUT = 'footer_about_us';
    public const SLOT_FOOTER_CONNECT = 'footer_connect';
    public const SLOT_FOOTER_SHOP = 'footer_shop_coupons';

    /**
     * Menu instances are fixed by the SRS — admins can add/remove items
     * within a menu, but never add or remove a menu itself.
     */
    public const FIXED_SLOTS = [
        self::SLOT_HEADER => 'Header Menu',
        self::SLOT_FOOTER_ABOUT => 'About Us',
        self::SLOT_FOOTER_CONNECT => 'Connect',
        self::SLOT_FOOTER_SHOP => 'Shop Coupons',
    ];

    protected $fillable = [
        'region_id',
        'slot',
        'name',
    ];

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(MenuItem::class)->orderBy('sort_order');
    }

    /**
     * Creates any of the 4 fixed menus that don't yet exist for this region
     * (used both for fresh-region initialization and to backfill regions
     * that existed before the Menu Manager module did).
     */
    public static function ensureFixedMenusExist(Region $region): void
    {
        foreach (self::FIXED_SLOTS as $slot => $name) {
            self::firstOrCreate(['region_id' => $region->id, 'slot' => $slot], ['name' => $name]);
        }
    }

    /**
     * Seeds each fixed menu's starting links (mirroring the site's original
     * hardcoded header/footer) — guarded per-menu so it only fills menus
     * that are still empty, never overwriting an admin's own edits. Without
     * this, header.blade.php/footer.blade.php render with zero links at
     * all (no hardcoded fallback exists), so this must run for every region
     * a fresh install creates, not just ones made through the admin UI.
     */
    public static function seedDefaultItemsFor(Region $region): void
    {
        self::ensureFixedMenusExist($region);

        $header = self::where('region_id', $region->id)->where('slot', self::SLOT_HEADER)->first();
        if ($header && $header->items()->doesntExist()) {
            foreach ([
                ['title' => 'Promo Codes', 'url' => '/coupons'],
                ['title' => 'Stores', 'url' => '/stores'],
                ['title' => 'Blog', 'url' => '/blogs'],
            ] as $i => $item) {
                $header->items()->create($item + ['target' => 'same_tab', 'sort_order' => $i + 1]);
            }
        }

        $about = self::where('region_id', $region->id)->where('slot', self::SLOT_FOOTER_ABOUT)->first();
        if ($about && $about->items()->doesntExist()) {
            foreach ([
                ['title' => 'Contact Us', 'url' => '/p/contact'],
                ['title' => 'Terms of Use', 'url' => '/p/terms-of-use'],
                ['title' => 'Privacy Policy', 'url' => '/p/privacy-policy'],
            ] as $i => $item) {
                $about->items()->create($item + ['target' => 'same_tab', 'sort_order' => $i + 1]);
            }
        }

        $connect = self::where('region_id', $region->id)->where('slot', self::SLOT_FOOTER_CONNECT)->first();
        if ($connect && $connect->items()->doesntExist()) {
            foreach ([
                ['title' => 'Blog', 'url' => '/blogs', 'target' => 'same_tab'],
                ['title' => 'Twitter', 'url' => '#', 'target' => 'new_tab'],
                ['title' => 'Facebook', 'url' => '#', 'target' => 'new_tab'],
                ['title' => 'Instagram', 'url' => '#', 'target' => 'new_tab'],
                ['title' => 'LinkedIn', 'url' => '#', 'target' => 'new_tab'],
            ] as $i => $item) {
                $connect->items()->create($item + ['sort_order' => $i + 1]);
            }
        }

        $shop = self::where('region_id', $region->id)->where('slot', self::SLOT_FOOTER_SHOP)->first();
        if ($shop && $shop->items()->doesntExist()) {
            foreach ([
                ['title' => 'Shop Deals', 'url' => '/coupons'],
                ['title' => 'Stores by Category', 'url' => '/stores'],
            ] as $i => $item) {
                $shop->items()->create($item + ['target' => 'same_tab', 'sort_order' => $i + 1]);
            }
        }
    }
}
