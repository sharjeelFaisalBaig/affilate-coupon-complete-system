<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\GuardsRegionOwnership;
use App\Http\Controllers\Controller;
use App\Models\Badge;
use App\Models\Category;
use App\Models\Offer;
use App\Models\PromotionType;
use App\Models\Region;
use App\Models\Store;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class OfferController extends Controller
{
    use GuardsRegionOwnership;

    /**
     * Global, cross-store listing per the SRS: search across title/
     * description/code/discount value/store/category/badges, with
     * category/store/promotion-type/badges/status+expiry filters and 7
     * sort options. Store-scoped drag-reordering lives separately at
     * manageOrder() — a single flat list spanning every store can't share
     * one meaningful sort_order sequence.
     */
    public function index(Request $request): View
    {
        /** @var Region $region */
        $region = $request->attributes->get('activeRegion');

        $query = Offer::with(['store.category', 'badges', 'promotionType'])
            ->whereHas('store', fn ($q) => $q->where('region_id', $region->id));

        if ($request->filled('q')) {
            $search = $request->string('q')->value();
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('discount_value', 'like', "%{$search}%")
                    ->orWhereHas('store', fn ($sq) => $sq->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('store.category', fn ($cq) => $cq->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('badges', fn ($bq) => $bq->where('name', 'like', "%{$search}%"));
            });
        }

        // Promotions inherit their category from the assigned store (SRS
        // §9) — there is no independent promotion-level category anymore.
        // Selecting a category filters to stores in that category or any
        // of its descendant subcategories.
        if ($request->filled('store_category_id')) {
            $category = Category::find($request->integer('store_category_id'));
            if ($category) {
                $categoryIds = array_merge([$category->id], $category->descendantIds());
                $query->whereHas('store', fn ($q) => $q->whereIn('category_id', $categoryIds));
            }
        }

        if ($request->filled('store_id')) {
            $query->where('store_id', $request->integer('store_id'));
        }

        if ($request->filled('promotion_type_id')) {
            $query->where('promotion_type_id', $request->integer('promotion_type_id'));
        }

        if ($request->filled('badge_id')) {
            $query->whereHas('badges', fn ($q) => $q->where('badges.id', $request->integer('badge_id')));
        }

        match ($request->string('status')->value()) {
            'active' => $query->where('is_active', true)->where(fn ($q) => $q->whereNull('expiry_date')->orWhere('expiry_date', '>', now())),
            'expired' => $query->where('expiry_date', '<=', now()),
            'draft' => $query->where('is_active', false),
            'published' => $query->where('is_active', true),
            default => null,
        };

        match ($request->string('sort')->value()) {
            'discount_high' => $query->orderByDesc('discount_value'),
            'discount_low' => $query->orderBy('discount_value'),
            'most_used' => $query->orderByDesc('clicks'),
            'most_unused' => $query->orderBy('clicks'),
            'oldest' => $query->orderBy('created_at'),
            'expiry' => $query->orderByRaw('expiry_date IS NULL')->orderByDesc('expiry_date'),
            default => $query->orderByDesc('created_at'),
        };

        $offers = $query->paginate(20)->withQueryString();

        $stores = Store::where('region_id', $region->id)->orderBy('name')->get();
        $storeCategories = Category::where('region_id', $region->id)->where('type', 'store')->orderBy('name')->get(['id', 'parent_id', 'name']);
        $promotionTypes = PromotionType::where('region_id', $region->id)->orderBy('title')->get();
        $badges = Badge::where('region_id', $region->id)->orderBy('name')->get();

        if ($request->header('X-Ajax-Filter')) {
            return view('admin.offers._results', compact('offers'));
        }

        return view('admin.offers.index', compact('offers', 'stores', 'storeCategories', 'promotionTypes', 'badges'));
    }

    /**
     * Store-scoped drag-and-drop display-order manager — the previous
     * index() behavior, kept as its own view since a global multi-store
     * list can't share one meaningful sort_order sequence.
     */
    public function manageOrder(Request $request, Store $store): View
    {
        $this->abortUnlessOwnedByActiveRegion($request, $store->region_id);

        $offers = $store->offers()->with('badges')->orderBy('sort_order')->get();
        $badges = Badge::where('region_id', $store->region_id)->where('is_active', true)->orderBy('name')->get();

        return view('admin.offers.manage-order', compact('store', 'offers', 'badges'));
    }

    public function create(Request $request): View
    {
        /** @var Region $region */
        $region = $request->attributes->get('activeRegion');

        $stores = Store::where('region_id', $region->id)->orderBy('name')->get();
        $selectedStore = $request->filled('store_id')
            ? Store::where('region_id', $region->id)->find($request->integer('store_id'))
            : null;

        return view('admin.offers.form', [
            'offer' => new Offer(),
            'stores' => $stores,
            'selectedStore' => $selectedStore,
            'promotionTypes' => PromotionType::where('region_id', $region->id)->orderBy('title')->get(),
            'badges' => Badge::where('region_id', $region->id)->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        /** @var Region $region */
        $region = $request->attributes->get('activeRegion');

        $data = $this->validated($request, $region);
        $store = Store::where('region_id', $region->id)->findOrFail($data['store_id']);
        $badgeIds = $data['badge_ids'];
        unset($data['badge_ids']);
        $data['sort_order'] = $store->offers()->max('sort_order') + 1;

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('offers', 'public');
        }

        $offer = Offer::create($data);
        $offer->badges()->sync($badgeIds);

        return redirect()->route('admin.offers.index')->with('status', 'Offer created.');
    }

    public function edit(Request $request, Offer $offer): View
    {
        $this->guardOffer($request, $offer);

        /** @var Region $region */
        $region = $request->attributes->get('activeRegion');

        return view('admin.offers.edit', [
            'offer' => $offer,
            'stores' => Store::where('region_id', $region->id)->orderBy('name')->get(),
            'selectedStore' => $offer->store,
            'promotionTypes' => PromotionType::where('region_id', $region->id)->orderBy('title')->get(),
            'badges' => Badge::where('region_id', $region->id)->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Offer $offer): RedirectResponse
    {
        $this->guardOffer($request, $offer);

        /** @var Region $region */
        $region = $request->attributes->get('activeRegion');

        $data = $this->validated($request, $region, $offer);
        $badgeIds = $data['badge_ids'];
        unset($data['badge_ids']);

        if ($request->hasFile('image')) {
            if ($offer->image_path) {
                Storage::disk('public')->delete($offer->image_path);
            }
            $data['image_path'] = $request->file('image')->store('offers', 'public');
        }

        $offer->update($data);
        $offer->badges()->sync($badgeIds);

        return redirect()->route('admin.offers.index')->with('status', 'Offer updated.');
    }

    public function destroy(Request $request, Offer $offer): RedirectResponse
    {
        $this->guardOffer($request, $offer);

        if ($offer->image_path) {
            Storage::disk('public')->delete($offer->image_path);
        }

        $offer->delete();

        return redirect()->route('admin.offers.index')->with('status', 'Offer deleted.');
    }

    public function reorder(Request $request): Response
    {
        /** @var Region $region */
        $region = $request->attributes->get('activeRegion');

        $data = $request->validate([
            'store_id' => ['required', 'exists:stores,id'],
            'ids' => ['required', 'array'],
        ]);

        $store = Store::where('region_id', $region->id)->findOrFail($data['store_id']);

        foreach ($data['ids'] as $index => $id) {
            Offer::where('id', $id)->where('store_id', $store->id)->update(['sort_order' => $index + 1]);
        }

        return response()->noContent();
    }

    private function guardOffer(Request $request, Offer $offer): void
    {
        $this->abortUnlessOwnedByActiveRegion($request, $offer->store->region_id);
    }

    private function validated(Request $request, Region $region, ?Offer $offer = null): array
    {
        $data = $request->validate([
            'store_id' => ['required', Rule::exists('stores', 'id')->where('region_id', $region->id)],
            'offer_type' => ['required', 'in:coupon,deal'],
            'code' => ['nullable', 'required_if:offer_type,coupon', 'string', 'max:50'],
            'destination_url' => ['required', 'url', 'max:2048'],
            'promotion_type_id' => [
                'required',
                Rule::exists('promotion_types', 'id')->where('region_id', $region->id),
            ],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'terms' => ['nullable', 'string'],
            'discount_value' => ['nullable', 'numeric', 'min:0'],
            'badge_label' => ['nullable', 'string', 'max:255'],
            'image' => ['nullable', 'image', 'max:5120'],
            'clicks' => ['nullable', 'integer', 'min:0'],
            'start_date' => ['nullable', 'date'],
            'expiry_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'badge_ids' => ['nullable', 'array', 'max:2'],
            'badge_ids.*' => [Rule::exists('badges', 'id')->where('region_id', $region->id)],
        ]);

        // The Promotion Type's discount_format is the single source of
        // truth for the dynamic Discount Rate/Value field (SRS §9) —
        // discount_type is derived here rather than trusting the client's
        // hidden field, and drives whether discount_value or badge_label
        // (the "Special Types/Deals: Text input") is required.
        $discountFormat = PromotionType::find($data['promotion_type_id'])->discount_format;
        $data['discount_type'] = match ($discountFormat) {
            'percentage' => 'percentage',
            'flat' => 'flat',
            default => 'other',
        };

        if (in_array($discountFormat, ['deal', 'custom_text'])) {
            if (blank($request->input('badge_label'))) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'badge_label' => 'A badge label is required for text-only promotion types (Deals / Special Coupons).',
                ]);
            }
            $data['discount_value'] = null;
        } elseif (blank($request->input('discount_value'))) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'discount_value' => 'A discount value is required for this promotion type.',
            ]);
        }

        if ($data['offer_type'] === 'deal') {
            $data['code'] = null;
        }

        $data['badge_ids'] = $data['badge_ids'] ?? [];
        $data['is_active'] = $request->boolean('is_active');

        if ($offer) {
            // Admin-editable usage counter (SRS) — only applied on update,
            // since a brand-new offer always starts at 0.
            $data['clicks'] = $data['clicks'] ?? $offer->clicks;
        } else {
            unset($data['clicks']);
        }

        return $data;
    }
}
