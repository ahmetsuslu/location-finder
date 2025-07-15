<?php

namespace Sslah\LocationFinder\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class LocationFinder extends Component
{
    public string $name;
    public string $placeholder;
    public string $value;
    public int $minChars;
    public int $maxResults;
    public int $debounceMs;
    public bool $required;
    public string $class;
    public ?string $onSelect;
    public ?string $onClear;
    public ?string $onError;

    public function __construct(
        string $name = 'location',
        string $placeholder = '',
        string $value = '',
        int $minChars = 3,
        int $maxResults = 10,
        int $debounceMs = 300,
        bool $required = false,
        string $class = '',
        ?string $onSelect = null,
        ?string $onClear = null,
        ?string $onError = null
    ) {
        $this->name = $name;
        $this->placeholder = $placeholder ?: config('location-finder.ui.placeholder', 'Adres arayın...');
        $this->value = $value;
        $this->minChars = $minChars;
        $this->maxResults = $maxResults;
        $this->debounceMs = $debounceMs;
        $this->required = $required;
        $this->class = $class;
        $this->onSelect = $onSelect;
        $this->onClear = $onClear;
        $this->onError = $onError;
    }

    public function render(): View
    {
        return view('location-finder::components.location-finder', [
            'config' => [
                'ui' => config('location-finder.ui'),
                'search' => config('location-finder.search'),
            ],
        ]);
    }
} 