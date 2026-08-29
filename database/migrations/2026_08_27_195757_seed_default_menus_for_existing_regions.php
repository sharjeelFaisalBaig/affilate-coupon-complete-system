<?php

use App\Models\Menu;
use App\Models\Region;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Seeds the 4 fixed menus + their initial items (mirroring today's
     * hardcoded header.blade.php / footer.blade.php links) for every region
     * that existed before the Menu Manager module did. Region-relative
     * paths are stored without the {region} prefix — the renderer adds it.
     */
    public function up(): void
    {
        Region::all()->each(function (Region $region) {
            Menu::ensureFixedMenusExist($region);

            $header = Menu::where('region_id', $region->id)->where('slot', Menu::SLOT_HEADER)->first();
            if ($header && $header->items()->doesntExist()) {
                foreach ([
                    ['title' => 'Promo Codes', 'url' => '/coupons'],
                    ['title' => 'Stores', 'url' => '/stores'],
                    ['title' => 'Blog', 'url' => '/blogs'],
                ] as $i => $item) {
                    $header->items()->create($item + ['target' => 'same_tab', 'sort_order' => $i + 1]);
                }
            }

            $about = Menu::where('region_id', $region->id)->where('slot', Menu::SLOT_FOOTER_ABOUT)->first();
            if ($about && $about->items()->doesntExist()) {
                foreach ([
                    ['title' => 'Contact Us', 'url' => '/p/contact'],
                    ['title' => 'Terms of Use', 'url' => '/p/terms-of-use'],
                    ['title' => 'Privacy Policy', 'url' => '/p/privacy-policy'],
                ] as $i => $item) {
                    $about->items()->create($item + ['target' => 'same_tab', 'sort_order' => $i + 1]);
                }
            }

            $connect = Menu::where('region_id', $region->id)->where('slot', Menu::SLOT_FOOTER_CONNECT)->first();
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

            $shop = Menu::where('region_id', $region->id)->where('slot', Menu::SLOT_FOOTER_SHOP)->first();
            if ($shop && $shop->items()->doesntExist()) {
                foreach ([
                    ['title' => 'Shop Deals', 'url' => '/coupons'],
                    ['title' => 'Stores by Category', 'url' => '/stores'],
                ] as $i => $item) {
                    $shop->items()->create($item + ['target' => 'same_tab', 'sort_order' => $i + 1]);
                }
            }
        });
    }

    public function down(): void
    {
        // Data-only migration — nothing to reverse beyond dropping the tables
        // themselves, which the create_menus_table/create_menu_items_table
        // migrations already handle.
    }
};
