@php
$optionsJson = json_encode([
    'name' => $name,
    'placeholder' => $placeholder,
    'minChars' => $minChars,
    'maxResults' => $maxResults,
    'debounceMs' => $debounceMs,
    'required' => $required,
    'onSelect' => $onSelect,
    'onClear' => $onClear,
    'onError' => $onError,
    'config' => $config
]);
@endphp

<div 
    class="location-finder-container {{ $class }}"
    data-location-finder="{{ $optionsJson }}"
>
    <!-- JavaScript will render the component here -->
</div>

<!-- Location Finder JavaScript -->
@once
<script src="{{ asset('vendor/location-finder/js/location-finder.js') }}"></script>
@endonce 