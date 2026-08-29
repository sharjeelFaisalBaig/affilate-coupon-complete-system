<?php

namespace Database\Factories;

use App\Models\Badge;
use App\Models\Offer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Offer>
 *
 * store_id has no default here — the caller (OfferSeeder) must pass it explicitly.
 */
class OfferFactory extends Factory
{
    public function definition(): array
    {
        $offerType = fake()->randomElement(['coupon', 'coupon', 'deal']);
        $discountType = fake()->randomElement(['flat', 'percentage', 'percentage']);
        $discountValue = $discountType === 'flat'
            ? fake()->randomElement([5, 10, 15, 20, 25, 50])
            : fake()->randomElement([10, 15, 20, 25, 30, 40, 50]);

        $title = $discountType === 'flat'
            ? "\${$discountValue} Off Storewide"
            : "{$discountValue}% Off Select Items";

        return [
            'offer_type' => $offerType,
            'code' => $offerType === 'coupon' ? strtoupper(fake()->bothify('SAVE##??')) : null,
            'title' => $title,
            'description' => fake()->sentence(12),
            'terms' => fake()->optional(0.6)->paragraph(),
            'discount_type' => $discountType,
            'discount_value' => $discountValue,
            'badge_label' => null,
            'is_active' => true,
            'start_date' => now()->subDays(fake()->numberBetween(1, 60)),
            'expiry_date' => fake()->optional(0.7)->dateTimeBetween('now', '+3 months'),
            'sort_order' => 0,
            'clicks' => fake()->numberBetween(0, 5000),
        ];
    }

    public function configure(): static
    {
        // Booleans (is_verified/is_exclusive/is_top_deal/is_featured) were
        // replaced by the offer_badge pivot — assign 0-2 random badges from
        // this offer's own region, matching the old flags' rough odds.
        return $this->afterCreating(function (Offer $offer) {
            $badges = Badge::where('region_id', $offer->store->region_id)->get();
            if ($badges->isEmpty()) {
                return;
            }

            $selected = collect();
            if (fake()->boolean(80)) {
                $selected->push($badges->firstWhere('name', 'Verified'));
            }
            if ($selected->count() < 2 && fake()->boolean(20)) {
                $remaining = $badges->reject(fn ($b) => $selected->pluck('id')->contains($b->id));
                if ($remaining->isNotEmpty()) {
                    $selected->push($remaining->random());
                }
            }

            $offer->badges()->sync($selected->filter()->pluck('id'));
        });
    }
}
