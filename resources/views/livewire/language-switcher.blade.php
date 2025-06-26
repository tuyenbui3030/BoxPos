<div class="nav-item dropdown"
     x-data="{ isOpen: @entangle('isDropdownOpen').live }"
     x-on:click.away="$wire.closeDropdown()"
     wire:ignore.self>

    <a href="#"
       class="nav-link d-flex lh-1 text-reset p-0"
       aria-label="Language selector"
       wire:click="toggleDropdown"
       x-bind:aria-expanded="isOpen"
       wire:loading.class="pe-none opacity-50"
       wire:target="switchLanguage">

        <span class="avatar avatar-sm"
              wire:loading.style="opacity: 0.6"
              wire:target="switchLanguage">
            {{ $currentLanguage['flag'] ?? '🌐' }}
        </span>

        <div class="d-none d-xl-block ps-2">
            <div>{{ $currentLanguage['native'] ?? 'Language' }}</div>
            <div wire:loading
                 wire:target="switchLanguage"
                 class="small text-muted">
                {{ __('localization::language.switching') }}...
            </div>
        </div>
    </a>

    <div x-show="isOpen"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="dropdown-menu dropdown-menu-arrow dropdown-menu-end show"
         style="position: absolute; top: 100%; right: 0; z-index: 1050;"
         wire:loading.remove
         wire:target="switchLanguage">

        <h6 class="dropdown-header">{{ __('localization::language.select_language') }}</h6>

        @foreach($availableLanguages as $locale => $language)
            <a href="#"
               class="dropdown-item {{ $currentLocale === $locale ? 'active' : '' }}"
               wire:click="switchLanguage('{{ $locale }}')"
               wire:loading.class="pe-none opacity-50"
               wire:target="switchLanguage('{{ $locale }}')">

                <span class="me-2">{{ $language['flag'] ?? '🌐' }}</span>
                <span>{{ $language['native'] ?? $language['name'] ?? $locale }}</span>

                @if($currentLocale === $locale)
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon ms-auto text-success" width="18" height="18" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                        <path d="M5 12l5 5l10 -10"/>
                    </svg>
                @endif

                <span wire:loading
                      wire:target="switchLanguage('{{ $locale }}')"
                      class="ms-auto">
                    <span class="spinner-border spinner-border-sm" role="status"></span>
                </span>
            </a>
        @endforeach
    </div>
</div>
