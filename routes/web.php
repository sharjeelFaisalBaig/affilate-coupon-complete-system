<?php

use App\Http\Controllers\Public\BlogController;
use App\Http\Controllers\Public\CategoryController;
use App\Http\Controllers\Public\ContactController;
use App\Http\Controllers\Public\OfferRedirectController;
use App\Http\Controllers\Public\PageRouterController;
use App\Http\Controllers\Public\PromoCodeController;
use App\Http\Controllers\Public\SitemapController;
use App\Http\Controllers\Public\StaticPageController;
use App\Http\Controllers\Public\StoreController;
use App\Http\Controllers\Public\StoreDirectoryController;
use App\Http\Controllers\Public\SystemController;
use App\Models\Region;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $default = Region::where('is_active', true)->orderBy('sort_order')->first();
    abort_if(! $default, 404);

    return redirect("/{$default->code}");
})->name('public.root');

Route::get('sitemap.xml', [SitemapController::class, 'index'])->name('public.sitemap');
Route::get('robots.txt', [SitemapController::class, 'robots'])->name('public.robots');

// Render's free tier has no SSH/one-off job support, so this is the only
// way to run `optimize:clear` after a deploy — gated by DEPLOY_TOKEN, not
// truly open (see SystemController).
Route::get('system/optimize-clear', [SystemController::class, 'optimizeClear'])->name('system.optimize-clear');

// Admin routes are static prefixes ("admin/...") and must be registered
// before the dynamic {region} catch-all below — otherwise a request like
// GET /admin matches {region}="admin" first and never reaches the panel.
require __DIR__.'/admin.php';

Route::prefix('{region}')->middleware('public.region')->where(['region' => '[a-z]{2,4}'])->group(function () {
    Route::get('store/{storeSlug}', [StoreController::class, 'show'])->name('public.store');

    Route::get('category/{categorySlug}', [CategoryController::class, 'show'])->name('public.category');

    Route::get('blog/{blogSlug}', [BlogController::class, 'show'])->name('public.blog');

    Route::get('suggest/stores', [StoreDirectoryController::class, 'suggest'])->name('public.suggest.stores');
    Route::get('suggest/coupons', [PromoCodeController::class, 'suggest'])->name('public.suggest.coupons');
    Route::get('suggest/blogs', [BlogController::class, 'suggest'])->name('public.suggest.blogs');

    Route::get('p/{pageSlug}', [StaticPageController::class, 'show'])->name('public.page');
    Route::post('contact', [ContactController::class, 'store'])->middleware('throttle:contact-form')->name('public.contact.submit');

    Route::get('go/{offer}', OfferRedirectController::class)->name('public.offer.redirect');

    // Catch-all for the 4 fixed pages (home/stores/coupons/blogs), whose URL
    // segment is admin-renameable per region (PageSetting.slug) — must stay
    // LAST in this group so every more specific route above always wins.
    // Build outbound links to these pages via PageSetting::urlFor(), not
    // route('public.home'|'public.stores'|'public.coupons'|'public.blogs'),
    // since those route names no longer exist.
    Route::get('{slug?}', PageRouterController::class)->where('slug', '[a-z0-9-]*')->name('public.page-router');
});
