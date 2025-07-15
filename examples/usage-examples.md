# Location Finder Package - Usage Examples

Bu dosya Laravel Location Finder paketinin çeşitli kullanım örneklerini içerir.

## 📖 Genel Bakış

Location Finder paketi, OpenStreetMap Nominatim API'si kullanarak geocoding hizmetleri sunan pure API service paketidir. Framework-agnostic yaklaşımı sayesinde herhangi bir frontend framework ile kullanılabilir.

## 🚀 Kurulum

```bash
composer require sslah/location-finder
php artisan location-finder:install
```

## 📍 API Endpoints

### 1. Konum Arama
```
GET /api/location-finder/search?query={query}
```

### 2. Geocoding (Adres → Koordinat)
```
POST /api/location-finder/geocode
```

### 3. Reverse Geocoding (Koordinat → Adres)
```
POST /api/location-finder/reverse-geocode
```

## 💻 Kullanım Örnekleri

### 1. Vanilla JavaScript ile Kullanım

```html
<!DOCTYPE html>
<html>
<head>
    <title>Location Finder Example</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
    <input type="text" id="search" placeholder="Adres arayın...">
    <div id="results"></div>

    <script>
    class LocationFinder {
        constructor() {
            this.searchInput = document.getElementById('search');
            this.resultsDiv = document.getElementById('results');
            this.setupEvents();
        }

        setupEvents() {
            let timeout;
            this.searchInput.addEventListener('input', (e) => {
                clearTimeout(timeout);
                timeout = setTimeout(() => {
                    this.search(e.target.value);
                }, 300);
            });
        }

        async search(query) {
            if (query.length < 3) return;

            try {
                const response = await fetch(`/api/location-finder/search?query=${encodeURIComponent(query)}`);
                const data = await response.json();
                
                if (data.success) {
                    this.displayResults(data.results);
                }
            } catch (error) {
                console.error('Arama hatası:', error);
            }
        }

        displayResults(results) {
            this.resultsDiv.innerHTML = results.map(result => `
                <div onclick="selectLocation('${result.display_name}', ${result.lat}, ${result.lon})">
                    <strong>${result.display_name}</strong><br>
                    <small>Lat: ${result.lat}, Lon: ${result.lon}</small>
                </div>
            `).join('');
        }
    }

    function selectLocation(address, lat, lon) {
        console.log('Seçilen konum:', { address, lat, lon });
        // Burada seçilen konumla istediğiniz işlemi yapabilirsiniz
    }

    // Initialize
    new LocationFinder();
    </script>
</body>
</html>
```

### 2. Alpine.js ile Kullanım

```html
<div x-data="locationFinder()">
    <input 
        type="text" 
        x-model="query" 
        @input.debounce.300ms="search()"
        placeholder="Adres arayın..."
    >
    
    <div x-show="loading">Aranıyor...</div>
    
    <div x-show="results.length > 0">
        <template x-for="result in results" :key="result.lat + result.lon">
            <div @click="selectLocation(result)" class="cursor-pointer p-2 hover:bg-gray-100">
                <div x-text="result.display_name" class="font-medium"></div>
                <div x-text="`${result.lat}, ${result.lon}`" class="text-sm text-gray-500"></div>
            </div>
        </template>
    </div>
</div>

<script>
function locationFinder() {
    return {
        query: '',
        results: [],
        loading: false,
        
        async search() {
            if (this.query.length < 3) {
                this.results = [];
                return;
            }
            
            this.loading = true;
            
            try {
                const response = await fetch(`/api/location-finder/search?query=${encodeURIComponent(this.query)}`);
                const data = await response.json();
                
                if (data.success) {
                    this.results = data.results;
                }
            } catch (error) {
                console.error('Arama hatası:', error);
            } finally {
                this.loading = false;
            }
        },
        
        selectLocation(location) {
            console.log('Seçilen konum:', location);
            this.query = location.display_name;
            this.results = [];
        }
    }
}
</script>
```

### 3. Vue.js ile Kullanım

```vue
<template>
  <div>
    <input 
      v-model="query" 
      @input="debounceSearch"
      placeholder="Adres arayın..."
    >
    
    <div v-if="loading">Aranıyor...</div>
    
    <div v-for="result in results" :key="result.lat + result.lon" 
         @click="selectLocation(result)"
         class="cursor-pointer p-2 hover:bg-gray-100">
      <div class="font-medium">{{ result.display_name }}</div>
      <div class="text-sm text-gray-500">{{ result.lat }}, {{ result.lon }}</div>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      query: '',
      results: [],
      loading: false,
      searchTimeout: null
    }
  },
  methods: {
    debounceSearch() {
      clearTimeout(this.searchTimeout);
      this.searchTimeout = setTimeout(() => {
        this.search();
      }, 300);
    },
    
    async search() {
      if (this.query.length < 3) {
        this.results = [];
        return;
      }
      
      this.loading = true;
      
      try {
        const response = await fetch(`/api/location-finder/search?query=${encodeURIComponent(this.query)}`);
        const data = await response.json();
        
        if (data.success) {
          this.results = data.results;
        }
      } catch (error) {
        console.error('Arama hatası:', error);
      } finally {
        this.loading = false;
      }
    },
    
    selectLocation(location) {
      console.log('Seçilen konum:', location);
      this.query = location.display_name;
      this.results = [];
      this.$emit('location-selected', location);
    }
  }
}
</script>
```

### 4. React ile Kullanım

```jsx
import React, { useState, useEffect } from 'react';

function LocationFinder({ onLocationSelect }) {
  const [query, setQuery] = useState('');
  const [results, setResults] = useState([]);
  const [loading, setLoading] = useState(false);

  useEffect(() => {
    const timeoutId = setTimeout(() => {
      if (query.length >= 3) {
        search();
      } else {
        setResults([]);
      }
    }, 300);

    return () => clearTimeout(timeoutId);
  }, [query]);

  const search = async () => {
    setLoading(true);
    
    try {
      const response = await fetch(`/api/location-finder/search?query=${encodeURIComponent(query)}`);
      const data = await response.json();
      
      if (data.success) {
        setResults(data.results);
      }
    } catch (error) {
      console.error('Arama hatası:', error);
    } finally {
      setLoading(false);
    }
  };

  const selectLocation = (location) => {
    console.log('Seçilen konum:', location);
    setQuery(location.display_name);
    setResults([]);
    onLocationSelect?.(location);
  };

  return (
    <div>
      <input 
        type="text"
        value={query}
        onChange={(e) => setQuery(e.target.value)}
        placeholder="Adres arayın..."
      />
      
      {loading && <div>Aranıyor...</div>}
      
      {results.map((result) => (
        <div 
          key={result.lat + result.lon}
          onClick={() => selectLocation(result)}
          className="cursor-pointer p-2 hover:bg-gray-100"
        >
          <div className="font-medium">{result.display_name}</div>
          <div className="text-sm text-gray-500">{result.lat}, {result.lon}</div>
        </div>
      ))}
    </div>
  );
}

export default LocationFinder;
```

### 5. Laravel Backend'de Kullanım

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Sslah\LocationFinder\Services\GeocodingService;

class LocationController extends Controller
{
    public function __construct(private GeocodingService $geocodingService)
    {
    }

    public function searchLocations(Request $request)
    {
        $query = $request->input('query');
        $results = $this->geocodingService->search($query);
        
        return response()->json([
            'success' => true,
            'results' => $results
        ]);
    }

    public function geocodeAddress(Request $request)
    {
        $address = $request->input('address');
        $result = $this->geocodingService->geocode($address);
        
        if ($result) {
            return response()->json([
                'success' => true,
                'result' => $result
            ]);
        }
        
        return response()->json([
            'success' => false,
            'message' => 'Adres bulunamadı'
        ], 404);
    }

    public function reverseGeocode(Request $request)
    {
        $lat = $request->input('lat');
        $lon = $request->input('lon');
        
        $result = $this->geocodingService->reverseGeocode($lat, $lon);
        
        if ($result) {
            return response()->json([
                'success' => true,
                'result' => $result
            ]);
        }
        
        return response()->json([
            'success' => false,
            'message' => 'Koordinat için adres bulunamadı'
        ], 404);
    }
}
```

### 6. Blade Template'de Form Entegrasyonu

```html
<form action="/save-address" method="POST">
    @csrf
    
    <div class="form-group">
        <label for="address">Adres:</label>
        <input type="text" id="address" name="address" required>
        <input type="hidden" id="latitude" name="latitude">
        <input type="hidden" id="longitude" name="longitude">
        <div id="address-suggestions"></div>
    </div>
    
    <button type="submit">Kaydet</button>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const addressInput = document.getElementById('address');
    const latInput = document.getElementById('latitude');
    const lonInput = document.getElementById('longitude');
    const suggestionsDiv = document.getElementById('address-suggestions');
    
    let timeout;
    
    addressInput.addEventListener('input', function() {
        clearTimeout(timeout);
        timeout = setTimeout(() => {
            searchAddress(this.value);
        }, 300);
    });
    
    async function searchAddress(query) {
        if (query.length < 3) {
            suggestionsDiv.innerHTML = '';
            return;
        }
        
        try {
            const response = await fetch(`/api/location-finder/search?query=${encodeURIComponent(query)}`);
            const data = await response.json();
            
            if (data.success) {
                displaySuggestions(data.results);
            }
        } catch (error) {
            console.error('Arama hatası:', error);
        }
    }
    
    function displaySuggestions(results) {
        suggestionsDiv.innerHTML = results.map(result => `
            <div onclick="selectAddress('${result.display_name}', ${result.lat}, ${result.lon})" 
                 class="suggestion-item">
                ${result.display_name}
            </div>
        `).join('');
    }
    
    function selectAddress(address, lat, lon) {
        addressInput.value = address;
        latInput.value = lat;
        lonInput.value = lon;
        suggestionsDiv.innerHTML = '';
    }
});
</script>
```

## 🔧 API Response Formatı

### Search Response
```json
{
  "success": true,
  "results": [
    {
      "display_name": "Ankara, Çankaya, Ankara, İç Anadolu Bölgesi, 06420, Türkiye",
      "lat": 39.9207759,
      "lon": 32.8540497,
      "class": "place",
      "type": "city"
    }
  ],
  "count": 1
}
```

### Geocode Response
```json
{
  "success": true,
  "result": {
    "display_name": "İstanbul, Türkiye",
    "lat": 41.0082,
    "lon": 28.9784,
    "class": "place",
    "type": "city"
  }
}
```

### Reverse Geocode Response
```json
{
  "success": true,
  "result": {
    "display_name": "Kadıköy, İstanbul, Türkiye",
    "lat": 40.9833,
    "lon": 29.0167,
    "class": "place",
    "type": "suburb"
  }
}
```

## ⚙️ Konfigürasyon

Config dosyası: `config/location-finder.php`

```php
return [
    'service' => [
        'provider' => 'nominatim',
        'base_url' => 'https://nominatim.openstreetmap.org',
        'user_agent' => 'LocationFinder/1.0 (Laravel Package)',
        'timeout' => 10,
    ],
    
    'search' => [
        'min_chars' => 3,
        'max_results' => 10,
        'country_code' => 'tr', // Turkey only
        'language' => 'tr',
    ],
    
    'cache' => [
        'enabled' => true,
        'ttl' => 3600, // 1 hour
        'prefix' => 'location_finder_',
    ],
];
```

## 🎯 Best Practices

1. **Debouncing**: Her karakter girişinde API çağrısı yapmayın, 300ms bekleyin
2. **Minimum Karakter**: En az 3 karakter girildikten sonra arama yapın
3. **Loading States**: Kullanıcıya arama durumunu gösterin
4. **Error Handling**: API hatalarını uygun şekilde handle edin
5. **Caching**: Aynı aramalar için cache kullanın (paket otomatik yapar)

## 🌍 OpenStreetMap Integration

Seçilen konumu haritada göstermek için:

```javascript
function showOnMap(lat, lon) {
    const url = `https://www.openstreetmap.org/?mlat=${lat}&mlon=${lon}#map=15/${lat}/${lon}`;
    window.open(url, '_blank');
}
```

## 🤝 Katkıda Bulunma

Bu paket açık kaynak kodludur. Katkılarınızı bekliyoruz!

- GitHub: https://github.com/ahmetsuslu/location-finder
- Issues: Sorunları bildirin
- Pull Requests: Geliştirmelerinizi gönderin

## 📄 Lisans

MIT License - Detaylar için LICENSE dosyasına bakın. 