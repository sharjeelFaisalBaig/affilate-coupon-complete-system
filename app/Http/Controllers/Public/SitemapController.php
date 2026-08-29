<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\Category;
use App\Models\PageSetting;
use App\Models\Region;
use App\Models\StaticPage;
use App\Models\Store;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Response;

class SitemapController extends Controller
{
    public function index()
    {
        $xml = Cache::remember('sitemap.xml', now()->addHour(), function () {
            $urls = [];

            foreach (Region::where('is_active', true)->get() as $region) {
                $pageSettings = PageSetting::where('region_id', $region->id)->get()->keyBy('page_key');

                $fixedPages = [
                    'home' => ['loc' => url("/{$region->code}"), 'priority' => '1.0'],
                    'stores' => ['loc' => url("/{$region->code}/stores"), 'priority' => '0.8'],
                    'coupons' => ['loc' => url("/{$region->code}/coupons"), 'priority' => '0.8'],
                    'blogs' => ['loc' => url("/{$region->code}/blogs"), 'priority' => '0.6'],
                ];

                foreach ($fixedPages as $pageKey => $entry) {
                    $setting = $pageSettings->get($pageKey);
                    // Draft pages 404 on the frontend and noindex pages ask
                    // crawlers not to index them — neither belongs in the
                    // sitemap.
                    if ($setting && (! $setting->is_active || ! $setting->robots_index)) {
                        continue;
                    }
                    $urls[] = $entry;
                }

                foreach (Category::where('region_id', $region->id)->where('type', 'store')->where('is_active', true)->get() as $category) {
                    $urls[] = ['loc' => url("/{$region->code}/category/{$category->slug}"), 'priority' => '0.7'];
                }

                foreach (Store::where('region_id', $region->id)->where('is_active', true)->where('robots_index', true)->get() as $store) {
                    $urls[] = ['loc' => url("/{$region->code}/store/{$store->slug}"), 'priority' => '0.7'];
                }

                foreach (Blog::where('region_id', $region->id)->where('is_published', true)->where('robots_index', true)->get() as $blog) {
                    $urls[] = ['loc' => url("/{$region->code}/blog/{$blog->slug}"), 'priority' => '0.5'];
                }

                foreach (StaticPage::where('region_id', $region->id)->where('is_active', true)->where('robots_index', true)->get() as $page) {
                    $urls[] = ['loc' => url("/{$region->code}/p/{$page->slug}"), 'priority' => '0.3'];
                }
            }

            return view('public.sitemap', ['urls' => $urls])->render();
        });

        return Response::make($xml, 200, ['Content-Type' => 'application/xml']);
    }

    /**
     * Dynamic so the Sitemap: directive always points at this environment's
     * real domain — a static public/robots.txt would need hand-editing on
     * every deploy target.
     */
    public function robots()
    {
        $lines = [
            'User-agent: *',
            'Disallow: /admin',
            '',
            'Sitemap: '.url('/sitemap.xml'),
        ];

        return Response::make(implode("\n", $lines)."\n", 200, ['Content-Type' => 'text/plain']);
    }
}
