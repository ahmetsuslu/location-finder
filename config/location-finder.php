<?php

return [

    /*
    |--------------------------------------------------------------------------
    | OpenStreetMap Nominatim Service Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the OpenStreetMap Nominatim geocoding service.
    | All requests use consistent lat/lon field naming.
    |
    */

    'service' => [
        'base_url' => env('LOCATION_FINDER_BASE_URL', 'https://nominatim.openstreetmap.org'),
        'user_agent' => env('LOCATION_FINDER_USER_AGENT', 'Laravel LocationFinder Package'),
        'timeout' => env('LOCATION_FINDER_TIMEOUT', 10),
    ],

    /*
    |--------------------------------------------------------------------------
    | Search Configuration
    |--------------------------------------------------------------------------
    |
    | Configure search behavior and constraints.
    |
    */

    'search' => [
        'min_chars' => 3,           // Minimum characters required for search
        'max_results' => 10,        // Maximum number of results to return
        'country_code' => 'tr',     // Default country code (Turkey)
        'language' => 'tr',         // Default language for results
    ],

    /*
    |--------------------------------------------------------------------------
    | Caching Configuration
    |--------------------------------------------------------------------------
    |
    | Configure caching for search results to improve performance
    | and reduce API calls to OpenStreetMap.
    |
    */

    'cache' => [
        'enabled' => env('LOCATION_FINDER_CACHE_ENABLED', true),
        'ttl' => env('LOCATION_FINDER_CACHE_TTL', 3600), // Cache TTL in seconds (1 hour)
        'prefix' => 'location_finder_',
    ],

]; 