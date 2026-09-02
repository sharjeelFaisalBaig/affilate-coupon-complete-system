<?php

namespace Database\Seeders;

use App\Models\Offer;
use App\Models\Store;
use Illuminate\Database\Seeder;

class OfferSeeder extends Seeder
{
    public function run(): void
    {
        Store::all()->each(function (Store $store) {
            $count = fake()->numberBetween(2, 4);

            for ($i = 0; $i < $count; $i++) {
                Offer::factory()->create([
                    'store_id' => $store->id,
                    'sort_order' => $i + 1,
                ]);
            }
        });
    }
}
