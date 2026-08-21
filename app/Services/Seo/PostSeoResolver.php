<?php

declare(strict_types=1);

namespace App\Services\Seo;

use App\Models\BlogPost;

/**
 * Resolves the SEO payload for a blog post.
 *
 * Same precedence rule as page SEO: the post's own SEO field, then a sensible
 * derivation from the post itself, then the site-wide config default. A blank
 * or whitespace-only field is treated as "not set", never as an override, so
 * a stray space can't produce an empty <title>.
 */
class PostSeoResolver
{
    public function __construct(private readonly JsonLdNormalizer $jsonLd)
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function resolve(BlogPost $post): array
    {
        $defaults = config('seo.defaults');

        $title = $this->pick($post->seo_title, $post->title, $defaults['title']);
        $description = $this->pick(
            $post->seo_description,
            $post->excerpt,
            $this->excerptFromContent($post->content),
            $defaults['description']
        );

        $image = $this->absolute($this->pick($post->og_image, $post->featured_image, $defaults['og_image']));

        return [
            'page_key' => '/blog/' . $post->slug,
            'title' => $title,
            'description' => $description,
            'keywords' => $this->pick($post->seo_keywords, $post->tags->pluck('name')->implode(', '), $defaults['keywords']),
            'canonical' => $this->pick($post->canonical_url, null, $post->url),
            // A post that is not live must never be indexed, whatever the field says.
            'robots' => $post->isLive()
                ? $this->pick($post->robots, null, $defaults['robots'])
                : 'noindex, nofollow',

            'og_title' => $this->pick($post->og_title, null, $title),
            'og_description' => $this->pick($post->og_description, null, $description),
            'og_image' => $image,
            'og_type' => $this->pick($post->og_type, null, 'article'),

            'twitter_title' => $this->pick($post->twitter_title, $post->og_title, $title),
            'twitter_description' => $this->pick($post->twitter_description, $post->og_description, $description),
            'twitter_image' => $this->absolute($this->pick($post->twitter_image, $post->og_image, $image)),
            'twitter_card' => $this->pick($post->twitter_card, null, $defaults['twitter_card']),

            'schema_markup' => $this->decode($post->schema_markup),
            'faq_schema' => $this->decode($post->faq_schema),
            'faqs' => $post->relationLoaded('faqs')
                ? $post->faqs->map(fn ($f) => [
                    'question' => (string) $f->question,
                    'answer' => (string) $f->answer,
                ])->values()->all()
                : [],
        ];
    }

    /**
     * The BlogPosting node for this post, added to the site-wide @graph.
     *
     * @return array<string, mixed>
     */
    public function articleSchema(BlogPost $post, array $seo): array
    {
        $orgId = url('/') . '/#organization';

        return array_filter([
            '@type' => 'BlogPosting',
            '@id' => $post->url . '#article',
            'headline' => $post->title,
            'description' => $seo['description'],
            'image' => $seo['og_image'] ?: null,
            'datePublished' => $post->published_at?->toAtomString(),
            'dateModified' => $post->updated_at?->toAtomString(),
            'author' => $post->author ? [
                '@type' => 'Person',
                'name' => $post->author->name,
            ] : null,
            'publisher' => ['@id' => $orgId],
            'mainEntityOfPage' => [
                '@type' => 'WebPage',
                '@id' => $post->url,
            ],
            'articleSection' => $post->category?->name,
            'keywords' => $seo['keywords'] ?: null,
            'wordCount' => str_word_count(strip_tags((string) $post->content)) ?: null,
        ]);
    }

    private function excerptFromContent(?string $html): string
    {
        $text = trim(preg_replace('/\s+/', ' ', strip_tags((string) $html)) ?? '');

        return $text === '' ? '' : rtrim(mb_substr($text, 0, 155), " \t\n\r.,;:") . '…';
    }

    private function pick(?string ...$candidates): string
    {
        foreach ($candidates as $c) {
            if ($c !== null && trim($c) !== '') {
                return trim($c);
            }
        }

        return '';
    }

    /**
     * @return array<mixed>
     */
    private function decode(?string $raw): array
    {
        if ($raw === null || trim($raw) === '') {
            return [];
        }

        $result = $this->jsonLd->normalize($raw);

        return $result['ok'] ? $result['data'] : [];
    }

    private function absolute(string $path): string
    {
        if ($path === '' || preg_match('#^https?://#i', $path)) {
            return $path;
        }

        return url($path);
    }
}
