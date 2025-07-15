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
        $this->config = config('location-finder');
    }

    public function search(string $query): array
    {
        if (strlen($query) < $this->config['search']['min_chars']) {
            return [];
        }

        $cacheKey = $this->getCacheKey($query);

        if ($this->config['cache']['enabled']) {
            $cached = Cache::get($cacheKey);
            if ($cached !== null) {
                return $cached;
            }
        }

        try {
            $response = $this->makeRequest($query);
            $results = $this->formatResults($response);

            if ($this->config['cache']['enabled']) {
                Cache::put($cacheKey, $results, $this->config['cache']['ttl']);
            }

            return $results;
        } catch (\Exception $e) {
            Log::error('Location Finder API Error: ' . $e->getMessage());
            return [];
        }
    }

    protected function makeRequest(string $query): array
    {
        $response = Http::timeout($this->config['service']['timeout'])
            ->withHeaders([
                'User-Agent' => $this->config['service']['user_agent'],
            ])
            ->get($this->config['service']['base_url'] . '/search', [
                'q' => $query,
                'format' => 'json',
                'limit' => $this->config['search']['max_results'],
                'countrycodes' => $this->config['search']['country_code'],
                'accept-language' => $this->config['search']['language'],
                'addressdetails' => 1,
                'extratags' => 0,
                'namedetails' => 0,
            ]);

        if (!$response->successful()) {
            throw new \Exception('Geocoding API request failed: ' . $response->status());
        }

        return $response->json() ?? [];
    }

    protected function formatResults(array $response): array
    {
        $results = [];
        $format = $this->config['response']['format'];

        foreach ($response as $item) {
            $formatted = [
                'display_name' => $item[$format['address']] ?? $item['display_name'] ?? '',
                'lat' => (float) ($item[$format['lat']] ?? $item['lat'] ?? 0),
                'lon' => (float) ($item[$format['lng']] ?? $item['lon'] ?? 0),
                'class' => $item['class'] ?? null,
                'type' => $item['type'] ?? null,
            ];

            if ($this->config['response']['include_raw']) {
                $formatted['raw'] = $item;
            }

            $results[] = $formatted;
        }

        return $results;
    }

    protected function getCacheKey(string $query): string
    {
        return $this->config['cache']['prefix'] . md5($query);
    }

    public function geocode(string $address): ?array
    {
        $results = $this->search($address);
        return $results[0] ?? null;
    }

    public function reverseGeocode(float $lat, float $lng): ?array
    {
        $cacheKey = $this->getCacheKey("reverse_{$lat}_{$lng}");

        if ($this->config['cache']['enabled']) {
            $cached = Cache::get($cacheKey);
            if ($cached !== null) {
                return $cached;
            }
        }

        try {
            $response = Http::timeout($this->config['service']['timeout'])
                ->withHeaders([
                    'User-Agent' => $this->config['service']['user_agent'],
                ])
                ->get($this->config['service']['base_url'] . '/reverse', [
                    'lat' => $lat,
                    'lon' => $lng,
                    'format' => 'json',
                    'accept-language' => $this->config['search']['language'],
                    'addressdetails' => 1,
                ]);

            if (!$response->successful()) {
                throw new \Exception('Reverse geocoding API request failed: ' . $response->status());
            }

            $data = $response->json();
            $result = [
                'display_name' => $data['display_name'] ?? '',
                'lat' => (float) ($data['lat'] ?? 0),
                'lon' => (float) ($data['lon'] ?? 0),
                'class' => $data['class'] ?? null,
                'type' => $data['type'] ?? null,
            ];

            if ($this->config['cache']['enabled']) {
                Cache::put($cacheKey, $result, $this->config['cache']['ttl']);
            }

            return $result;
        } catch (\Exception $e) {
            Log::error('Reverse Location Finder API Error: ' . $e->getMessage());
            return null;
        }
    }
} 