<?php

namespace Sslah\LocationFinder;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;
use Sslah\LocationFinder\Commands\InstallCommand;
use Sslah\LocationFinder\Components\LocationFinder;
use Sslah\LocationFinder\Services\GeocodingService;
use Sslah\LocationFinder\Http\Controllers\LocationFinderController;

class LocationFinderServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Register the main service
        $this->app->singleton(GeocodingService::class, function ($app) {
            return new GeocodingService();
        });

        // Bind for facade access
        $this->app->bind('location-finder', function ($app) {
            return $app->make(GeocodingService::class);
        });

        // Register facade alias
        $loader = \Illuminate\Foundation\AliasLoader::getInstance();
        $loader->alias('LocationFinder', \Sslah\LocationFinder\Facades\LocationFinder::class);
    }

    public function boot(): void
    {
        // Register routes
        $this->registerRoutes();

        // Register Blade components
        Blade::component('location-finder', LocationFinder::class);

        // Register commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallCommand::class,
            ]);
        }

        // Publish configuration file
        $this->publishes([
            __DIR__ . '/../config/location-finder.php' => config_path('location-finder.php'),
        ], 'location-finder-config');

        // Publish views (optional - for API documentation)
        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/location-finder'),
        ], 'location-finder-views');

        // Load views
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'location-finder');

        // Merge configuration
        $this->mergeConfigFrom(__DIR__ . '/../config/location-finder.php', 'location-finder');
    }

    protected function registerRoutes(): void
    {
        Route::group([
            'prefix' => 'api/location-finder',
            'middleware' => ['web'],
        ], function () {
            Route::get('/search', [LocationFinderController::class, 'search']);
            Route::post('/geocode', [LocationFinderController::class, 'geocode']);
            Route::post('/reverse-geocode', [LocationFinderController::class, 'reverseGeocode']);
        });
    }
} 