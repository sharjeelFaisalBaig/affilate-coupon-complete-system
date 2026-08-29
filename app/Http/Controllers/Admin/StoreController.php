<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\GuardsRegionOwnership;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Region;
use App\Models\Store;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StoreController extends Controller
{
    use GuardsRegionOwnership;

    public function index(Request $request): View
    {
        /** @var Region $region */
        $region = $request->attributes->get('activeRegion');

        $query = Store::where('region_id', $region->id)->with('category')->withCount('offers');

        if ($request->filled('category_id')) {
            $category = Category::where('region_id', $region->id)->where('type', 'store')->find($request->integer('category_id'));
            if ($category) {
                $categoryIds = array_merge([$category->id], $category->descendantIds());
                $query->whereIn('category_id', $categoryIds);
            }
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->string('status') === 'active');
        }

        if ($request->filled('q')) {
            $search = $request->string('q')->value();
            $query->where(fn ($q) => $q->where('name', 'like', "%{$search}%")
                ->orWhere('title_prefix', 'like', "%{$search}%")
                ->orWhere('title_suffix', 'like', "%{$search}%")
                ->orWhere('about', 'like', "%{$search}%"));
        }

        if ($request->filled('letter')) {
            $query->where('name', 'like', $request->string('letter').'%');
        }

        if ($request->filled('promotions')) {
            match ($request->string('promotions')->value()) {
                'coupons' => $query->whereHas('coupons', fn ($q) => $q->where('is_active', true)),
                'deals' => $query->whereHas('deals', fn ($q) => $q->where('is_active', true)),
                'both' => $query->whereHas('coupons', fn ($q) => $q->where('is_active', true))
                    ->whereHas('deals', fn ($q) => $q->where('is_active', true)),
                'none' => $query->whereDoesntHave('offers', fn ($q) => $q->where('is_active', true)),
                default => null,
            };
        }

        $stores = $query->orderBy('name')->paginate(20)->withQueryString();
        $categories = Category::where('region_id', $region->id)->where('type', 'store')->orderBy('name')->get(['id', 'parent_id', 'name']);

        if ($request->header('X-Ajax-Filter')) {
            return view('admin.stores._results', compact('stores'));
        }

        return view('admin.stores.index', compact('stores', 'categories'));
    }

    public function create(Request $request): View
    {
        /** @var Region $region */
        $region = $request->attributes->get('activeRegion');
        $categories = Category::where('region_id', $region->id)->where('type', 'store')->orderBy('name')->get();

        return view('admin.stores.form', [
            'store' => new Store(),
            'categories' => $categories,
            'competitorOptions' => Store::where('region_id', $region->id)->orderBy('name')->get(),
            'offerOptions' => collect(),
            'selectedCompetitorIds' => [],
            'selectedFeaturedOfferIds' => [],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        /** @var Region $region */
        $region = $request->attributes->get('activeRegion');

        $data = $this->validated($request);
        $data['region_id'] = $region->id;
        $data['slug'] = $data['slug'] ?: $this->uniqueSlug($data['name'], $region->id);

        // SEO fields default to sensible, editable copy rather than being
        // left blank — an admin should never see an empty settings form for
        // content that's actually live (it just falls back silently
        // otherwise, per the same pattern as PageSetting/StaticPage).
        $data['meta_title'] = $data['meta_title'] ?: "{$data['name']} Promo Codes, Coupons & Deals";
        $data['meta_description'] = $data['meta_description'] ?: "Save with the latest verified {$data['name']} coupon codes and deals in {$region->name}.";
        $data['og_title'] = $data['og_title'] ?: $data['meta_title'];
        $data['og_description'] = $data['og_description'] ?: $data['meta_description'];

        if ($request->hasFile('logo')) {
            $data['logo_path'] = $request->file('logo')->store('stores', 'public');
        }

        if ($request->hasFile('banner_image')) {
            $data['banner_image'] = $request->file('banner_image')->store('stores/banners', 'public');
        }

        $store = Store::create($data);
        $this->syncSelections($request, $store);

        return redirect()->route('admin.stores.index')->with('status', 'Store created.');
    }

    public function edit(Request $request, Store $store): View
    {
        $this->abortUnlessOwnedByActiveRegion($request, $store->region_id);

        $categories = Category::where('region_id', $store->region_id)->where('type', 'store')->orderBy('name')->get();

        return view('admin.stores.form', [
            'store' => $store,
            'categories' => $categories,
            'competitorOptions' => Store::where('region_id', $store->region_id)->where('id', '!=', $store->id)->orderBy('name')->get(),
            'offerOptions' => $store->offers()->orderBy('title')->get(),
            'selectedCompetitorIds' => $store->relatedStores->pluck('id')->all(),
            'selectedFeaturedOfferIds' => $store->featuredOffers->pluck('id')->all(),
        ]);
    }

    public function update(Request $request, Store $store): RedirectResponse
    {
        $this->abortUnlessOwnedByActiveRegion($request, $store->region_id);

        $data = $this->validated($request, $store);
        if ($data['slug']) {
            // Admin explicitly typed a slug — already validated unique below.
        } elseif ($data['name'] !== $store->name) {
            $data['slug'] = $this->uniqueSlug($data['name'], $store->region_id, $store->id);
        } else {
            unset($data['slug']);
        }

        if ($request->hasFile('logo')) {
            if ($store->logo_path) {
                Storage::disk('public')->delete($store->logo_path);
            }
            $data['logo_path'] = $request->file('logo')->store('stores', 'public');
        }

        if ($request->hasFile('banner_image')) {
            if ($store->banner_image) {
                Storage::disk('public')->delete($store->banner_image);
            }
            $data['banner_image'] = $request->file('banner_image')->store('stores/banners', 'public');
        }

        $store->update($data);
        $this->syncSelections($request, $store);

        return redirect()->route('admin.stores.index')->with('status', 'Store updated.');
    }

    public function destroy(Request $request, Store $store): RedirectResponse
    {
        $this->abortUnlessOwnedByActiveRegion($request, $store->region_id);

        if ($store->offers()->where('is_active', true)->exists()) {
            return back()->with('error', 'This store has active coupons or deals and cannot be deleted. Deactivate or remove them first.');
        }

        if ($store->logo_path) {
            Storage::disk('public')->delete($store->logo_path);
        }
        if ($store->banner_image) {
            Storage::disk('public')->delete($store->banner_image);
        }

        $store->delete();

        return redirect()->route('admin.stores.index')->with('status', 'Store deleted.');
    }

    public function toggleActive(Request $request, Store $store): RedirectResponse
    {
        $this->abortUnlessOwnedByActiveRegion($request, $store->region_id);

        $store->update(['is_active' => ! $store->is_active]);

        return back()->with('status', $store->is_active ? 'Store activated.' : 'Store deactivated.');
    }

    public function classification(Request $request): View
    {
        /** @var Region $region */
        $region = $request->attributes->get('activeRegion');

        $featured = Store::where('region_id', $region->id)->where('is_featured', true)->orderBy('featured_order')->get();
        $popular = Store::where('region_id', $region->id)->where('is_popular', true)->orderBy('popular_order')->get();

        return view('admin.stores.classification', compact('featured', 'popular'));
    }

    public function reorderFeatured(Request $request): Response
    {
        return $this->reorder($request, 'featured_order');
    }

    public function reorderPopular(Request $request): Response
    {
        return $this->reorder($request, 'popular_order');
    }

    private function reorder(Request $request, string $column): Response
    {
        /** @var Region $region */
        $region = $request->attributes->get('activeRegion');
        $ids = $request->validate(['ids' => ['required', 'array']])['ids'];

        foreach ($ids as $index => $id) {
            Store::where('id', $id)->where('region_id', $region->id)->update([$column => $index + 1]);
        }

        return response()->noContent();
    }

    /**
     * Competitor stores (store_related) and the "More Verified Discount
     * Codes" picker (store_featured_offer) both need to exist before we can
     * sync them, so this only runs after Store::create()/update().
     */
    private function syncSelections(Request $request, Store $store): void
    {
        $competitorIds = collect($request->input('competitor_ids', []))
            ->filter(fn ($id) => Store::where('id', $id)->where('region_id', $store->region_id)->where('id', '!=', $store->id)->exists())
            ->values();

        $store->relatedStores()->sync(
            $competitorIds->mapWithKeys(fn ($id, $i) => [$id => ['sort_order' => $i + 1]])->all()
        );

        $featuredOfferIds = collect($request->input('featured_offer_ids', []))
            ->filter(fn ($id) => $store->offers()->where('id', $id)->exists())
            ->values();

        $store->featuredOffers()->sync(
            $featuredOfferIds->mapWithKeys(fn ($id, $i) => [$id => ['sort_order' => $i + 1]])->all()
        );
    }

    private function validated(Request $request, ?Store $store = null): array
    {
        /** @var Region $region */
        $region = $request->attributes->get('activeRegion');

        $data = $request->validate([
            'category_id' => [
                'nullable',
                Rule::exists('categories', 'id')->where('region_id', $region->id)->where('type', 'store'),
            ],
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable', 'string', 'max:255', 'alpha_dash',
                Rule::unique('stores', 'slug')->where('region_id', $region->id)->ignore($store),
            ],
            'title_prefix' => ['nullable', 'string', 'max:100'],
            'title_suffix' => ['nullable', 'string', 'max:100'],
            'about' => ['nullable', 'string'],
            'website_url' => ['nullable', 'url', 'max:255'],
            'affiliate_url' => ['required', 'url', 'max:255'],
            'expiry_date' => ['nullable', 'date'],
            'star_rating' => ['required', 'numeric', 'min:0', 'max:5'],
            'reviews_count' => ['nullable', 'integer', 'min:0'],
            'logo' => ['nullable', 'image', 'max:5120'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:255'],
            'meta_keywords' => ['nullable', 'string', 'max:255'],
            'canonical_url' => ['nullable', 'url', 'max:255'],
            'og_title' => ['nullable', 'string', 'max:255'],
            'og_description' => ['nullable', 'string', 'max:255'],
            'head_start_script' => ['nullable', 'string'],
            'head_end_script' => ['nullable', 'string'],
            'body_start_script' => ['nullable', 'string'],
            'body_end_script' => ['nullable', 'string'],
            'faqs' => ['nullable', 'array'],
            'faqs.*.question' => ['nullable', 'string', 'max:500'],
            'faqs.*.answer' => ['nullable', 'string'],
            'banner_heading' => ['nullable', 'string', 'max:255'],
            'banner_text' => ['nullable', 'string'],
            'banner_image' => ['nullable', 'image', 'max:5120'],
            'banner_button_text' => ['nullable', 'string', 'max:100'],
            'banner_button_url' => ['nullable', 'string', 'max:255'],
            'curate_right_content' => ['nullable', 'string'],
            'custom_sections' => ['nullable', 'array'],
            'custom_sections.*.title' => ['nullable', 'string', 'max:255'],
            'custom_sections.*.content' => ['nullable', 'string'],
        ]);

        $data['faqs'] = collect($data['faqs'] ?? [])
            ->filter(fn ($faq) => filled($faq['question'] ?? null) && filled($faq['answer'] ?? null))
            ->values()
            ->all();

        $data['custom_sections'] = collect($data['custom_sections'] ?? [])
            ->filter(fn ($section) => filled($section['title'] ?? null) && filled($section['content'] ?? null))
            ->values()
            ->all();

        $data['reviews_count'] = $data['reviews_count'] ?? 0;
        $data['is_featured'] = $request->boolean('is_featured');
        $data['is_popular'] = $request->boolean('is_popular');
        $data['is_active'] = $request->boolean('is_active');
        $data['robots_index'] = $request->boolean('robots_index');
        $data['robots_follow'] = $request->boolean('robots_follow');

        return $data;
    }

    private function uniqueSlug(string $name, int $regionId, ?int $exceptId = null): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $suffix = 1;

        while (
            Store::where('region_id', $regionId)->where('slug', $slug)
                ->when($exceptId, fn ($q) => $q->where('id', '!=', $exceptId))
                ->exists()
        ) {
            $slug = "{$base}-".++$suffix;
        }

        return $slug;
    }
}
