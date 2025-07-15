# 🚀 Laravel Location Finder

**Professional geocoding package for Laravel using OpenStreetMap Nominatim API**

> ✨ **Clean API, consistent field naming (lat/lon), comprehensive error handling, facade support**

[![Latest Version](https://img.shields.io/packagist/v/sslah/location-finder)](https://packagist.org/packages/sslah/location-finder)
[![PHP Version](https://img.shields.io/packagist/php-v/sslah/location-finder)](https://packagist.org/packages/sslah/location-finder)
[![Laravel Version](https://img.shields.io/badge/Laravel-10%2B-red)](https://laravel.com)
[![License](https://img.shields.io/packagist/l/sslah/location-finder)](https://packagist.org/packages/sslah/location-finder)

## ✨ Features

- 🗺️ **OpenStreetMap Integration** - Free, no API key required
- 🎯 **Consistent Field Naming** - Always `lat` and `lon` (no more lng/lon confusion!)
- 🚀 **Laravel Facade Support** - `LocationFinder::search('ankara')`
- 🔄 **Three API Endpoints** - Search, Geocode, Reverse Geocode
- 💾 **Smart Caching** - Configurable caching for better performance
- 🛡️ **Comprehensive Error Handling** - Proper validation and logging
- 🇹🇷 **Turkey Optimized** - Default Turkish language and country settings
- 📦 **Framework Agnostic** - Pure API service, use with any frontend

## 📦 Installation

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

// Geocode address to coordinates
$location = LocationFinder::geocode('Kızılay, Ankara');

// Reverse geocode coordinates to address
$address = LocationFinder::reverseGeocode(39.9208, 32.8541);
```

### 🔧 Using Dependency Injection

```php
use Sslah\LocationFinder\Services\GeocodingService;

class LocationController
{
    public function __construct(private GeocodingService $locationFinder) {}

    public function search(Request $request)
    {
        $results = $this->locationFinder->search($request->query);
        return response()->json($results);
    }
}
```

## 🌐 API Endpoints

### 1️⃣ Search Locations

**Search for locations by text query:**

```http
GET /api/location-finder/search?query=ankara
```

**Response:**

```json
{
    "success": true,
    "query": "ankara",
    "results": [
        {
            "display_name": "Ankara, Çankaya, Ankara, İç Anadolu Bölgesi, 06420, Türkiye",
            "lat": 39.9207759,
            "lon": 32.8540497,
            "class": "place",
            "type": "city",
            "place_id": 298822663,
            "osm_id": 223474,
            "osm_type": "relation",
            "importance": 0.8
        }
    ],
    "count": 1
}
```

### 2️⃣ Geocode Address

**Convert address to coordinates:**

```http
POST /api/location-finder/geocode
Content-Type: application/json

{
    "address": "Kızılay, Ankara"
}
```

**Response:**

```json
{
    "success": true,
    "address": "Kızılay, Ankara",
    "result": {
        "display_name": "Kızılay, Çankaya, Ankara, İç Anadolu Bölgesi, Türkiye",
        "lat": 39.9208,
        "lon": 32.8541,
        "class": "place",
        "type": "neighbourhood"
    }
}
```

### 3️⃣ Reverse Geocode

**Convert coordinates to address:**

```http
POST /api/location-finder/reverse-geocode
Content-Type: application/json

{
    "lat": 39.9208,
    "lon": 32.8541
}
```

**Response:**

```json
{
    "success": true,
    "coordinates": {
        "lat": 39.9208,
        "lon": 32.8541
    },
    "result": {
        "display_name": "Kızılay, Çankaya, Ankara, İç Anadolu Bölgesi, Türkiye",
        "lat": 39.9208,
        "lon": 32.8541,
        "class": "place",
        "type": "neighbourhood"
    }
}
```

## ⚙️ Configuration

Configure the package by editing `config/location-finder.php`:

```php
return [
    'service' => [
        'base_url' => 'https://nominatim.openstreetmap.org',
        'user_agent' => 'Laravel LocationFinder Package',
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
        'ttl' => 3600, // 1 hour
        'prefix' => 'location_finder_',
    ],
];
```

## 🎯 Field Naming Consistency

This package uses **consistent field naming** throughout:

- ✅ **`lat`** - Latitude (always)
- ✅ **`lon`** - Longitude (always)
- ❌ **No more `lng` confusion!**

## 🔥 Frontend Integration Examples

### Vanilla JavaScript

```javascript
// Search
const response = await fetch('/api/location-finder/search?query=ankara');
const data = await response.json();

// Reverse Geocode
const response = await fetch('/api/location-finder/reverse-geocode', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ lat: 39.9208, lon: 32.8541 })
});
```

### Alpine.js

```html
<div x-data="{ query: '', results: [] }">
    <input x-model="query" @input.debounce.300ms="search">
    <div x-show="results.length">
        <template x-for="result in results">
            <div x-text="result.display_name"></div>
        </template>
    </div>
</div>

<script>
function search() {
    if (this.query.length < 3) return;
    
    fetch(`/api/location-finder/search?query=${this.query}`)
        .then(r => r.json())
        .then(data => this.results = data.results);
}
</script>
```

## 🛠️ Error Handling

The package provides comprehensive error handling:

```php
try {
    $results = LocationFinder::search('ankara');
} catch (\Exception $e) {
    Log::error('Location search failed: ' . $e->getMessage());
    // Handle error gracefully
}
```

## 📊 Caching

Built-in caching reduces API calls and improves performance:

- **Default TTL:** 1 hour
- **Cache Key:** MD5 hash of query with prefix
- **Configurable:** Enable/disable via config

## 🔄 Changelog

### v2.0.0 (Latest)
- ✨ **Consistent field naming** (lat/lon everywhere)
- 🛡️ **Enhanced error handling** and validation
- 🚀 **Improved facade support**
- 📝 **Better API documentation**
- 🔧 **Cleaner configuration**

### v1.x.x (Legacy)
- ⚠️ **Deprecated:** Mixed lng/lon field naming
- 🐛 **Issues:** Controller/Service inconsistencies

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## 📄 License

This package is open-sourced software licensed under the [MIT license](LICENSE).

## 🙏 Credits

- **OpenStreetMap** - For providing free geocoding services
- **Nominatim** - For the excellent geocoding API
- **Laravel Community** - For the amazing framework

---

⭐ **Star this repo** if you find it useful! 

🐛 **Found a bug?** [Report it here](https://github.com/ahmetsuslu/location-finder/issues)

📖 **Need help?** Check out the [documentation](https://github.com/ahmetsuslu/location-finder)
