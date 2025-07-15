# 🚀 Laravel Location Finder - API Service

**Pure API service for OpenStreetMap/Nominatim address search and geocoding.**

> **Zero UI Dependencies** - Only provides backend API endpoints. Frontend implementation is up to you.

## ✅ Installation

```bash
composer require sslah/location-finder
```

```bash
php artisan location-finder:install
```

## 🚀 Usage

### 🎭 Using Facade (Recommended)
```php
use LocationFinder;

// Search for locations
$results = LocationFinder::search('ankara');

// Geocode an address to get coordinates
$location = LocationFinder::geocode('Kızılay, Ankara');

// Reverse geocode coordinates to get address
$address = LocationFinder::reverseGeocode(39.9208, 32.8541);
```

### 🔧 Using Dependency Injection
```php
use Sslah\LocationFinder\Services\GeocodingService;

class LocationController
{
    public function __construct(private GeocodingService $locationFinder)
    {
    }

    public function search(Request $request)
    {
        $results = $this->locationFinder->search($request->query);
        return response()->json($results);
    }
}
```

## 🎯 API Endpoints

### Search Address
```http
GET /api/location-finder/search?query=istanbul
```

**Response:**
```json
{
    "success": true,
    "results": [
        {
            "address": "İstanbul, Türkiye",
            "lat": 41.0082,
            "lng": 28.9784
        }
    ],
    "count": 1
}
```

### Geocode Address
```http
POST /api/location-finder/geocode
Content-Type: application/json

{
    "address": "İstanbul, Türkiye"
}
```

### Reverse Geocode
```http
POST /api/location-finder/reverse-geocode
Content-Type: application/json

{
    "lat": 41.0082,
    "lng": 28.9784
}
```

## 🔧 Configuration

```php
// config/location-finder.php
return [
    'service' => [
        'provider' => 'nominatim',
        'base_url' => 'https://nominatim.openstreetmap.org',
        'user_agent' => 'Laravel Location Finder',
        'timeout' => 10,
    ],
    'search' => [
        'min_chars' => 3,
        'max_results' => 10,
        'country_code' => 'tr',
        'language' => 'tr',
    ],
    'cache' => [
        'enabled' => true,
        'ttl' => 3600,
    ],
];
```

## 💡 Frontend Implementation Examples

### Vanilla JavaScript
```html
<input type="text" id="address-search" placeholder="Search address...">
<div id="results"></div>

<script>
document.getElementById('address-search').addEventListener('input', function(e) {
    const query = e.target.value;
    
    if (query.length < 3) return;
    
    fetch(`/api/location-finder/search?query=${encodeURIComponent(query)}`)
        .then(response => response.json())
        .then(data => {
            const resultsDiv = document.getElementById('results');
            resultsDiv.innerHTML = '';
            
            data.results.forEach(result => {
                const div = document.createElement('div');
                div.innerHTML = `
                    <strong>${result.address}</strong><br>
                    <small>Lat: ${result.lat}, Lng: ${result.lng}</small>
                `;
                div.addEventListener('click', () => {
                    console.log('Selected:', result);
                });
                resultsDiv.appendChild(div);
            });
        });
});
</script>
```

### jQuery
```javascript
$('#address-search').on('input', function() {
    const query = $(this).val();
    
    if (query.length < 3) return;
    
    $.get('/api/location-finder/search', { query: query })
        .done(function(data) {
            const $results = $('#results').empty();
            
            data.results.forEach(function(result) {
                $results.append(`
                    <div class="result-item" data-lat="${result.lat}" data-lng="${result.lng}">
                        <strong>${result.address}</strong><br>
                        <small>Lat: ${result.lat}, Lng: ${result.lng}</small>
                    </div>
                `);
            });
        });
});
```

### Vue.js
```vue
<template>
    <div>
        <input v-model="query" @input="search" placeholder="Search address...">
        <div v-for="result in results" :key="result.address" @click="selectResult(result)">
            <strong>{{ result.address }}</strong><br>
            <small>Lat: {{ result.lat }}, Lng: {{ result.lng }}</small>
        </div>
    </div>
</template>

<script>
export default {
    data() {
        return {
            query: '',
            results: []
        }
    },
    methods: {
        search() {
            if (this.query.length < 3) return;
            
            fetch(`/api/location-finder/search?query=${encodeURIComponent(this.query)}`)
                .then(response => response.json())
                .then(data => {
                    this.results = data.results;
                });
        },
        selectResult(result) {
            console.log('Selected:', result);
        }
    }
}
</script>
```

### React
```jsx
import React, { useState } from 'react';

function AddressSearch() {
    const [query, setQuery] = useState('');
    const [results, setResults] = useState([]);

    const handleSearch = async (e) => {
        const value = e.target.value;
        setQuery(value);
        
        if (value.length < 3) return;
        
        const response = await fetch(`/api/location-finder/search?query=${encodeURIComponent(value)}`);
        const data = await response.json();
        setResults(data.results);
    };

    return (
        <div>
            <input 
                value={query}
                onChange={handleSearch}
                placeholder="Search address..."
            />
            <div>
                {results.map((result) => (
                    <div key={result.address} onClick={() => console.log('Selected:', result)}>
                        <strong>{result.address}</strong><br/>
                        <small>Lat: {result.lat}, Lng: {result.lng}</small>
                    </div>
                ))}
            </div>
        </div>
    );
}
```

## 🌍 OpenStreetMap Integration

This package uses OpenStreetMap's Nominatim API:
- **Free** - No API keys required
- **Global** - Worldwide address coverage
- **Accurate** - High-quality geocoding data
- **No limits** - Reasonable usage is free

## 📊 Response Format

All endpoints return standardized JSON responses:

```json
{
    "success": true,
    "results": [
        {
            "address": "Full formatted address",
            "lat": 41.0082,
            "lng": 28.9784
        }
    ],
    "count": 1
}
```

Error responses:
```json
{
    "success": false,
    "error": "Error message",
    "results": [],
    "count": 0
}
```

## 🔧 Advanced Usage

### Custom Service Provider

```php
// In your AppServiceProvider
use Sslah\LocationFinder\Services\GeocodingService;

public function boot()
{
    $this->app->bind(GeocodingService::class, function ($app) {
        return new GeocodingService([
            'country_code' => 'us',
            'language' => 'en',
            'max_results' => 5,
        ]);
    });
}
```

### Direct Service Usage

```php
use Sslah\LocationFinder\Services\GeocodingService;

class MyController extends Controller
{
    public function search(Request $request, GeocodingService $geocoding)
    {
        $results = $geocoding->search($request->input('query'));
        
        return response()->json($results);
    }
}
```

## 🎯 Why This Package?

### ✅ **Pure API Service**
- No frontend dependencies
- Framework agnostic
- Use with any CSS framework
- Implement your own UI

### ✅ **OpenStreetMap Powered**
- Free and open source
- No API keys required
- Global coverage
- High accuracy

### ✅ **Laravel Integration**
- Service provider included
- Configuration file
- Caching support
- Error handling

### ✅ **Developer Friendly**
- Clean API endpoints
- Standardized responses
- Easy to integrate
- Well documented

## 📝 License

MIT License

## 🔗 Links

- GitHub: https://github.com/ahmetsuslu/location-finder
- Packagist: https://packagist.org/packages/sslah/location-finder
- OpenStreetMap: https://www.openstreetmap.org/
- Nominatim API: https://nominatim.org/
