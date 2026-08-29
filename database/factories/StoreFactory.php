<?php

namespace Database\Factories;

use App\Models\Store;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Store>
 *
 * region_id and category_id have no default here — the caller (StoreSeeder)
 * must always pass them explicitly.
 */
class StoreFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->company();

        $about = collect([
            "Overview" => fake()->paragraph(4),
            "Products & Services" => fake()->paragraph(3),
            "Competitors" => fake()->paragraph(2),
        ])->map(fn ($paragraph, $heading) => "<h3>{$name} {$heading}</h3><p>{$paragraph}</p>")->implode('');

        $faqs = collect(range(1, 3))->map(fn () => [
            'question' => fake()->sentence(6).'?',
            'answer' => fake()->paragraph(2),
        ])->all();

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'logo_path' => null,
            'about' => $about,
            'faqs' => $faqs,
            'banner_heading' => "Why search for {$name} coupons?",
            'banner_text' => '<p>'.fake()->paragraph(3).'</p>',
            'banner_image' => null,
            'banner_button_text' => 'Shop Now',
            'banner_button_url' => 'https://www.'.Str::slug($name).'.com',
            'curate_left_content' => '<h3>Our verification process</h3><p>'.fake()->paragraph(3).'</p>',
            'curate_right_content' => '<h3>How often we update</h3><p>'.fake()->paragraph(3).'</p>',
            'website_url' => 'https://www.'.Str::slug($name).'.com',
            'affiliate_url' => 'https://www.'.Str::slug($name).'.com/?ref=affiliate-demo',
            'expiry_date' => fake()->optional(0.3)->dateTimeBetween('now', '+1 year'),
            'star_rating' => fake()->randomFloat(1, 3.5, 5.0),
            'is_featured' => fake()->boolean(20),
            'featured_order' => 0,
            'is_popular' => fake()->boolean(30),
            'popular_order' => 0,
            'is_active' => true,
            'meta_title' => "{$name} Coupons, Promo Codes & Deals",
            'meta_description' => "Save with the latest verified {$name} coupon codes, promo codes and deals.",
            'meta_keywords' => null,
            'og_image' => null,
            'canonical_url' => null,
            'robots_index' => true,
            'robots_follow' => true,
        ];
    }
}
