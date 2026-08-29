<?php

namespace Database\Seeders;

use App\Models\Blog;
use App\Models\BlogCategory;
use App\Models\Region;
use App\Models\Store;
use Illuminate\Database\Seeder;

class BlogSeeder extends Seeder
{
    public function run(): void
    {
        Region::all()->each(function (Region $region) {
            $blogCategories = BlogCategory::where('region_id', $region->id)->get();
            $stores = Store::where('region_id', $region->id)->inRandomOrder()->take(10)->get();

            for ($i = 0; $i < 5; $i++) {
                $blog = Blog::factory()->create([
                    'region_id' => $region->id,
                    'blog_category_id' => $blogCategories->random()->id,
                ]);

                $relatedStores = $stores->random(min(3, $stores->count()));
                foreach ($relatedStores as $index => $store) {
                    $blog->relatedStores()->attach($store->id, ['sort_order' => $index + 1]);
                }
            }
        });
    }
}
