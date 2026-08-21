<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Models\Product;
use App\Models\SeoMeta;
use Illuminate\Http\Response;

/**
 * robots.txt and sitemap.xml are generated from routes and published content
 * rather than hand-maintained files, so they cannot drift out of date.
 */
class SitemapController extends Controller
{
    public function sitemap(): Response
    {
        $xml = view('seo.sitemap', ['urls' => $this->urls()])->render();

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }

    public function robots(): Response
    {
        $lines = [
            'User-agent: *',
            'Allow: /',
            '',
            '# Order and account flows carry no search value',
            'Disallow: /checkout',
            'Disallow: /orders',
            'Disallow: /login',
            'Disallow: /register',
            'Disallow: /order/',
            'Disallow: /clear-cache',
            'Disallow: /seed-menu',
            'Disallow: /admin',
            '',
            'Sitemap: ' . url('/sitemap.xml'),
        ];

        return response(implode("\n", $lines) . "\n", 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }

    /**
     * Public, indexable URLs.
     *
     * @return array<int, array{loc: string, lastmod: string|null, changefreq: string, priority: string}>
     */
    public function urls(): array
    {
        $noindex = $this->noindexKeys();
        $urls = [];

        foreach (['/' => '1.0', '/menu' => '0.9', '/catering' => '0.8', '/blog' => '0.8'] as $path => $priority) {
            if (in_array($path, $noindex, true)) {
                continue;
            }

            $urls[] = [
                'loc' => url($path),
                'lastmod' => optional(SeoMeta::where('page_key', $path)->first())?->updated_at?->toAtomString(),
                'changefreq' => 'weekly',
                'priority' => $priority,
            ];
        }

        Product::query()
            ->where('is_active', true)
            ->select(['slug', 'updated_at'])
            ->orderBy('id')
            ->chunk(200, function ($products) use (&$urls) {
                foreach ($products as $product) {
                    $urls[] = [
                        'loc' => url('/menu/product/' . $product->slug),
                        'lastmod' => $product->updated_at?->toAtomString(),
                        'changefreq' => 'weekly',
                        'priority' => '0.7',
                    ];
                }
            });

        // Published posts, excluding any marked noindex.
        BlogPost::published()
            ->where(function ($q) {
                $q->whereNull('robots')->orWhere('robots', 'not like', '%noindex%');
            })
            ->select(['slug', 'updated_at'])
            ->orderByDesc('published_at')
            ->chunk(200, function ($posts) use (&$urls) {
                foreach ($posts as $post) {
                    $urls[] = [
                        'loc' => url('/blog/' . $post->slug),
                        'lastmod' => $post->updated_at?->toAtomString(),
                        'changefreq' => 'monthly',
                        'priority' => '0.6',
                    ];
                }
            });

        return $urls;
    }

    /**
     * Page keys explicitly marked noindex, from the database or config.
     *
     * @return array<int, string>
     */
    private function noindexKeys(): array
    {
        $fromConfig = collect(config('seo.pages'))
            ->filter(fn (array $page) => str_contains($page['robots'] ?? '', 'noindex'))
            ->keys();

        $fromDb = SeoMeta::forPages()
            ->where('robots', 'like', '%noindex%')
            ->pluck('page_key');

        return $fromConfig->merge($fromDb)->unique()->values()->all();
    }
}
