<?php

namespace App\Http\Middleware;

use App\Models\GeneralSetting;
use App\Models\Menu;
use App\Models\Region;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class ResolvePublicRegion
{
    /**
     * By the time this middleware runs, Laravel's own SubstituteBindings
     * middleware has already resolved the {region} route segment into a
     * Region model via Region::resolveRouteBinding() (404s automatically
     * if the code doesn't exist or isn't active). This just shares it
     * with views and request attributes for convenience.
     */
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Region $region */
        $region = $request->route('region');

        $request->attributes->set('region', $region);

        View::share('region', $region);
        View::share('allRegions', Region::where('is_active', true)->orderBy('sort_order')->get());

        // Header/footer nav (Menu Manager) and footer copy/logo (General
        // Settings) are shared here once per request for every public page,
        // matching how $region/$allRegions are already shared.
        $menusBySlot = Menu::where('region_id', $region->id)->with('items')->get()->keyBy('slot');
        View::share('headerMenu', $menusBySlot->get(Menu::SLOT_HEADER));
        View::share('footerAboutMenu', $menusBySlot->get(Menu::SLOT_FOOTER_ABOUT));
        View::share('footerConnectMenu', $menusBySlot->get(Menu::SLOT_FOOTER_CONNECT));
        View::share('footerShopMenu', $menusBySlot->get(Menu::SLOT_FOOTER_SHOP));
        View::share('generalSettings', GeneralSetting::forRegion($region->id));

        return $next($request);
    }
}
