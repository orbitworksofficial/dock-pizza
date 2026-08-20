<?php

declare(strict_types=1);

namespace App\Services\Seo;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Canonicalises social profile URLs for sameAs.
 *
 * Google matches sameAs entries by exact URL, so a link copied out of a phone
 * app — carrying ?igsh=, ?si=, or pointing at a share redirect — will not be
 * recognised as the same profile.
 */
class SocialUrlNormalizer
{
    /**
     * Query parameters apps append when a link is shared.
     */
    private const TRACKING_PARAMS = [
        'igsh', 'igshid', 'si', 'is_from_webapp', 'sender_device', 'sender_web_id',
        'mibextid', 'rdid', 'share_url', 'fbclid', 'gclid', 'utm_source',
        'utm_medium', 'utm_campaign', 'utm_term', 'utm_content', '_rdr',
    ];

    /**
     * Hosts that serve short share links rather than the profile itself.
     */
    private const REDIRECT_HOSTS = [
        'l.instagram.com', 'lm.facebook.com', 'l.facebook.com',
        'vm.tiktok.com', 'vt.tiktok.com', 'youtu.be', 'fb.me', 'bit.ly', 't.co',
    ];

    /**
     * Clean a single profile URL. Network resolution is opt-in so validation
     * stays fast; the admin save path enables it.
     */
    public function normalize(?string $url, bool $resolveRedirects = false): ?string
    {
        $url = trim((string) $url);

        if ($url === '') {
            return null;
        }

        if (!preg_match('#^https?://#i', $url)) {
            $url = 'https://' . ltrim($url, '/');
        }

        $parts = parse_url($url);

        if (!$parts || empty($parts['host'])) {
            return null;
        }

        if ($resolveRedirects && $this->isRedirectHost($parts['host'])) {
            $resolved = $this->followRedirect($url);
            if ($resolved) {
                $parts = parse_url($resolved) ?: $parts;
            }
        }

        return $this->rebuild($parts);
    }

    /**
     * @param  array<string, string|null>  $input
     * @return array<string, string>
     */
    public function normalizeMany(array $input, bool $resolveRedirects = false): array
    {
        $out = [];

        foreach ($input as $key => $url) {
            $clean = $this->normalize($url, $resolveRedirects);
            if ($clean !== null) {
                $out[$key] = $clean;
            }
        }

        return $out;
    }

    private function isRedirectHost(string $host): bool
    {
        $host = strtolower(ltrim($host, 'www.'));

        return in_array($host, self::REDIRECT_HOSTS, true);
    }

    /**
     * Ask the share host where the link actually points.
     */
    private function followRedirect(string $url): ?string
    {
        try {
            $response = Http::timeout(6)
                ->withoutRedirecting()
                ->head($url);

            $location = $response->header('Location');

            if ($location) {
                // One hop is enough for share links; more risks a redirect loop.
                return $location;
            }
        } catch (\Throwable $e) {
            Log::info('Could not resolve social share link', ['url' => $url, 'error' => $e->getMessage()]);
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $parts
     */
    private function rebuild(array $parts): string
    {
        $host = strtolower((string) ($parts['host'] ?? ''));
        $path = rtrim((string) ($parts['path'] ?? ''), '/');

        $query = '';
        if (!empty($parts['query'])) {
            parse_str((string) $parts['query'], $params);

            foreach (array_keys($params) as $name) {
                if (in_array(strtolower((string) $name), self::TRACKING_PARAMS, true)) {
                    unset($params[$name]);
                }
            }

            if ($params !== []) {
                $query = '?' . http_build_query($params);
            }
        }

        // Fragments never identify a profile.
        return 'https://' . $host . $path . $query;
    }
}
