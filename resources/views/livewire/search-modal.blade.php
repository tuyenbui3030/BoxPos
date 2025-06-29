<!-- Search Modal -->
<div x-data="{
        isOpen: false,
        searchQuery: '',
        searchResults: [],
        isSearching: false,

        init() {
            // Ensure modal is closed on init
            this.isOpen = false;
        },

        openModal() {
            this.isOpen = true;
            this.$nextTick(() => {
                this.$refs.searchInput?.focus();
            });
        },

        closeModal() {
            this.isOpen = false;
            this.searchQuery = '';
            this.searchResults = [];
        },

        performSearch() {
            if (this.searchQuery.length >= 2) {
                this.isSearching = true;

                // Simulate search - replace with actual search logic
                setTimeout(() => {
                    this.searchResults = [
                        {
                            type: 'customer',
                            title: 'John Doe',
                            subtitle: 'Customer • john@example.com',
                            url: '{{ localized_route("customers") }}',
                            icon: '👤'
                        },
                        {
                            type: 'product',
                            title: 'Coffee Beans',
                            subtitle: 'Product • $15.99',
                            url: '#',
                            icon: '☕'
                        }
                    ];
                    this.isSearching = false;
                }, 300);
            } else {
                this.searchResults = [];
            }
        }
     }"
     x-show="isOpen"
     x-on:open-search-modal.window="openModal()"
     x-on:keydown.escape.window="closeModal()"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="modal modal-blur fade"
     style="z-index: 1055;"
     x-cloak
     tabindex="-1"
     role="dialog"
     aria-hidden="false">

    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95">

            <div class="modal-header">
                <h5 class="modal-title">{{ __('app.search') }}</h5>
                <button type="button"
                        class="btn-close"
                        @click="closeModal()"
                        aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <div class="input-group mb-3">
                    <input type="text"
                           class="form-control form-control-lg"
                           placeholder="{{ __('app.search_placeholder') }}"
                           x-model="searchQuery"
                           @input.debounce.300ms="performSearch()"
                           x-ref="searchInput"
                           autocomplete="off">
                    <button class="btn btn-primary"
                            type="button"
                            @click="performSearch()"
                            :disabled="isSearching">
                        <span x-show="!isSearching">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                <circle cx="11" cy="11" r="8"/>
                                <path d="m21 21-4.35-4.35"/>
                            </svg>
                        </span>
                        <span x-show="isSearching">
                            <span class="spinner-border spinner-border-sm" role="status"></span>
                        </span>
                    </button>
                </div>

                <!-- Search Results -->
                @if(count($searchResults) > 0)
                    <div class="list-group list-group-flush">
                        @foreach($searchResults as $index => $result)
                            <a href="#"
                               class="list-group-item list-group-item-action"
                               wire:click="selectResult('{{ $result['url'] }}')"
                               wire:key="search-result-{{ $index }}">
                                <div class="d-flex align-items-center">
                                    <span class="me-3">{{ $result['icon'] }}</span>
                                    <div>
                                        <div class="fw-bold">{{ $result['title'] }}</div>
                                        <small class="text-muted">{{ $result['subtitle'] }}</small>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @elseif($searchQuery && !$isSearching)
                    <div class="text-center py-4">
                        <div class="empty">
                            <div class="empty-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                    <circle cx="11" cy="11" r="8"/>
                                    <path d="m21 21-4.35-4.35"/>
                                </svg>
                            </div>
                            <p class="empty-title">{{ __('app.no_results_found') }}</p>
                            <p class="empty-subtitle text-muted">
                                {{ __('app.try_different_keywords') }}
                            </p>
                        </div>
                    </div>
                @endif

                <!-- Loading State -->
                <div wire:loading.delay class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">{{ __('app.searching') }}...</span>
                    </div>
                    <p class="mt-2 text-muted">{{ __('app.searching') }}...</p>
                </div>
            </div>

            <div class="modal-footer">
                <div class="text-muted small">
                    {{ __('app.search_tip') }}
                </div>
            </div>
        </div>
    </div>
</div>
