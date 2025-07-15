/**
 * Location Finder - Vanilla JavaScript
 * OpenStreetMap/Nominatim powered address search
 * No dependencies - works with any framework
 */

class LocationFinder {
    constructor(element, options = {}) {
        this.element = element;
        this.options = {
            name: options.name || 'address',
            placeholder: options.placeholder || 'Adres arayın...',
            minChars: options.minChars || 3,
            maxResults: options.maxResults || 10,
            debounceMs: options.debounceMs || 300,
            required: options.required || false,
            onSelect: options.onSelect || null,
            onClear: options.onClear || null,
            onError: options.onError || null,
            config: options.config || {}
        };

        this.query = '';
        this.results = [];
        this.selectedResult = null;
        this.highlightedIndex = -1;
        this.showResults = false;
        this.loading = false;
        this.error = false;
        this.debounceTimer = null;

        this.init();
    }

    init() {
        this.createHTML();
        this.bindEvents();
    }

    createHTML() {
        this.element.innerHTML = `
            <div class="location-finder-wrapper relative">
                <div class="relative">
                    <input 
                        type="text" 
                        class="location-finder-input w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="${this.options.placeholder}"
                        ${this.options.required ? 'required' : ''}
                    />
                    
                    <div class="location-finder-loading absolute right-3 top-3 hidden">
                        <svg class="animate-spin h-4 w-4 text-blue-500" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </div>

                    <button 
                        class="location-finder-clear absolute right-3 top-3 text-gray-400 hover:text-gray-600 hidden"
                        type="button"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <div class="location-finder-dropdown absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-64 overflow-y-auto hidden">
                    <div class="location-finder-results"></div>
                </div>

                <div class="location-finder-no-results absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg hidden">
                    <div class="px-4 py-3 text-sm text-gray-500">${this.options.config.ui?.no_results_text || 'Sonuç bulunamadı'}</div>
                </div>

                <div class="location-finder-error absolute z-50 w-full mt-1 bg-red-50 border border-red-200 rounded-lg shadow-lg hidden">
                    <div class="px-4 py-3 text-sm text-red-600">${this.options.config.ui?.error_text || 'Arama sırasında hata oluştu'}</div>
                </div>

                <input type="hidden" name="${this.options.name}" value="">
                <input type="hidden" name="${this.options.name}_lat" value="">
                <input type="hidden" name="${this.options.name}_lng" value="">
            </div>
        `;

        // Get references to created elements
        this.input = this.element.querySelector('.location-finder-input');
        this.loadingElement = this.element.querySelector('.location-finder-loading');
        this.clearButton = this.element.querySelector('.location-finder-clear');
        this.dropdown = this.element.querySelector('.location-finder-dropdown');
        this.resultsContainer = this.element.querySelector('.location-finder-results');
        this.noResultsElement = this.element.querySelector('.location-finder-no-results');
        this.errorElement = this.element.querySelector('.location-finder-error');
        this.hiddenInput = this.element.querySelector(`input[name="${this.options.name}"]`);
        this.hiddenLatInput = this.element.querySelector(`input[name="${this.options.name}_lat"]`);
        this.hiddenLngInput = this.element.querySelector(`input[name="${this.options.name}_lng"]`);
    }

    bindEvents() {
        this.input.addEventListener('input', (e) => this.onInput(e));
        this.input.addEventListener('keydown', (e) => this.onKeydown(e));
        this.input.addEventListener('focus', () => this.onFocus());
        this.input.addEventListener('blur', () => this.onBlur());
        this.clearButton.addEventListener('click', () => this.clearInput());
        
        // Close dropdown when clicking outside
        document.addEventListener('click', (e) => {
            if (!this.element.contains(e.target)) {
                this.hideDropdown();
            }
        });
    }

    onInput(e) {
        this.query = e.target.value;
        this.updateClearButton();
        
        if (this.debounceTimer) {
            clearTimeout(this.debounceTimer);
        }
        
        this.debounceTimer = setTimeout(() => {
            this.search();
        }, this.options.debounceMs);
    }

    onKeydown(e) {
        if (e.key === 'ArrowDown') {
            e.preventDefault();
            this.highlightNext();
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            this.highlightPrevious();
        } else if (e.key === 'Enter') {
            e.preventDefault();
            this.selectHighlighted();
        } else if (e.key === 'Escape') {
            this.hideDropdown();
        }
    }

    onFocus() {
        if (this.results.length > 0) {
            this.showDropdown();
        }
    }

    onBlur() {
        setTimeout(() => {
            this.hideDropdown();
        }, 200);
    }

    search() {
        if (this.query.length < this.options.minChars) {
            this.results = [];
            this.hideDropdown();
            return;
        }

        this.setLoading(true);
        this.setError(false);

        fetch(`/api/location-finder/search?query=${encodeURIComponent(this.query)}`)
            .then(response => response.json())
            .then(data => {
                this.setLoading(false);
                if (data.success) {
                    this.results = data.results.slice(0, this.options.maxResults);
                    this.renderResults();
                    this.showDropdown();
                    this.highlightedIndex = -1;
                } else {
                    this.setError(true);
                    this.results = [];
                }
            })
            .catch(error => {
                this.setLoading(false);
                this.setError(true);
                this.results = [];
                console.error('Location search error:', error);
                
                if (this.options.onError) {
                    if (typeof this.options.onError === 'function') {
                        this.options.onError(error);
                    } else if (typeof this.options.onError === 'string') {
                        // String function name'i çalıştır
                        if (typeof window[this.options.onError] === 'function') {
                            window[this.options.onError](error);
                        }
                    }
                }
            });
    }

    renderResults() {
        this.resultsContainer.innerHTML = '';
        
        this.results.forEach((result, index) => {
            const resultElement = document.createElement('div');
            resultElement.className = 'location-finder-result px-4 py-2 cursor-pointer hover:bg-blue-50 border-b border-gray-100 last:border-b-0';
            resultElement.innerHTML = `
                <div class="text-sm font-medium text-gray-900">${result.address}</div>
                <div class="text-xs text-gray-500">Lat: ${result.lat}, Lng: ${result.lng}</div>
            `;
            
            resultElement.addEventListener('click', () => this.selectResult(result));
            resultElement.addEventListener('mouseenter', () => this.setHighlightedIndex(index));
            
            this.resultsContainer.appendChild(resultElement);
        });
    }

    selectResult(result) {
        this.selectedResult = result;
        this.query = result.address;
        this.input.value = result.address;
        this.updateHiddenInputs();
        this.hideDropdown();
        this.updateClearButton();
        this.highlightedIndex = -1;

        if (this.options.onSelect) {
            if (typeof this.options.onSelect === 'function') {
                this.options.onSelect(result);
            } else if (typeof this.options.onSelect === 'string') {
                // String function name'i çalıştır
                if (typeof window[this.options.onSelect] === 'function') {
                    window[this.options.onSelect](result);
                }
            }
        }
    }

    highlightNext() {
        if (this.highlightedIndex < this.results.length - 1) {
            this.setHighlightedIndex(this.highlightedIndex + 1);
        }
    }

    highlightPrevious() {
        if (this.highlightedIndex > 0) {
            this.setHighlightedIndex(this.highlightedIndex - 1);
        }
    }

    setHighlightedIndex(index) {
        // Remove previous highlight
        this.resultsContainer.querySelectorAll('.location-finder-result').forEach(el => {
            el.classList.remove('bg-blue-50');
        });
        
        this.highlightedIndex = index;
        
        // Add new highlight
        if (index >= 0 && index < this.results.length) {
            const resultElement = this.resultsContainer.children[index];
            resultElement.classList.add('bg-blue-50');
        }
    }

    selectHighlighted() {
        if (this.highlightedIndex >= 0 && this.results[this.highlightedIndex]) {
            this.selectResult(this.results[this.highlightedIndex]);
        }
    }

    clearInput() {
        this.query = '';
        this.input.value = '';
        this.selectedResult = null;
        this.updateHiddenInputs();
        this.hideDropdown();
        this.updateClearButton();
        
        if (this.options.onClear) {
            if (typeof this.options.onClear === 'function') {
                this.options.onClear();
            } else if (typeof this.options.onClear === 'string') {
                // String function name'i çalıştır
                if (typeof window[this.options.onClear] === 'function') {
                    window[this.options.onClear]();
                }
            }
        }
    }

    updateHiddenInputs() {
        this.hiddenInput.value = this.selectedResult ? this.selectedResult.address : '';
        this.hiddenLatInput.value = this.selectedResult ? this.selectedResult.lat : '';
        this.hiddenLngInput.value = this.selectedResult ? this.selectedResult.lng : '';
    }

    updateClearButton() {
        if (this.query.length > 0) {
            this.clearButton.classList.remove('hidden');
        } else {
            this.clearButton.classList.add('hidden');
        }
    }

    setLoading(loading) {
        this.loading = loading;
        if (loading) {
            this.loadingElement.classList.remove('hidden');
        } else {
            this.loadingElement.classList.add('hidden');
        }
    }

    setError(error) {
        this.error = error;
        if (error) {
            this.errorElement.classList.remove('hidden');
            this.hideDropdown();
        } else {
            this.errorElement.classList.add('hidden');
        }
    }

    showDropdown() {
        this.showResults = true;
        if (this.results.length > 0) {
            this.dropdown.classList.remove('hidden');
            this.noResultsElement.classList.add('hidden');
        } else if (this.query.length >= this.options.minChars && !this.loading) {
            this.noResultsElement.classList.remove('hidden');
            this.dropdown.classList.add('hidden');
        }
    }

    hideDropdown() {
        this.showResults = false;
        this.dropdown.classList.add('hidden');
        this.noResultsElement.classList.add('hidden');
    }
}

// Auto-initialize all location finder elements
document.addEventListener('DOMContentLoaded', function() {
    const elements = document.querySelectorAll('[data-location-finder]');
    elements.forEach(element => {
        const options = JSON.parse(element.dataset.locationFinder || '{}');
        new LocationFinder(element, options);
    });
});

// Global function for manual initialization
window.LocationFinder = LocationFinder; 