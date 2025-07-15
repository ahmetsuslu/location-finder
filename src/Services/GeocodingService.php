<?php

namespace Sslah\LocationFinder\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class GeocodingService
{
    protected array $config;

    public function __construct()
    {
        $this->config = config('location-finder', []);
    }

    /**
     * Search for locations by query string
     */
    public function search(string $query): array
    {
        if (strlen($query) < ($this->config['search']['min_chars'] ?? 3)) {
            return [];
        }

        $cacheKey = $this->getCacheKey($query);

        // Check cache first
        if ($this->config['cache']['enabled'] ?? true) {
            $cached = Cache::get($cacheKey);
            if ($cached !== null) {
                return $cached;
            }
        }

        try {
            $response = $this->makeSearchRequest($query);
            $results = $this->formatSearchResults($response);

            // Cache results
            if ($this->config['cache']['enabled'] ?? true) {
                Cache::put($cacheKey, $results, $this->config['cache']['ttl'] ?? 3600);
            }

            return $results;
        } catch (\Exception $e) {
            Log::error('Location Finder Search Error: ' . $e->getMessage(), [
                'query' => $query,
                'exception' => $e
            ]);
            return [];
        }
    }

    /**
     * Geocode address to coordinates (returns first result)
     */
    public function geocode(string $address): ?array
    {
        $results = $this->search($address);
        return $results[0] ?? null;
    }

    /**
     * Reverse geocode coordinates to address
     */
    public function reverseGeocode(float $lat, float $lon): ?array
    {
        // Validate coordinates
        if ($lat < -90 || $lat > 90 || $lon < -180 || $lon > 180) {
            Log::warning('Invalid coordinates provided', ['lat' => $lat, 'lon' => $lon]);
            return null;
        }

        $cacheKey = $this->getCacheKey("reverse_{$lat}_{$lon}");

        // Check cache first
        if ($this->config['cache']['enabled'] ?? true) {
            $cached = Cache::get($cacheKey);
            if ($cached !== null) {
                return $cached;
            }
        }

        try {
            $response = $this->makeReverseRequest($lat, $lon);
            $result = $this->formatReverseResult($response);

            // Cache result
            if ($this->config['cache']['enabled'] ?? true && $result !== null) {
                Cache::put($cacheKey, $result, $this->config['cache']['ttl'] ?? 3600);
            }

            return $result;
        } catch (\Exception $e) {
            Log::error('Location Finder Reverse Geocoding Error: ' . $e->getMessage(), [
                'lat' => $lat,
                'lon' => $lon,
                'exception' => $e
            ]);
            return null;
        }
    }

    protected function makeSearchRequest(string $query): array
    {
        $response = Http::timeout($this->config['service']['timeout'] ?? 10)
            ->withHeaders([
                'User-Agent' => $this->config['service']['user_agent'] ?? 'Laravel LocationFinder Package',
            ])
            ->get($this->getBaseUrl() . '/search', [
                'q' => $query,
                'format' => 'json',
                'limit' => $this->config['search']['max_results'] ?? 10,
                'countrycodes' => $this->config['search']['country_code'] ?? 'tr',
                'accept-language' => $this->config['search']['language'] ?? 'tr',
                'addressdetails' => 1,
            ]);

        if (!$response->successful()) {
            throw new \Exception('Search API request failed: HTTP ' . $response->status());
        }

        return $response->json() ?? [];
    }

    protected function makeReverseRequest(float $lat, float $lon): array
    {
        $response = Http::timeout($this->config['service']['timeout'] ?? 10)
            ->withHeaders([
                'User-Agent' => $this->config['service']['user_agent'] ?? 'Laravel LocationFinder Package',
            ])
            ->get($this->getBaseUrl() . '/reverse', [
                'lat' => $lat,
                'lon' => $lon,
                'format' => 'json',
                'accept-language' => $this->config['search']['language'] ?? 'tr',
                'addressdetails' => 1,
            ]);

        if (!$response->successful()) {
            throw new \Exception('Reverse geocoding API request failed: HTTP ' . $response->status());
        }

        return $response->json() ?? [];
    }

    protected function formatSearchResults(array $data): array
    {
        $results = [];

        foreach ($data as $item) {
            $formatted = $this->formatLocationItem($item);
            if ($formatted !== null) {
                $results[] = $formatted;
            }
        }

        return $results;
    }

    protected function formatReverseResult(array $data): ?array
    {
        if (empty($data) || !isset($data['display_name'])) {
            return null;
        }

        return $this->formatLocationItem($data);
    }

    protected function formatLocationItem(array $item): ?array
    {
        // Ensure required fields exist
        if (!isset($item['display_name'], $item['lat'], $item['lon'])) {
            return null;
        }

        return [
            'display_name' => $item['display_name'],
            'lat' => (float) $item['lat'],
            'lon' => (float) $item['lon'],
            'class' => $item['class'] ?? null,
            'type' => $item['type'] ?? null,
            'place_id' => $item['place_id'] ?? null,
            'osm_id' => $item['osm_id'] ?? null,
            'osm_type' => $item['osm_type'] ?? null,
            'importance' => isset($item['importance']) ? (float) $item['importance'] : null,
        ];
    }

    protected function getBaseUrl(): string
    {
        return $this->config['service']['base_url'] ?? 'https://nominatim.openstreetmap.org';
    }

    protected function getCacheKey(string $query): string
    {
        $prefix = $this->config['cache']['prefix'] ?? 'location_finder_';
        return $prefix . md5($query);
    }
} 