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
}
