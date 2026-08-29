<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PageSetting;
use App\Models\Region;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PageSettingController extends Controller
{
    public const PAGES = [
        'home' => 'Homepage (/)',
        'stores' => 'Stores Directory (/stores)',
        'coupons' => 'Promo Codes (/coupons)',
        'blogs' => 'Blog Listing (/blogs)',
    ];

    /**
     * Each page's heading/SEO/scripts lives on its own screen — a single
     * page mixing all 4 pages' settings together read as one generic,
     * confusing "Page Settings" catch-all.
     */
    public function edit(Request $request, string $pageKey): View
    {
        abort_unless(array_key_exists($pageKey, self::PAGES), 404);

        /** @var Region $region */
        $region = $request->attributes->get('activeRegion');

        $settings = PageSetting::forPage($region->id, $pageKey) ?? new PageSetting(['page_key' => $pageKey]);

        return view('admin.page-settings.edit', [
            'settings' => $settings,
            'pageKey' => $pageKey,
            'pageLabel' => self::PAGES[$pageKey],
        ]);
    }

    public function update(Request $request, string $pageKey): RedirectResponse
    {
        abort_unless(array_key_exists($pageKey, self::PAGES), 404);

        /** @var Region $region */
        $region = $request->attributes->get('activeRegion');

        $data = $request->validate([
            'heading' => ['nullable', 'string', 'max:255'],
            'subheading' => ['nullable', 'string', 'max:1000'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:255'],
            'og_title' => ['nullable', 'string', 'max:255'],
            'og_description' => ['nullable', 'string', 'max:255'],
            'schema_script' => ['nullable', 'string'],
            'head_start_script' => ['nullable', 'string'],
            'head_end_script' => ['nullable', 'string'],
            'body_start_script' => ['nullable', 'string'],
            'body_end_script' => ['nullable', 'string'],
        ]);

        $data['is_active'] = $request->boolean('is_active');
        $data['robots_index'] = $request->boolean('robots_index');
        $data['robots_follow'] = $request->boolean('robots_follow');

        PageSetting::updateOrCreate(
            ['region_id' => $region->id, 'page_key' => $pageKey],
            $data
        );

        return redirect()->route('admin.page-settings.edit', $pageKey)->with('status', self::PAGES[$pageKey].' settings updated.');
    }
}
