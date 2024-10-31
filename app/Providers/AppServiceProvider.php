<?php

namespace App\Providers;

use App\Models\Installation;
use App\Observers\InstallationObserver;
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
    public function boot()
    {
        // Remove any existing observers first
        Installation::flushEventListeners();

        // Register the observer
        Installation::observe(InstallationObserver::class);
    }
}
