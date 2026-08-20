<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\Seo\SchemaGraphBuilder;
use App\Services\Seo\SeoManager;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class SeoServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Scoped: one resolution per request, so the row is read once and the
        // next request always sees a fresh save. Deliberately not a TTL cache.
        $this->app->scoped(SeoManager::class);
    }

    public function boot(): void
    {
        View::composer('layouts.app', function ($view) {
            // A controller may pass its own $seo (e.g. a product page); only
            // resolve from the route when it has not.
            if ($view->offsetExists('seo')) {
                return;
            }

            $manager = app(SeoManager::class);
            $pageKey = $this->currentPageKey();
            $page = $manager->forPage($pageKey);

            $view->with([
                'seo' => $page,
                'seoGraph' => app(SchemaGraphBuilder::class)->build($page, $page['faqs']),
            ]);
        });
    }

    /**
     * The request path in page_key form ('/' or '/menu').
     */
    private function currentPageKey(): string
    {
        $path = '/' . trim(request()->path(), '/');

        return $path === '/' ? '/' : rtrim($path, '/');
    }
}
