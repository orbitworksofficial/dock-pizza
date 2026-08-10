<?php

namespace App\Providers;

use App\Models\Store;
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
        View::composer('layouts.app', function ($view) {
            $view->with(
                'stores',
                Store::query()
                    ->where('is_active', true)
                    ->where('slug', '!=', 'dock-pizza-annapolis')
                    ->orderByRaw("CASE WHEN slug = 'dock-pizza-shady-side' THEN 0 ELSE 1 END")
                    ->orderBy('name')
                    ->get()
            );
        });
    }
}
