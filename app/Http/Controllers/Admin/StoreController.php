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

        $query = Store::where('region_id', $region->id)->with('category');

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->integer('category_id'));
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->string('status') === 'active');
        }

        if ($request->filled('q')) {
            $search = $request->string('q')->value();
            $query->where('name', 'like', "%{$search}%");
        }

        $stores = $query->orderBy('name')->paginate(20)->withQueryString();
        $categories = Category::where('region_id', $region->id)->where('type', 'store')->orderBy('name')->get(['id', 'name']);

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

        Store::create($data);

        return redirect()->route('admin.stores.index')->with('status', 'Store created.');
    }

    public function edit(Request $request, Store $store): View
    {
        $this->abortUnlessOwnedByActiveRegion($request, $store->region_id);

        $categories = Category::where('region_id', $store->region_id)->where('type', 'store')->orderBy('name')->get();

        return view('admin.stores.form', [
            'store' => $store,
            'categories' => $categories,
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

        $store->update($data);

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
            'about' => ['nullable', 'string'],
            'website_url' => ['nullable', 'url', 'max:255'],
            'affiliate_url' => ['required', 'url', 'max:255'],
            'expiry_date' => ['nullable', 'date'],
            'star_rating' => ['required', 'numeric', 'min:0', 'max:5'],
            'reviews_count' => ['nullable', 'integer', 'min:0'],
            'logo' => ['nullable', 'image', 'max:5120', 'dimensions:width=200,height=200'],
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
        ]);

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
