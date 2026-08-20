<?php

declare(strict_types=1);

namespace App\Services\Seo;

use App\Models\SeoMeta;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Resolves the SEO payload for the current page.
 *
 * Order of precedence is database, then config/seo.php. There is deliberately
 * no cache layer: the row is read once per request, so a dashboard save is
 * live on the very next request with no purge or deploy.
 */
class SeoManager
{
    private ?SeoMeta $row = null;
    private bool $loaded = false;

    public function __construct(private readonly JsonLdNormalizer $jsonLd)
    {
    }

    /**
     * The full resolved payload for a route.
     *
     * @return array<string, mixed>
     */
    public function forPage(string $pageKey, ?SeoMeta $override = null): array
    {
        $row = $override ?? $this->row($pageKey);
        $defaults = config('seo.defaults');
        $page = config('seo.pages.' . $pageKey, []);

        $title = $this->pick($row?->meta_title, $page['title'] ?? null, $defaults['title']);
        $description = $this->pick($row?->meta_description, $page['description'] ?? null, $defaults['description']);

        return [
            'page_key' => $pageKey,
            'title' => $title,
            'description' => $description,
            'keywords' => $this->pick($row?->meta_keywords, $page['keywords'] ?? null, $defaults['keywords']),
            'canonical' => $this->pick($row?->canonical_url, null, url($pageKey === '/' ? '/' : $pageKey)),
            'robots' => $this->pick($row?->robots, $page['robots'] ?? null, $defaults['robots']),

            // Social fields inherit from the search listing when unset, which
            // is what an editor means by leaving them blank.
            'og_title' => $this->pick($row?->og_title, null, $title),
            'og_description' => $this->pick($row?->og_description, null, $description),
            'og_image' => $this->absoluteUrl($this->pick($row?->og_image, null, $defaults['og_image'])),
            'og_type' => $this->pick($row?->og_type, null, $defaults['og_type']),

            'twitter_title' => $this->pick($row?->twitter_title, $row?->og_title, $title),
            'twitter_description' => $this->pick($row?->twitter_description, $row?->og_description, $description),
            'twitter_image' => $this->absoluteUrl($this->pick($row?->twitter_image, $row?->og_image, $defaults['og_image'])),
            'twitter_card' => $this->pick($row?->twitter_card, null, $defaults['twitter_card']),

            'schema_markup' => $this->decodeSchema($row?->schema_markup),
            'faq_schema' => $this->decodeSchema($row?->faq_schema),
            'faqs' => $this->faqs($row),
        ];
    }

    /**
     * The SEO row for a route, or null. Never throws — a database that is
     * down must degrade to config, not to a blank page.
     */
    public function row(string $pageKey): ?SeoMeta
    {
        if ($this->loaded) {
            return $this->row;
        }

        $this->loaded = true;

        try {
            $this->row = SeoMeta::with(['faqs' => function ($q) {
                $q->where('is_active', true)->orderBy('sort_order')->orderBy('id');
            }])->where('page_key', $pageKey)->first();
        } catch (Throwable $e) {
            Log::warning('SEO lookup failed; falling back to config', [
                'page_key' => $pageKey,
                'error' => $e->getMessage(),
            ]);
            $this->row = null;
        }

        return $this->row;
    }

    /**
     * Visible FAQ rows, used to render the page *and* generate its JSON-LD so
     * the two can never disagree.
     *
     * @return array<int, array{question: string, answer: string}>
     */
    private function faqs(?SeoMeta $row): array
    {
        if (!$row || !$row->relationLoaded('faqs')) {
            return [];
        }

        return $row->faqs
            ->map(fn ($faq) => [
                'question' => (string) $faq->question,
                'answer' => (string) $faq->answer,
            ])
            ->values()
            ->all();
    }

    /**
     * First non-blank candidate. Whitespace-only counts as "not set", so an
     * accidental space never overrides a good fallback.
     */
    private function pick(?string ...$candidates): string
    {
        foreach ($candidates as $candidate) {
            if ($candidate !== null && trim($candidate) !== '') {
                return trim($candidate);
            }
        }

        return '';
    }

    /**
     * @return array<mixed>
     */
    private function decodeSchema(?string $raw): array
    {
        if ($raw === null || trim($raw) === '') {
            return [];
        }

        $result = $this->jsonLd->normalize($raw);

        // Stored values were validated on save; if one is somehow broken now,
        // drop it rather than letting raw text reach the page.
        return $result['ok'] ? $result['data'] : [];
    }

    private function absoluteUrl(string $path): string
    {
        if ($path === '' || preg_match('#^https?://#i', $path)) {
            return $path;
        }

        return url($path);
    }
}
