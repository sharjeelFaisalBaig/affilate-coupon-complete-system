<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Badge;
use App\Models\PromotionType;
use App\Models\Region;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StoreController extends Controller
{
    public function show(Request $request, Region $region, string $storeSlug): View
    {
        $store = Store::where('region_id', $region->id)->where('slug', $storeSlug)->where('is_active', true)
            ->with(['category', 'relatedStores', 'featuredOffers.store'])
            ->firstOrFail();

        $query = $store->offers()->with(['store', 'badges', 'promotionType'])->where('is_active', true);

        match ($request->string('filter')->value()) {
            'coupon' => $query->where('offer_type', 'coupon'),
            'deal' => $query->where('offer_type', 'deal'),
            default => null,
        };

        if ($request->filled('promotion_type')) {
            $query->where('promotion_type_id', $request->integer('promotion_type'));
        }

        if ($request->filled('badge')) {
            $query->whereHas('badges', fn ($q) => $q->where('badges.id', $request->integer('badge')));
        }

        if ($request->filled('q')) {
            $search = $request->string('q')->value();
            $query->where(fn ($q) => $q->where('title', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%"));
        }

        match ($request->string('sort')->value()) {
            'discount_high' => $query->orderByDesc('discount_value'),
            'discount_low' => $query->orderBy('discount_value'),
            'most_used' => $query->orderByDesc('clicks'),
            'most_unused' => $query->orderBy('clicks'),
            'oldest' => $query->orderBy('created_at'),
            'expiry' => $query->orderByRaw('expiry_date IS NULL')->orderByDesc('expiry_date'),
            default => $query->orderBy('sort_order'),
        };

        $offers = $query->paginate(24)->withQueryString();

        // Competitor cards show a live active-code count, same as the store
        // directory listing — computed here rather than a subquery so the
        // relation stays a plain BelongsToMany.
        $store->relatedStores->each(function ($related) {
            $related->activeOfferCount = $related->offers()->where('is_active', true)->count();
        });

        $couponCount = $store->offers()->where('is_active', true)->where('offer_type', 'coupon')->count();
        $dealCount = $store->offers()->where('is_active', true)->where('offer_type', 'deal')->count();

        $promotionTypes = PromotionType::where('region_id', $region->id)->orderBy('title')->get();
        $badges = Badge::where('region_id', $region->id)->where('is_active', true)->orderBy('name')->get();

        $topOffer = $store->offers()->where('is_active', true)->where('discount_type', 'percentage')
            ->orderByDesc('discount_value')->first()
            ?? $store->offers()->where('is_active', true)->where('discount_type', 'flat')
                ->orderByDesc('discount_value')->first();

        // The H1 always reflects the current top offer, regardless of any
        // admin-set meta_title override (which only affects the <title> tag).
        $storeTitle = $store->fullTitle() ?: $store->name;
        $h1 = $topOffer
            ? "{$storeTitle} Promo Codes - {$topOffer->displayLabelFor($region)} Discount Code ".now()->format('F Y')
            : "{$storeTitle} Promo Codes, Coupons & Deals ".now()->format('F Y');

        $viewData = [
            'region' => $region,
            'pageType' => 'store_detail',
            'storeId' => $store->id,
            'store' => $store,
            'offers' => $offers,
            'featuredOffers' => $store->featuredOffers,
            'couponCount' => $couponCount,
            'dealCount' => $dealCount,
            'promotionTypes' => $promotionTypes,
            'badges' => $badges,
            'savingsStats' => $store->savingsStats(),
            'h1' => $h1,
            'seoTitle' => $store->meta_title ?: $h1,
            'seoDescription' => $store->meta_description ?: "Save with the latest verified {$store->name} coupon codes and deals in {$region->name}.",
            'canonicalUrl' => $store->canonical_url,
            'robotsIndex' => $store->robots_index,
            'robotsFollow' => $store->robots_follow,
            'ogTitle' => $store->og_title,
            'ogImage' => $store->og_image,
        ];

        if ($request->header('X-Ajax-Filter')) {
            return view('public.partials.store-offers-results', $viewData);
        }

        return view('public.store', $viewData);
    }
}
