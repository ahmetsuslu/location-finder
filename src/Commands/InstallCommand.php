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
        $this->publishViews();
        $this->publishAssets();

        $this->info('Location Finder package installed successfully!');
        $this->line('');
        $this->line('You can now use <x-location-finder name="address" /> in your Blade templates.');
        $this->line('');
        $this->line('Configuration file: config/location-finder.php');
        $this->line('View files: resources/views/vendor/location-finder/');
        $this->line('Assets: public/vendor/location-finder/');

        return self::SUCCESS;
    }

    protected function publishConfig(): void
    {
        $this->call('vendor:publish', [
            '--tag' => 'location-finder-config',
            '--force' => true,
        ]);
    }

    protected function publishViews(): void
    {
        $this->call('vendor:publish', [
            '--tag' => 'location-finder-views',
            '--force' => true,
        ]);
    }

    protected function publishAssets(): void
    {
        $this->call('vendor:publish', [
            '--tag' => 'location-finder-assets',
            '--force' => true,
        ]);
    }
} 