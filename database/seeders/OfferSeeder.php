<?php

namespace Database\Seeders;

use App\Models\Offer;
use App\Models\PromotionType;
use App\Models\Store;
use Illuminate\Database\Seeder;

class OfferSeeder extends Seeder
{
    public function run(): void
    {
        Store::all()->each(function (Store $store) {
            // "Special Types" isn't a fixed system type (admins create as
            // many named custom types as they like) — seed one demo custom
            // type per region so seeded data still exercises that path.
            $customType = PromotionType::firstOrCreate(
                ['region_id' => $store->region_id, 'slug' => 'free-shipping'],
                ['title' => 'Free Shipping', 'discount_format' => 'custom_text', 'is_system_default' => false]
            );

            $promotionTypesBySlug = PromotionType::where('region_id', $store->region_id)->get()->keyBy('slug');

            $count = fake()->numberBetween(2, 4);

            for ($i = 0; $i < $count; $i++) {
                $offer = Offer::factory()->create([
                    'store_id' => $store->id,
                    'destination_url' => $store->website_url ?: $store->affiliate_url,
                    'sort_order' => $i + 1,
                ]);

                $slug = match (true) {
                    $offer->discount_type === 'percentage' => 'percentage-discount',
                    $offer->discount_type === 'flat' => 'flat-rate-discount',
                    $offer->offer_type === 'deal' => 'deals',
                    default => $customType->slug,
                };
                $offer->update(['promotion_type_id' => $promotionTypesBySlug->get($slug)?->id]);
            }
        });

        // "More Verified {Store} Discount Codes" picks — needs offers to exist,
        // so this runs after the create loop above rather than in StoreSeeder.
        Store::all()->each(function (Store $store) {
            $store->featuredOffers()->sync(
                $store->offers()->inRandomOrder()->take(3)->pluck('id')
                    ->mapWithKeys(fn ($id, $i) => [$id => ['sort_order' => $i + 1]])
            );
        });
    }
}
