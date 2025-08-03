<div>
    <header class="navbar navbar-expand-md d-print-none">
        <div class="container-xl">
            <!-- Mobile Menu Toggle -->
            <button class="navbar-toggler" 
                    type="button" 
                    data-bs-toggle="collapse" 
                    data-bs-target="#navbar-menu"
                    aria-controls="navbar-menu" 
                    aria-expanded="false" 
                    aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- Brand Logo -->
            <div class="navbar-brand d-none-navbar-horizontal pe-0 pe-md-3">
                <a class="nav-link" href="{{ route('dashboard') }}" wire:navigate aria-label="{{ $this->storeName }}">
                    <img src="https://therangcoffee.com/logo2.png" 
                         width="32" 
                         height="32" 
                         alt="{{ $this->storeName }}" 
                         class="navbar-brand-image">
                    <span class="text-blue">Tenant</span><strong class="text-green fw-bold">POS</strong>
                </a>
            </div>

            <!-- Header Tools -->
            <div class="navbar-nav flex-row order-md-last">
                <!-- Search -->
                <div class="d-none d-md-flex">
                    <a href="#"
                       class="nav-link px-0"
                       @click.prevent="$dispatch('open-search-modal')"
                       tabindex="-1"
                       title="{{ __('Search') }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                            <circle cx="10" cy="10" r="7"/>
                            <line x1="21" y1="21" x2="15" y2="15"/>
                        </svg>
                    </a>
                </div>

                <!-- Theme Selector -->
                <div class="d-none d-md-flex">
                    <livewire:appearance-switcher key="theme-switcher" />
                </div>

                <!-- Notifications -->
                <div class="d-none d-md-flex me-3">
                    <livewire:notification-center key="notification-center" />
                </div>

                <!-- Store Selector (if multiple stores) -->
                @if(count($availableStores) > 1)
                    <div class="nav-item dropdown d-none d-md-flex me-3">
                        <livewire:store-selector key="store-selector" />
                    </div>
                @endif

                <!-- User Menu -->
                <div class="nav-item dropdown">
                    <x-user-dropdown />
                </div>
            </div>
        </div>
    </header>
</div>