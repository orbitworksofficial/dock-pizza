<?php

declare(strict_types=1);

namespace App\Services\Seo;

/**
 * Builds the single site-wide @graph emitted on every page.
 *
 * Organization, WebSite and ProfessionalService are cross-referenced by @id so
 * Google reads them as one entity rather than three unrelated blocks.
 */
class SchemaGraphBuilder
{
    public function __construct(private readonly SocialUrlNormalizer $social)
    {
    }

    /**
     * @param  array<int, array{question: string, answer: string}>  $faqs
     * @return array<string, mixed>
     */
    public function build(array $page, array $faqs = []): array
    {
        $graph = [
            $this->organization(),
            $this->website(),
            $this->professionalService(),
        ];

        // A page must never declare its FAQs twice: a hand-written faq_schema
        // replaces the generated block rather than joining it.
        if (!empty($page['faq_schema'])) {
            $graph[] = $page['faq_schema'];
        } elseif ($faqs !== []) {
            $graph[] = $this->faqPage($faqs);
        }

        if (!empty($page['schema_markup'])) {
            $custom = $page['schema_markup'];
            // Accept either a single node or a list of them.
            $graph = array_merge($graph, array_is_list($custom) ? $custom : [$custom]);
        }

        return [
            '@context' => 'https://schema.org',
            '@graph' => $graph,
        ];
    }

    private function organizationId(): string
    {
        return url('/') . '/#organization';
    }

    /**
     * @return array<string, mixed>
     */
    private function organization(): array
    {
        $org = config('seo.organization');

        return array_filter([
            '@type' => 'Organization',
            '@id' => $this->organizationId(),
            'name' => $org['name'],
            'legalName' => $org['legal_name'] ?? null,
            'url' => url('/'),
            // A real image file — a homepage URL here fails validation.
            'logo' => [
                '@type' => 'ImageObject',
                'url' => $this->absolute($org['logo']),
            ],
            'description' => $org['description'],
            'email' => $org['email'],
            'telephone' => $org['telephone'],
            'address' => $this->postalAddress(),
            'sameAs' => $this->sameAs(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function website(): array
    {
        return [
            '@type' => 'WebSite',
            '@id' => url('/') . '/#website',
            'url' => url('/'),
            'name' => config('seo.organization.name'),
            'description' => config('seo.organization.description'),
            'publisher' => ['@id' => $this->organizationId()],
        ];
    }

    /**
     * The NAP is repeated here deliberately — validators match on the shape of
     * the node, not on the type name, so ProfessionalService needs its own copy.
     *
     * @return array<string, mixed>
     */
    private function professionalService(): array
    {
        $org = config('seo.organization');

        return array_filter([
            '@type' => 'ProfessionalService',
            '@id' => url('/') . '/#business',
            'name' => $org['name'],
            'url' => url('/'),
            'image' => $this->absolute($org['logo']),
            'description' => $org['description'],
            'email' => $org['email'],
            'telephone' => $org['telephone'],
            'address' => $this->postalAddress(),
            'geo' => [
                '@type' => 'GeoCoordinates',
                'latitude' => $org['geo']['latitude'],
                'longitude' => $org['geo']['longitude'],
            ],
            'priceRange' => $org['price_range'],
            'openingHoursSpecification' => $this->openingHours(),
            'hasOfferCatalog' => $this->offerCatalog(),
            'parentOrganization' => ['@id' => $this->organizationId()],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function postalAddress(): array
    {
        $address = config('seo.organization.address');

        return [
            '@type' => 'PostalAddress',
            'streetAddress' => $address['street'],
            'addressLocality' => $address['locality'],
            'addressRegion' => $address['region'],
            'postalCode' => $address['postal_code'],
            'addressCountry' => $address['country'],
        ];
    }

    /**
     * Canonical profile URLs only — sameAs matching is URL-exact.
     *
     * @return array<int, string>
     */
    private function sameAs(): array
    {
        return array_values($this->social->normalizeMany(config('seo.social', [])));
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function openingHours(): array
    {
        return array_map(fn (array $slot) => [
            '@type' => 'OpeningHoursSpecification',
            'dayOfWeek' => $slot['days'],
            'opens' => $slot['opens'],
            'closes' => $slot['closes'],
        ], config('seo.opening_hours', []));
    }

    /**
     * @return array<string, mixed>
     */
    private function offerCatalog(): array
    {
        $services = ['Pizza Delivery', 'Pizza Pickup', 'Event Catering'];

        return [
            '@type' => 'OfferCatalog',
            'name' => config('seo.organization.name') . ' Services',
            'itemListElement' => array_map(fn (string $name) => [
                '@type' => 'Offer',
                'itemOffered' => ['@type' => 'Service', 'name' => $name],
            ], $services),
        ];
    }

    /**
     * Generated from the same rows the page renders, which is the only form
     * Google credits.
     *
     * @param  array<int, array{question: string, answer: string}>  $faqs
     * @return array<string, mixed>
     */
    private function faqPage(array $faqs): array
    {
        return [
            '@type' => 'FAQPage',
            'mainEntity' => array_map(fn (array $faq) => [
                '@type' => 'Question',
                'name' => $faq['question'],
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => $faq['answer'],
                ],
            ], $faqs),
        ];
    }

    private function absolute(string $path): string
    {
        return preg_match('#^https?://#i', $path) ? $path : url($path);
    }
}
