<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Geocoding Service Configuration
    |--------------------------------------------------------------------------
    |
    | This package uses OpenStreetMap Nominatim service for geocoding.
    | It's free and doesn't require API keys.
    |
    */

    'service' => [
        'provider' => 'nominatim',
        'base_url' => 'https://nominatim.openstreetmap.org',
        'user_agent' => 'LocationFinder/1.0 (Laravel Package)',
        'timeout' => 10,
        'rate_limit' => 1, // seconds between requests
    ],

    /*
    |--------------------------------------------------------------------------
    | Search Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the search behavior and constraints.
    |
    */

    'search' => [
        'min_chars' => 3,
        'max_results' => 10,
        'country_code' => 'tr', // Turkey only
        'language' => 'tr',
        'debounce_ms' => 300,
    ],

    /*
    |--------------------------------------------------------------------------
    | UI Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the appearance and behavior of the location finder component.
    |
    */

    'ui' => [
        'dropdown_max_height' => '200px',
        'placeholder' => 'Adres arayın...',
        'no_results_text' => 'Sonuç bulunamadı',
        'loading_text' => 'Aranıyor...',
        'error_text' => 'Arama sırasında hata oluştu',
        'keyboard_navigation' => true,
        'auto_close_on_select' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Response Format
    |--------------------------------------------------------------------------
    |
    | Configure the format of the returned location data.
    |
    */

    'response' => [
        'format' => [
            'address' => 'display_name',
            'lat' => 'lat',
            'lon' => 'lon',
        ],
        'include_raw' => false, // Include raw Nominatim response
    ],

    /*
    |--------------------------------------------------------------------------
    | Caching Configuration
    |--------------------------------------------------------------------------
    |
    | Configure caching for search results to improve performance.
    |
    */

    'cache' => [
        'enabled' => true,
        'ttl' => 3600, // 1 hour
        'prefix' => 'location_finder_',
    ],

]; 