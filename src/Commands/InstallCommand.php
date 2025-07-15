<?php

namespace Sslah\LocationFinder\Commands;

use Illuminate\Console\Command;

class InstallCommand extends Command
{
    protected $signature = 'location-finder:install';
    protected $description = 'Install the Location Finder package';

    public function handle(): int
    {
        $this->info('Installing Location Finder package...');

        $this->publishConfig();

        $this->info('Location Finder package installed successfully!');
        $this->line('');
        $this->line('This package provides API endpoints only:');
        $this->line('- GET /api/location-finder/search?query={query}');
        $this->line('- POST /api/location-finder/geocode');
        $this->line('- POST /api/location-finder/reverse-geocode');
        $this->line('');
        $this->line('Configuration file: config/location-finder.php');
        $this->line('');
        $this->line('Please implement your own frontend UI.');

        return self::SUCCESS;
    }

    protected function publishConfig(): void
    {
        $this->call('vendor:publish', [
            '--tag' => 'location-finder-config',
            '--force' => true,
        ]);
    }


} 