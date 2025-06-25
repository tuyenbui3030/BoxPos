<div class="nav-item d-flex" x-data="{ isOpen: @entangle('isDropdownOpen').live }"
     x-on:click.away="$wire.closeDropdown()">
    <div class="nav-item dropdown d-none d-md-flex" style="position: relative;">
        <a href="#" 
           class="nav-link px-0" 
           aria-label="Language selector"
           wire:click="toggleDropdown"
           x-bind:aria-expanded="isOpen">
            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler" width="20" height="20" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                <path d="M3 12a9 9 0 1 0 18 0a9 9 0 0 0 -18 0"/>
                <path d="M3.6 9h16.8"/>
                <path d="M3.6 15h16.8"/>
                <path d="M11.5 3a17 17 0 0 0 0 18"/>
                <path d="M12.5 3a17 17 0 0 1 0 18"/>
            </svg>
        </a>
        
        <div x-show="isOpen"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="dropdown-menu dropdown-menu-arrow dropdown-menu-end show" 
             style="position: absolute; top: 100%; right: 0; z-index: 1050; display: block;">
            
            <h6 class="dropdown-header">{{ __('app.select_language') }}</h6>
            
            @foreach($availableLanguages as $locale => $language)
                <a href="#"
                   class="dropdown-item {{ is_current_language($locale) ? 'active' : '' }}" 
                   wire:click="switchLanguage('{{ $locale }}')">
                    <span class="me-2">{{ $language['flag'] }}</span>
                    <span>{{ $language['native'] }}</span>
                    @if(is_current_language($locale))
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon ms-auto text-success" width="18" height="18" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                            <path d="M5 12l5 5l10 -10"/>
                        </svg>
                    @endif
                </a>
            @endforeach
        </div>
    </div>
</div>
