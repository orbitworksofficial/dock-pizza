<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\SitemapController;
use App\Services\Seo\SchemaGraphBuilder;
use App\Services\Seo\SeoManager;
use App\Services\Seo\SocialUrlNormalizer;
use Illuminate\View\View;

/**
 * Read-only view of what is actually being served, and where each part of it
 * comes from. Nothing here is editable by design.
 */
class TechnicalSeoController extends Controller
{
    public function index(
        SitemapController $sitemap,
        SchemaGraphBuilder $graph,
        SeoManager $seo,
        SocialUrlNormalizer $social,
    ): View {
        $analytics = $this->analyticsState();

        return view('admin.seo.technical', [
            'robots' => $sitemap->robots()->getContent(),
            'sitemapUrls' => $sitemap->urls(),
            'graph' => json_encode(
                $graph->build($seo->forPage('/')),
                JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
            ),
            'sameAs' => $social->normalizeMany(config('seo.social', [])),
            'rawSocial' => config('seo.social', []),
            'analytics' => $analytics,
        ]);
    }

    /**
     * Which tag loader is active, and why. Mirrors the exclusive rule enforced
     * in the layout so the screen cannot disagree with what ships.
     *
     * @return array<string, mixed>
     */
    private function analyticsState(): array
    {
        $gtm = config('services.gtm.id');
        $ga = config('services.ga.id');

        return [
            'gtm_id' => $gtm,
            'ga_id' => $ga,
            'loader' => $gtm ? 'Google Tag Manager' : ($ga ? 'GA4 (gtag.js direct)' : 'None'),
            'reason' => $gtm
                ? 'GTM_ID is set, so GTM is the only loader. GA4 must be configured as a tag inside GTM.'
                : ($ga
                    ? 'GTM_ID is empty, so GA4 loads directly via gtag.js.'
                    : 'Neither GTM_ID nor GA_ID is set — no analytics is loading.'),
        ];
    }
}
