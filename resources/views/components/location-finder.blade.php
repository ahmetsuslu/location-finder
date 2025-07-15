{{-- 
    Location Finder Package - API Service Only
    
    This package provides only backend API services.
    UI implementation is up to the user.
    
    Available endpoints:
    - GET /api/location-finder/search?query={query}
    - POST /api/location-finder/geocode
    - POST /api/location-finder/reverse-geocode
    
    Usage example:
    
    <input type="text" id="address-search" placeholder="Search address...">
    <div id="results"></div>
    
    <script>
        fetch('/api/location-finder/search?query=istanbul')
            .then(response => response.json())
            .then(data => {
                console.log(data.results);
                // Handle results as needed
            });
    </script>
--}}

<div class="location-finder-api-info">
    <p>Location Finder package provides API endpoints only.</p>
    <p>Please implement your own frontend UI.</p>
    <p>Available endpoints:</p>
    <ul>
        <li><code>GET /api/location-finder/search?query={query}</code></li>
        <li><code>POST /api/location-finder/geocode</code></li>
        <li><code>POST /api/location-finder/reverse-geocode</code></li>
    </ul>
</div> 