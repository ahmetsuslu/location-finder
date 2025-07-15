<div x-data="locationFinder({
    name: '{{ $name }}',
    placeholder: '{{ $placeholder }}',
    value: '{{ $value }}',
    minChars: {{ $minChars }},
    maxResults: {{ $maxResults }},
    debounceMs: {{ $debounceMs }},
    required: {{ $required ? 'true' : 'false' }},
    onSelect: {{ $onSelect ? "'" . $onSelect . "'" : 'null' }},
    onClear: {{ $onClear ? "'" . $onClear . "'" : 'null' }},
    onError: {{ $onError ? "'" . $onError . "'" : 'null' }},
    config: @json($config)
})" class="relative">
    <!-- Input Field -->
    <div class="relative">
        <input 
            type="text" 
            x-model="query"
            x-on:input.debounce.{{ $debounceMs }}ms="search"
            x-on:keydown.arrow-down.prevent="highlightNext"
            x-on:keydown.arrow-up.prevent="highlightPrevious"
            x-on:keydown.enter.prevent="selectHighlighted"
            x-on:keydown.escape="clearResults"
            x-on:focus="showResults = true"
            x-on:blur="setTimeout(() => showResults = false, 200)"
            :placeholder="placeholder"
            :required="required"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent {{ $class }}"
        />
        
        <!-- Loading Indicator -->
        <div x-show="loading" class="absolute right-3 top-3">
            <svg class="animate-spin h-4 w-4 text-blue-500" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </div>

        <!-- Clear Button -->
        <button 
            x-show="query.length > 0" 
            x-on:click="clearInput"
            type="button"
            class="absolute right-3 top-3 text-gray-400 hover:text-gray-600"
        >
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
    </div>

    <!-- Results Dropdown -->
    <div 
        x-show="showResults && results.length > 0" 
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 transform scale-95"
        x-transition:enter-end="opacity-100 transform scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="opacity-100 transform scale-100"
        x-transition:leave-end="opacity-0 transform scale-95"
        class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-64 overflow-y-auto"
    >
        <template x-for="(result, index) in results" :key="index">
            <div 
                x-on:click="selectResult(result)"
                x-on:mouseenter="highlightedIndex = index"
                :class="{'bg-blue-50': highlightedIndex === index}"
                class="px-4 py-2 cursor-pointer hover:bg-blue-50 border-b border-gray-100 last:border-b-0"
            >
                <div class="text-sm font-medium text-gray-900" x-text="result.address"></div>
                <div class="text-xs text-gray-500">
                    Lat: <span x-text="result.lat"></span>, Lng: <span x-text="result.lng"></span>
                </div>
            </div>
        </template>
    </div>

    <!-- No Results Message -->
    <div 
        x-show="showResults && results.length === 0 && query.length >= minChars && !loading"
        class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg"
    >
        <div class="px-4 py-3 text-sm text-gray-500" x-text="config.ui.no_results_text"></div>
    </div>

    <!-- Error Message -->
    <div 
        x-show="error"
        class="absolute z-50 w-full mt-1 bg-red-50 border border-red-200 rounded-lg shadow-lg"
    >
        <div class="px-4 py-3 text-sm text-red-600" x-text="config.ui.error_text"></div>
    </div>

    <!-- Hidden inputs for form submission -->
    <input type="hidden" :name="name" :value="selectedResult ? selectedResult.address : ''">
    <input type="hidden" :name="name + '_lat'" :value="selectedResult ? selectedResult.lat : ''">
    <input type="hidden" :name="name + '_lng'" :value="selectedResult ? selectedResult.lng : ''">
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('locationFinder', (options) => ({
        query: options.value || '',
        results: [],
        selectedResult: null,
        highlightedIndex: -1,
        showResults: false,
        loading: false,
        error: false,
        
        name: options.name,
        placeholder: options.placeholder,
        minChars: options.minChars,
        maxResults: options.maxResults,
        debounceMs: options.debounceMs,
        required: options.required,
        onSelect: options.onSelect,
        onClear: options.onClear,
        onError: options.onError,
        config: options.config,

        search() {
            if (this.query.length < this.minChars) {
                this.results = [];
                this.showResults = false;
                return;
            }

            this.loading = true;
            this.error = false;

            fetch(`/api/location-finder/search?query=${encodeURIComponent(this.query)}`)
                .then(response => response.json())
                .then(data => {
                    this.loading = false;
                    if (data.success) {
                        this.results = data.results.slice(0, this.maxResults);
                        this.showResults = true;
                        this.highlightedIndex = -1;
                    } else {
                        this.error = true;
                        this.results = [];
                    }
                })
                .catch(error => {
                    this.loading = false;
                    this.error = true;
                    this.results = [];
                    console.error('Location search error:', error);
                    
                    if (this.onError) {
                        window[this.onError](error);
                    }
                });
        },

        selectResult(result) {
            this.selectedResult = result;
            this.query = result.address;
            this.results = [];
            this.showResults = false;
            this.highlightedIndex = -1;

            if (this.onSelect) {
                window[this.onSelect](result);
            }
        },

        highlightNext() {
            if (this.highlightedIndex < this.results.length - 1) {
                this.highlightedIndex++;
            }
        },

        highlightPrevious() {
            if (this.highlightedIndex > 0) {
                this.highlightedIndex--;
            }
        },

        selectHighlighted() {
            if (this.highlightedIndex >= 0 && this.results[this.highlightedIndex]) {
                this.selectResult(this.results[this.highlightedIndex]);
            }
        },

        clearResults() {
            this.results = [];
            this.showResults = false;
            this.highlightedIndex = -1;
        },

        clearInput() {
            this.query = '';
            this.selectedResult = null;
            this.clearResults();
            
            if (this.onClear) {
                window[this.onClear]();
            }
        }
    }));
});
</script> 