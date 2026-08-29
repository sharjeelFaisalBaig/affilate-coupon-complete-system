<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Badge;
use App\Models\Category;
use App\Models\Offer;
use App\Models\PageSetting;
use App\Models\PromotionType;
use App\Models\Region;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PromoCodeController extends Controller
{
    public function index(Request $request, Region $region): View
    {
        $settings = PageSetting::forPage($region->id, 'coupons');
        abort_if($settings && ! $settings->is_active, 404);

        $query = Offer::with(['store.category', 'badges', 'promotionType'])
            ->withCount(['badges as verified_priority' => fn ($q) => $q->where('name', 'Verified')])
            ->whereHas('store', fn ($q) => $q->where('region_id', $region->id)->where('is_active', true))
            ->where('is_active', true)
            ->where(fn ($q) => $q->whereNull('expiry_date')->orWhere('expiry_date', '>', now()));

        // Promotions inherit their category from the assigned store (SRS
        // §9) — there is no independent promo-code category taxonomy.
        // Selecting a category includes stores in that category or any of
        // its descendant subcategories (the cascade widget always submits
        // the deepest non-"All" node the shopper picked).
        if ($request->filled('store_category_id')) {
            $category = Category::where('region_id', $region->id)->where('type', 'store')
                ->find($request->integer('store_category_id'));

            if ($category) {
                $categoryIds = array_merge([$category->id], $category->descendantIds());
                $query->whereHas('store', fn ($q) => $q->whereIn('category_id', $categoryIds));
            }
        }

        if ($request->filled('promotion_type')) {
            $query->where('promotion_type_id', $request->integer('promotion_type'));
        }

        if ($request->filled('badge')) {
            $query->whereHas('badges', fn ($q) => $q->where('badges.id', $request->integer('badge')));
        }

        if ($request->filled('q')) {
            $search = $request->string('q')->value();
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('discount_value', 'like', "%{$search}%")
                    ->orWhereHas('store', fn ($sq) => $sq->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('store.category', fn ($cq) => $cq->where('name', 'like', "%{$search}%"));
            });
        }

        match ($request->string('sort')->value()) {
            'discount_high' => $query->orderByDesc('discount_value'),
            'discount_low' => $query->orderBy('discount_value'),
            'most_used' => $query->orderByDesc('clicks'),
            'most_unused' => $query->orderBy('clicks'),
            'oldest' => $query->orderBy('created_at'),
            'expiry' => $query->orderByRaw('expiry_date IS NULL')->orderByDesc('expiry_date'),
            default => $query->orderByDesc('verified_priority')->orderByDesc('created_at'),
        };

        $offers = $query->paginate(18)->withQueryString();

        $storeCategories = Category::where('region_id', $region->id)->where('type', 'store')->where('is_active', true)
            ->orderBy('sort_order')->get(['id', 'parent_id', 'name']);

        $promotionTypes = PromotionType::where('region_id', $region->id)->orderBy('title')->get();
        $badges = Badge::where('region_id', $region->id)->where('is_active', true)->orderBy('name')->get();

        $viewData = [
            'region' => $region,
            'pageType' => 'coupons',
            'offers' => $offers,
            'storeCategories' => $storeCategories,
            'selectedStoreCategoryId' => $request->integer('store_category_id') ?: null,
            'promotionTypes' => $promotionTypes,
            'badges' => $badges,
            'heading' => $settings?->heading ?: "Today's Top Promo Codes & Coupons (".now()->format('F j, Y').')',
            'subheading' => $settings?->subheading,
            'seoTitle' => $settings?->meta_title ?: "Today's Top Promo Codes & Coupons — {$region->name} (".now()->format('F j, Y').')',
            'seoDescription' => $settings?->meta_description ?: ('Browse all active promo codes and coupons for stores in '.$region->name.', verified regularly.'),
            'ogTitle' => $settings?->og_title,
            'ogImage' => $settings?->og_image,
            'robotsIndex' => $settings?->robots_index ?? true,
            'robotsFollow' => $settings?->robots_follow ?? true,
        ];

        // AJAX-driven filtering (no page reload) — the JS layer pushes the
        // filtered URL via history.pushState so it stays fully shareable;
        // this just returns the results fragment instead of the full page.
        if ($request->header('X-Ajax-Filter')) {
            return view('public.partials.coupons-results', $viewData);
        }

        return view('public.coupons', $viewData);
    }
}
