<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\PageSetting;
use App\Models\Region;
use Illuminate\Http\Request;

/**
 * Catch-all for the 4 fixed pages (home/stores/coupons/blogs) whose URL
 * segment is admin-renameable per region via PageSetting.slug — registered
 * last in the {region} route group (routes/web.php) so the more specific
 * routes (store/{slug}, category/{slug}, blog/{slug}, p/{slug}, etc.)
 * always match first. Resolves the incoming segment back to a page key and
 * forwards to that page's own existing controller, whose logic is otherwise
 * unchanged — this is purely a routing indirection, not a rewrite of any
 * page's behavior.
 */
class PageRouterController extends Controller
{
    private const CONTROLLERS = [
        'home' => HomeController::class,
        'stores' => StoreDirectoryController::class,
        'coupons' => PromoCodeController::class,
        'blogs' => BlogController::class,
    ];

    public function __invoke(Request $request, Region $region, string $slug = '')
    {
        $pageKey = PageSetting::resolveSlug($region, $slug);
        abort_if($pageKey === null, 404);

        return app()->call([app(self::CONTROLLERS[$pageKey]), 'index'], ['region' => $region]);
    }
}
