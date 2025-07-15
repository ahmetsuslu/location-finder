# 🚀 Sslah/Location-Finder - Laravel Geocoding Autocomplete

> **The Ultimate Laravel Address Autocomplete!** OpenStreetMap/Nominatim powered geocoding with Alpine.js. Perfect for Turkish addresses, NO API keys required!

[![Total Downloads](https://img.shields.io/packagist/dt/sslah/location-finder.svg)](https://packagist.org/packages/sslah/location-finder)
[![Latest Version](https://img.shields.io/packagist/v/sslah/location-finder.svg)](https://packagist.org/packages/sslah/location-finder)
[![License](https://img.shields.io/packagist/l/sslah/location-finder.svg)](https://packagist.org/packages/sslah/location-finder)

---

## 🎯 Why This Package?

### ✅ **NO API Keys Required**
- **Google Maps API** costs money and requires API keys
- **This package** uses free OpenStreetMap/Nominatim service
- **Result**: Zero cost, no limits, no registration

### ✅ **Perfect for Turkish Addresses**
- **Most packages** are designed for US/European addresses
- **This package** is optimized for Turkish geography
- **Result**: Better search results for Turkish locations

### ✅ **Modern Alpine.js Integration**
- **jQuery-based** packages are outdated
- **This package** uses Alpine.js (Laravel's default)
- **Result**: Lightweight, modern, reactive UI

### ✅ **Keyboard Navigation**
- **↑↓ Arrow keys** to navigate results
- **Enter** to select highlighted item
- **Escape** to close dropdown
- **Result**: Perfect user experience

---

## 🏆 Feature Comparison

| Feature | This Package | Google Maps | MapBox | Other Packages |
|---------|-------------|-------------|--------|----------------|
| **No API Key** | ✅ | ❌ | ❌ | ⚠️ |
| **Turkish Focus** | ✅ | ⚠️ | ⚠️ | ❌ |
| **Alpine.js** | ✅ | ❌ | ❌ | ❌ |
| **Keyboard Nav** | ✅ | ⚠️ | ⚠️ | ❌ |
| **Caching** | ✅ | ❌ | ❌ | ❌ |
| **One-Line Install** | ✅ | ❌ | ❌ | ❌ |

---

## 🚀 Lightning-Fast Installation

```bash
# Install package (30 seconds)
composer require sslah/location-finder

# Setup everything automatically
php artisan location-finder:install
```

**That's it!** No API keys, no complex setup, no configuration needed.

---

## 💎 Usage Examples

### 🎯 **Basic Usage**

```blade
<x-location-finder name="address" placeholder="Adres arayın..." />
```

### 🎨 **Advanced Usage**

```blade
<x-location-finder 
    name="location"
    placeholder="Konum seçin..."
    :min-chars="2"
    :max-results="5"
    :debounce-ms="500"
    :required="true"
    class="custom-input-class"
    on-select="handleLocationSelect"
    on-clear="handleLocationClear"
    on-error="handleLocationError"
/>
```

### 🔧 **JavaScript Callbacks**

```javascript
// Handle location selection
function handleLocationSelect(result) {
    console.log('Selected location:', result);
    // result = { address: "...", lat: 41.0082, lng: 28.9784 }
}

// Handle input clear
function handleLocationClear() {
    console.log('Location cleared');
}

// Handle search errors
function handleLocationError(error) {
    console.error('Search error:', error);
}
```

### 📝 **Form Integration**

```blade
<form method="POST" action="/save-location">
    @csrf
    
    <div class="space-y-4">
        <div>
            <label class="block text-sm font-medium text-gray-700">
                Adres
            </label>
            <x-location-finder 
                name="address" 
                placeholder="Adres arayın..."
                :required="true"
            />
        </div>
        
        <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded">
            Kaydet
        </button>
    </div>
</form>
```

**Form data includes:**
- `address` → Selected address string
- `address_lat` → Latitude coordinate
- `address_lng` → Longitude coordinate

---

## 🔧 Configuration Options

Configuration file at `config/location-finder.php`:

```php
return [
    'service' => [
        'provider' => 'nominatim',
        'base_url' => 'https://nominatim.openstreetmap.org',
        'timeout' => 10,
        'rate_limit' => 1, // seconds between requests
    ],
    
    'search' => [
        'min_chars' => 3,
        'max_results' => 10,
        'country_code' => 'tr', // Turkey only
        'language' => 'tr',
        'debounce_ms' => 300,
    ],
    
    'ui' => [
        'placeholder' => 'Adres arayın...',
        'no_results_text' => 'Sonuç bulunamadı',
        'loading_text' => 'Aranıyor...',
        'error_text' => 'Arama sırasında hata oluştu',
        'keyboard_navigation' => true,
    ],
    
    'cache' => [
        'enabled' => true,
        'ttl' => 3600, // 1 hour
        'prefix' => 'location_finder_',
    ],
];
```

---

## 🎭 Component Options

### Available Props

```blade
<x-location-finder 
    name="address"              <!-- Input name (default: 'location') -->
    placeholder="Adres..."      <!-- Placeholder text -->
    value="Initial value"       <!-- Initial value -->
    :min-chars="3"             <!-- Minimum characters to search -->
    :max-results="10"          <!-- Maximum results to show -->
    :debounce-ms="300"         <!-- Debounce delay in milliseconds -->
    :required="false"          <!-- Required field -->
    class="custom-class"       <!-- Additional CSS classes -->
    on-select="callback"       <!-- JavaScript callback on selection -->
    on-clear="callback"        <!-- JavaScript callback on clear -->
    on-error="callback"        <!-- JavaScript callback on error -->
/>
```

---

## 🌐 API Endpoints

The package automatically registers these endpoints:

```php
GET  /api/location-finder/search?query=istanbul
POST /api/location-finder/geocode
POST /api/location-finder/reverse-geocode
```

### Search API Response
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

---

## 🛠️ Advanced Usage

### Programmatic Search

```php
use Sslah\LocationFinder\Services\GeocodingService;

$geocoding = app(GeocodingService::class);

// Search addresses
$results = $geocoding->search('Istanbul');

// Geocode specific address
$location = $geocoding->geocode('Taksim, Istanbul');

// Reverse geocode coordinates
$address = $geocoding->reverseGeocode(41.0082, 28.9784);
```

### Custom Styling

```css
/* Customize dropdown */
.location-finder-dropdown {
    max-height: 300px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
}

/* Customize highlighted item */
.location-finder-item:hover {
    background-color: #f3f4f6;
}
```

---

## 📋 Requirements

- **PHP**: 8.1 or higher
- **Laravel**: 10.0 or higher
- **Alpine.js**: 3.x (included in Laravel)
- **Tailwind CSS**: 3.x (for styling)

---

## 🎯 Why OpenStreetMap?

- **✅ Free:** No API keys, no rate limits
- **✅ Accurate:** Community-driven, constantly updated
- **✅ Global:** Worldwide coverage
- **✅ Open:** Open source, transparent
- **✅ Reliable:** Used by major websites

---

## 🤝 Contributing

We welcome contributions! Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

---

## 📄 License

The MIT License (MIT). Please see [License File](LICENSE) for more information.

---

## 🏆 Credits

- **Author**: [Sslah](https://github.com/ahmetsuslu)
- **OpenStreetMap**: For the amazing geocoding service
- **Alpine.js**: For the reactive frontend framework

---

## 📧 Support

For security issues, please email: mail@ahmetsuslu.com

For general questions, please use the [issue tracker](../../issues).

---

## 🌟 Star This Package!

If this package helps you build better Laravel applications, please ⭐ **star** it on GitHub!

---

**Made with ❤️ for the Laravel community**
