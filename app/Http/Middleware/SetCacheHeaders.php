<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Cache-Control policy.
 *
 * The rule that matters: asset caching is decided by *file extension*, never
 * by path prefix. A prefix rule such as `/(images|services)/*` also matches
 * the `/services` HTML page, and a 30-day max-age there means content and SEO
 * edits stay invisible until the cache expires.
 *
 * HTML is therefore always no-store, and only genuinely static file
 * extensions receive a long max-age.
 */
class SetCacheHeaders
{
    /**
     * Fingerprinted or immutable static assets.
     */
    private const ASSET_EXTENSIONS = [
        'css', 'js', 'mjs', 'map',
        'jpg', 'jpeg', 'png', 'gif', 'webp', 'avif', 'ico', 'bmp',
        'woff', 'woff2', 'ttf', 'otf', 'eot',
        'mp4', 'webm', 'mp3', 'ogg', 'pdf',
    ];

    /**
     * Generated text files that should be fresh but are cheap to revalidate.
     */
    private const SHORT_CACHE_PATHS = ['sitemap.xml', 'robots.txt'];

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Never touch a response that already set its own policy.
        if ($response->headers->has('Cache-Control')
            && $response->headers->getCacheControlDirective('max-age') !== null) {
            return $response;
        }

        $path = ltrim($request->path(), '/');
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        if ($extension !== '' && in_array($extension, self::ASSET_EXTENSIONS, true)) {
            $response->headers->set('Cache-Control', 'public, max-age=31536000, immutable');

            return $response;
        }

        if (in_array($path, self::SHORT_CACHE_PATHS, true)) {
            $response->headers->set('Cache-Control', 'public, max-age=3600');

            return $response;
        }

        // Everything else is HTML or an API response: always revalidate, so a
        // dashboard save is visible on the very next request.
        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');

        return $response;
    }
}
