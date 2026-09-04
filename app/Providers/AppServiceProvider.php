<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::define('manage-users', fn (User $user) => $user->isSuperadmin());

        RateLimiter::for('admin-login', fn ($request) => Limit::perMinutes(5, 5)->by($request->ip()));
        RateLimiter::for('contact-form', fn ($request) => Limit::perMinutes(5, 5)->by($request->ip()));
    }
}
