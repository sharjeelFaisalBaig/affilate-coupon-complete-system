<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Region;
use App\Models\Store;
use Illuminate\Database\Seeder;

class StoreSeeder extends Seeder
{
    public function run(): void
    {
        Region::all()->each(function (Region $region) {
            $categories = Category::where('region_id', $region->id)->where('type', 'store')->get();

            $featuredCount = 0;
            $popularCount = 0;

            foreach ($categories as $category) {
                for ($i = 0; $i < 3; $i++) {
                    $store = Store::factory()->create([
                        'region_id' => $region->id,
                        'category_id' => $category->id,
                    ]);

                    if ($store->is_featured) {
                        $store->update(['featured_order' => ++$featuredCount]);
                    }

                    if ($store->is_popular) {
                        $store->update(['popular_order' => ++$popularCount]);
                    }
                }
            }
        });
    }
}
