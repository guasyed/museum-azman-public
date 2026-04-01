<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\URL;
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
        Paginator::useBootstrapFive();

        // On production/staging the app sits behind an HTTPS reverse proxy.
        // Force HTTPS so every generated URL (assets, form actions, redirects)
        // uses https:// and avoids Mixed Content errors.
        if (! $this->app->environment('local')) {
            URL::forceScheme('https');
        }
    }
}
