<?php

namespace Database\Seeders;

use App\Models\Currency;
use App\Models\Region;
use Illuminate\Database\Seeder;

class RegionSeeder extends Seeder
{
    public function run(): void
    {
        $regions = [
            ['code' => 'us', 'name' => 'United States', 'currency_iso' => 'USD', 'conversion_rate_to_usd' => 1.0000, 'sort_order' => 1, 'is_default' => true],
            ['code' => 'uk', 'name' => 'United Kingdom', 'currency_iso' => 'GBP', 'conversion_rate_to_usd' => 0.7900, 'sort_order' => 2, 'is_default' => false],
            ['code' => 'au', 'name' => 'Australia', 'currency_iso' => 'AUD', 'conversion_rate_to_usd' => 1.5200, 'sort_order' => 3, 'is_default' => false],
        ];

        foreach ($regions as $region) {
            $currencyIso = $region['currency_iso'];
            unset($region['currency_iso']);

            Region::updateOrCreate(['code' => $region['code']], $region + [
                'currency_id' => Currency::where('iso_code', $currencyIso)->value('id'),
                'is_active' => true,
            ]);
        }
    }
}
