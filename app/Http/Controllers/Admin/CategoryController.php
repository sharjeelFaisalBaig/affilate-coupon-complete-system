<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\GuardsRegionOwnership;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Region;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Manages "store" categories — the taxonomy used to organize/browse Stores
 * (see /stores and /category/{slug}). Promotions have no category of their
 * own; they inherit their category from the assigned store (SRS §9), so
 * this is the only category taxonomy in the system.
 */
class CategoryController extends Controller
{
    use GuardsRegionOwnership;

    private const TYPE = 'store';

    /**
     * A category at this depth (0-indexed) can never be chosen as a parent —
     * doing so would create a 5th level, exceeding the SRS's 4-level cap.
     */
    private const MAX_PARENT_DEPTH = 2;

    public function index(Request $request): View
    {
        /** @var Region $region */
        $region = $request->attributes->get('activeRegion');

        $query = Category::where('region_id', $region->id)->where('type', self::TYPE)
            ->with('parent')
            ->withCount(['stores as active_stores_count' => fn ($q) => $q->where('is_active', true), 'children']);

        if ($request->filled('q')) {
            $search = $request->string('q')->value();
            $query->where(fn ($q) => $q->where('name', 'like', "%{$search}%")->orWhere('slug', 'like', "%{$search}%"));
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->string('status') === 'active');
        }

        $categories = $query->orderBy('sort_order')->get();

        if ($request->filled('level')) {
            $level = $request->integer('level');
            $categories = $categories->filter(fn ($c) => $c->depth() === $level)->values();
        }

        $categories = new \Illuminate\Pagination\LengthAwarePaginator(
            $categories->forPage($request->integer('page', 1), 20),
            $categories->count(),
            20,
            $request->integer('page', 1),
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('admin.categories.index', compact('categories'));
    }

    public function create(Request $request): View
    {
        /** @var Region $region */
        $region = $request->attributes->get('activeRegion');

        return view('admin.categories.form', [
            'category' => new Category(),
            'parentOptions' => $this->parentOptions($region->id),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        /** @var Region $region */
        $region = $request->attributes->get('activeRegion');

        $data = $this->validated($request);
        $data['region_id'] = $region->id;
        $data['type'] = self::TYPE;
        $data['slug'] = $data['slug'] ?: $this->uniqueSlug($data['name'], $region->id);

        if ($request->hasFile('icon_image')) {
            $data['icon_path'] = $request->file('icon_image')->store('categories/icons', 'public');
        }

        Category::create($data);

        return redirect()->route('admin.categories.index')->with('status', 'Category created.');
    }

    public function edit(Request $request, Category $category): View
    {
        $this->abortUnlessOwnedByActiveRegion($request, $category->region_id);

        return view('admin.categories.form', [
            'category' => $category,
            'parentOptions' => $this->parentOptions($category->region_id, $category),
        ]);
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $this->abortUnlessOwnedByActiveRegion($request, $category->region_id);

        $data = $this->validated($request, $category);

        if ($data['slug']) {
            // Admin explicitly typed a slug — already validated unique above.
        } elseif ($category->name !== $data['name']) {
            $data['slug'] = $this->uniqueSlug($data['name'], $category->region_id, $category->id);
        } else {
            unset($data['slug']);
        }

        if ($request->hasFile('icon_image')) {
            if ($category->icon_path) {
                Storage::disk('public')->delete($category->icon_path);
            }
            $data['icon_path'] = $request->file('icon_image')->store('categories/icons', 'public');
        }

        $category->update($data);

        return redirect()->route('admin.categories.index')->with('status', 'Category updated.');
    }

    public function destroy(Request $request, Category $category): RedirectResponse
    {
        $this->abortUnlessOwnedByActiveRegion($request, $category->region_id);

        if ($category->stores()->exists()) {
            return back()->with('error', 'This category still has stores assigned to it and cannot be deleted. Reassign them first.');
        }

        if ($category->children()->exists()) {
            return back()->with('error', 'This category still has subcategories and cannot be deleted. Remove or reassign them first.');
        }

        if ($category->icon_path) {
            Storage::disk('public')->delete($category->icon_path);
        }

        $category->delete();

        return redirect()->route('admin.categories.index')->with('status', 'Category deleted.');
    }

    public function reorder(Request $request): Response
    {
        /** @var Region $region */
        $region = $request->attributes->get('activeRegion');
        $ids = $request->validate(['ids' => ['required', 'array']])['ids'];

        foreach ($ids as $index => $id) {
            Category::where('id', $id)->where('region_id', $region->id)->where('type', self::TYPE)
                ->update(['sort_order' => $index + 1]);
        }

        return response()->noContent();
    }

    private function validated(Request $request, ?Category $category = null): array
    {
        /** @var Region $region */
        $region = $request->attributes->get('activeRegion');

        $excludedParentIds = $category ? array_merge([$category->id], $category->descendantIds()) : [];

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'parent_id' => [
                'nullable',
                Rule::exists('categories', 'id')->where('region_id', $region->id)->where('type', self::TYPE),
                Rule::notIn($excludedParentIds),
            ],
            'slug' => [
                'nullable', 'string', 'max:255', 'alpha_dash',
                Rule::unique('categories', 'slug')->where('region_id', $region->id)->where('type', self::TYPE)->ignore($category),
            ],
            'icon' => ['nullable', 'string', 'max:255'],
            'icon_image' => ['nullable', 'image', 'max:512'],
            'description' => ['nullable', 'string'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:255'],
        ]);

        $data['parent_id'] = $request->input('parent_id') ?: null;

        if ($data['parent_id']) {
            $parent = Category::find($data['parent_id']);
            if ($parent && $parent->depth() > self::MAX_PARENT_DEPTH) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'parent_id' => 'That category is already at the maximum depth (4 levels) and cannot have subcategories.',
                ]);
            }
        }

        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }

    private function uniqueSlug(string $name, int $regionId, ?int $exceptId = null): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $suffix = 1;

        while (
            Category::where('region_id', $regionId)->where('type', self::TYPE)->where('slug', $slug)
                ->when($exceptId, fn ($q) => $q->where('id', '!=', $exceptId))
                ->exists()
        ) {
            $slug = "{$base}-".++$suffix;
        }

        return $slug;
    }

    /**
     * Categories already at the max parent depth are excluded — choosing
     * one as a parent would create a 5th level. When editing, the category
     * itself and its own descendants are excluded too (no cycles).
     */
    private function parentOptions(int $regionId, ?Category $exclude = null)
    {
        $excludedIds = $exclude ? array_merge([$exclude->id], $exclude->descendantIds()) : [];

        return Category::where('region_id', $regionId)->where('type', self::TYPE)
            ->when($excludedIds, fn ($q) => $q->whereNotIn('id', $excludedIds))
            ->orderBy('name')
            ->get()
            ->filter(fn ($c) => $c->depth() <= self::MAX_PARENT_DEPTH)
            ->sortBy(fn ($c) => $c->breadcrumbLabel())
            ->values();
    }
}
