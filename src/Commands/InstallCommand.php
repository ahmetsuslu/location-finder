<?php

namespace Sslah\LocationFinder\Commands;

use Illuminate\Console\Command;

class InstallCommand extends Command
{
    protected $signature = 'location-finder:install';

    protected $description = 'Install Location Finder package - publish configuration files';

    public function handle(): int
    {
        $this->info('🚀 Installing Laravel Location Finder Package...');

        // Publish configuration file
        $this->call('vendor:publish', [
            '--tag' => 'location-finder-config',
            '--force' => true,
        ]);

        $this->info('');
        $this->info('✅ Location Finder package installed successfully!');
        $this->info('');
        $this->info('📁 Configuration published to: config/location-finder.php');
        $this->info('');
        $this->info('🎯 Usage Examples:');
        $this->info('   📍 Facade: LocationFinder::search("ankara")');
        $this->info('   📍 DI: app(GeocodingService::class)->search("ankara")');
        $this->info('   📍 API: GET /api/location-finder/search?query=ankara');
        $this->info('');
        $this->info('📖 Documentation: https://github.com/ahmetsuslu/location-finder');
        
        return self::SUCCESS;
    }
} 