<?php

use App\Http\Controllers\Public\BlogController;
use App\Http\Controllers\Public\CategoryController;
use App\Http\Controllers\Public\ContactController;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\OfferRedirectController;
use App\Http\Controllers\Public\PromoCodeController;
use App\Http\Controllers\Public\SitemapController;
use App\Http\Controllers\Public\StaticPageController;
use App\Http\Controllers\Public\StoreController;
use App\Http\Controllers\Public\StoreDirectoryController;
use App\Models\Region;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $default = Region::where('is_active', true)->orderBy('sort_order')->first();
    abort_if(! $default, 404);

    return redirect("/{$default->code}");
})->name('public.root');

Route::get('sitemap.xml', [SitemapController::class, 'index'])->name('public.sitemap');
Route::get('robots.txt', [SitemapController::class, 'robots'])->name('public.robots');

// Admin routes are static prefixes ("admin/...") and must be registered
// before the dynamic {region} catch-all below — otherwise a request like
// GET /admin matches {region}="admin" first and never reaches the panel.
require __DIR__.'/admin.php';

Route::prefix('{region}')->middleware('public.region')->where(['region' => '[a-z]{2,4}'])->group(function () {
    Route::get('/', [HomeController::class, 'index'])->name('public.home');

    Route::get('stores', [StoreDirectoryController::class, 'index'])->name('public.stores');
    Route::get('store/{storeSlug}', [StoreController::class, 'show'])->name('public.store');

    Route::get('coupons', [PromoCodeController::class, 'index'])->name('public.coupons');

    Route::get('category/{categorySlug}', [CategoryController::class, 'show'])->name('public.category');

    Route::get('blogs', [BlogController::class, 'index'])->name('public.blogs');
    Route::get('blog/{blogSlug}', [BlogController::class, 'show'])->name('public.blog');

    Route::get('p/{pageSlug}', [StaticPageController::class, 'show'])->name('public.page');
    Route::post('contact', [ContactController::class, 'store'])->name('public.contact.submit');

    Route::get('go/{offer}', OfferRedirectController::class)->name('public.offer.redirect');
});
