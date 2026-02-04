<?php

namespace App\Core\Providers;

use App\Core\Models\User;
use App\Core\Services\TextFormatterService;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register TextFormatter service
        $this->app->singleton('text-formatter', function ($app) {
            return new TextFormatterService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Configure frontend directory paths
        View::addLocation(base_path('frontend/views'));
        $this->app->singleton('path.lang', fn() => base_path('frontend/lang'));
    }
}

