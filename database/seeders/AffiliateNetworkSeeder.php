<?php

namespace Database\Seeders;

use App\Models\AffiliateNetwork;
use App\Models\Region;
use Illuminate\Database\Seeder;

class AffiliateNetworkSeeder extends Seeder
{
    public function run(): void
    {
        Region::all()->each(function (Region $region) {
            foreach (['Amazon Associates', 'eBay Partner Network', 'CJ Affiliate'] as $network) {
                AffiliateNetwork::updateOrCreate(
                    ['region_id' => $region->id, 'network_name' => $network],
                    [
                        'tracking_id' => null,
                        'api_key' => null,
                        'api_secret' => null,
                        'is_active' => false,
                        'sync_status' => 'pending',
                    ]
                );
            }
        });
    }
}
