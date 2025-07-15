<?php

namespace Sslah\LocationFinder\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array search(string $query)
 * @method static array geocode(string $address)
 * @method static array reverseGeocode(float $lat, float $lon)
 * 
 * @see \Sslah\LocationFinder\Services\GeocodingService
 */
class LocationFinder extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'location-finder';
    }
} 