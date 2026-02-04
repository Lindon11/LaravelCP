<?php

namespace App\Core\Providers;

use App\Core\Models\User;
use Illuminate\Support\Facades\View;
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
        // Configure frontend directory paths
        View::addLocation(base_path('frontend/views'));
        $this->app->singleton('path.lang', fn() => base_path('frontend/lang'));
    }
}

