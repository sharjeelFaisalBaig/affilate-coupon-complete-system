<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\GuardsRegionOwnership;
use App\Http\Controllers\Controller;
use App\Models\PromotionType;
use App\Models\Region;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PromotionTypeController extends Controller
{
    use GuardsRegionOwnership;

    public function index(Request $request): View
    {
        /** @var Region $region */
        $region = $request->attributes->get('activeRegion');

        $types = PromotionType::where('region_id', $region->id)->withCount('offers')->orderBy('title')->get();

        return view('admin.promotion-types.index', compact('types'));
    }

    public function create(): View
    {
        return view('admin.promotion-types.form', ['type' => new PromotionType()]);
    }

    public function store(Request $request): RedirectResponse
    {
        /** @var Region $region */
        $region = $request->attributes->get('activeRegion');

        $data = $this->validated($request);
        $data['region_id'] = $region->id;
        $data['slug'] = $this->uniqueSlug($data['title'], $region->id);
        $data['is_system_default'] = false;
        // Custom types are always free-form/text-driven — only the 3
        // protected system defaults represent numeric discount formats.
        $data['discount_format'] = 'custom_text';

        PromotionType::create($data);

        return redirect()->route('admin.promotion-types.index')->with('status', 'Promotion type created.');
    }

    public function edit(Request $request, PromotionType $promotionType): View
    {
        $this->abortUnlessOwnedByActiveRegion($request, $promotionType->region_id);

        return view('admin.promotion-types.form', ['type' => $promotionType]);
    }

    public function update(Request $request, PromotionType $promotionType): RedirectResponse
    {
        $this->abortUnlessOwnedByActiveRegion($request, $promotionType->region_id);

        $data = $this->validated($request, $promotionType);

        if (! $promotionType->is_system_default) {
            $data['slug'] = $this->uniqueSlug($data['title'], $promotionType->region_id, $promotionType->id);
            $data['discount_format'] = 'custom_text';
        } else {
            // System defaults keep their slug/discount_format stable — only
            // the display title may be customized per region.
            unset($data['discount_format']);
        }

        $promotionType->update($data);

        return redirect()->route('admin.promotion-types.index')->with('status', 'Promotion type updated.');
    }

    public function destroy(Request $request, PromotionType $promotionType): RedirectResponse
    {
        $this->abortUnlessOwnedByActiveRegion($request, $promotionType->region_id);

        if ($promotionType->is_system_default) {
            return back()->with('error', 'System default promotion types cannot be deleted.');
        }

        if ($promotionType->offers()->exists()) {
            return back()->with('error', 'This promotion type is assigned to active promotions and cannot be deleted.');
        }

        $promotionType->delete();

        return redirect()->route('admin.promotion-types.index')->with('status', 'Promotion type deleted.');
    }

    private function validated(Request $request, ?PromotionType $type = null): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
        ]);
    }

    private function uniqueSlug(string $title, int $regionId, ?int $exceptId = null): string
    {
        $base = Str::slug($title);
        $slug = $base;
        $suffix = 1;

        while (
            PromotionType::where('region_id', $regionId)->where('slug', $slug)
                ->when($exceptId, fn ($q) => $q->where('id', '!=', $exceptId))
                ->exists()
        ) {
            $slug = "{$base}-".++$suffix;
        }

        return $slug;
    }
}
