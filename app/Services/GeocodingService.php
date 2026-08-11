<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Turns a street address into coordinates.
 *
 * Uses Google Geocoding when GOOGLE_MAPS_API_KEY is configured, and falls back
 * to Photon (OpenStreetMap) — the same free service the front-end autocomplete
 * uses — so delivery checks keep working without a key or during an outage.
 */
class GeocodingService
{
    /**
     * Geocode a US address.
     *
     * @return array{latitude: float, longitude: float, formatted: string, provider: string}|null
     */
    public function geocode(string $address, ?string $zip = null, ?string $city = null, ?string $state = null): ?array
    {
        $query = trim(implode(', ', array_filter([
            trim($address),
            $city ? trim($city) : null,
            $state ? trim($state) : null,
            $zip ? trim($zip) : null,
        ])));

        if ($query === '') {
            return null;
        }

        $cacheKey = 'geocode:' . md5(strtolower($query));

        return Cache::remember($cacheKey, now()->addDays(30), function () use ($query) {
            return $this->geocodeWithGoogle($query) ?? $this->geocodeWithPhoton($query);
        });
    }

    private function geocodeWithGoogle(string $query): ?array
    {
        $key = config('services.google.maps_api_key');

        if (!$key) {
            return null;
        }

        try {
            $response = Http::timeout(6)->get('https://maps.googleapis.com/maps/api/geocode/json', [
                'address' => $query,
                'components' => 'country:US',
                'key' => $key,
            ]);

            if (!$response->successful()) {
                return null;
            }

            $data = $response->json();

            if (($data['status'] ?? '') !== 'OK' || empty($data['results'][0])) {
                return null;
            }

            $result = $data['results'][0];
            $location = $result['geometry']['location'] ?? null;

            if (!isset($location['lat'], $location['lng'])) {
                return null;
            }

            return [
                'latitude' => (float) $location['lat'],
                'longitude' => (float) $location['lng'],
                'formatted' => (string) ($result['formatted_address'] ?? $query),
                'provider' => 'google',
            ];
        } catch (\Throwable $e) {
            Log::warning('Google geocoding failed', ['query' => $query, 'error' => $e->getMessage()]);

            return null;
        }
    }

    private function geocodeWithPhoton(string $query): ?array
    {
        try {
            $response = Http::timeout(6)->get('https://photon.komoot.io/api/', [
                'q' => $query,
                'limit' => 1,
                'lang' => 'en',
            ]);

            if (!$response->successful()) {
                return null;
            }

            $feature = $response->json('features.0');
            $coordinates = $feature['geometry']['coordinates'] ?? null;

            // Photon returns [longitude, latitude]
            if (!is_array($coordinates) || count($coordinates) < 2) {
                return null;
            }

            return [
                'latitude' => (float) $coordinates[1],
                'longitude' => (float) $coordinates[0],
                'formatted' => (string) ($feature['properties']['name'] ?? $query),
                'provider' => 'photon',
            ];
        } catch (\Throwable $e) {
            Log::warning('Photon geocoding failed', ['query' => $query, 'error' => $e->getMessage()]);

            return null;
        }
    }
}
