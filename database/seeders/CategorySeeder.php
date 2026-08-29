<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Region;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Top-level categories, each with an optional list of subcategories.
     * Mirrors the real-world taxonomy shape (parent category -> children)
     * used by reference coupon sites, trimmed to a representative set.
     */
    public static array $tree = [
        'Electronics' => ['Computers & Laptops', 'Phones & Accessories', 'Smart Home', 'Audio & Headphones'],
        'Fashion & Apparel' => ['Men\'s Clothing', 'Women\'s Clothing', 'Shoes', 'Accessories'],
        'Beauty & Personal Care' => ['Skin Care', 'Makeup', 'Hair Care', 'Fragrances', 'Nail Care'],
        'Health & Wellness' => ['Vitamins & Supplements', 'Fitness Equipment', 'Personal Care'],
        'Travel' => ['Flights', 'Hotels', 'Car Rentals', 'Vacation Packages'],
        'Home & Garden' => ['Furniture', 'Kitchen & Dining', 'Outdoor & Garden'],
        'Food & Restaurants' => ['Meal Kits', 'Grocery Delivery', 'Restaurant Deals'],
        'Sports & Outdoors' => ['Camping & Hiking', 'Team Sports', 'Fitness Apparel'],
    ];

    public function run(): void
    {
        Region::all()->each(function (Region $region) {
            $order = 0;

            foreach (self::$tree as $name => $children) {
                $order++;

                $parent = Category::updateOrCreate(
                    ['region_id' => $region->id, 'type' => 'store', 'slug' => Str::slug($name)],
                    [
                        'parent_id' => null,
                        'name' => $name,
                        'sort_order' => $order,
                        'is_active' => true,
                    ]
                );

                foreach ($children as $childIndex => $childName) {
                    Category::updateOrCreate(
                        ['region_id' => $region->id, 'type' => 'store', 'slug' => Str::slug($childName)],
                        [
                            'parent_id' => $parent->id,
                            'name' => $childName,
                            'sort_order' => $childIndex + 1,
                            'is_active' => true,
                        ]
                    );
                }
            }
        });
    }
}
