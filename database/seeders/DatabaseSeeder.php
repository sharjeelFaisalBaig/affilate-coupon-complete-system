<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            CurrencySeeder::class,
            RegionSeeder::class,
            AdminUserSeeder::class,
            CategorySeeder::class,
            StoreSeeder::class,
            OfferSeeder::class,
            BlogCategorySeeder::class,
            BlogSeeder::class,
            StaticPageSeeder::class,
            AffiliateNetworkSeeder::class,
            HomepageSectionSeeder::class,
        ]);
    }
}
