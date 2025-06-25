<div class="nav-item dropdown">
    @php
        $currentLang = get_current_language();
        $availableLangs = get_available_languages();
    @endphp
    
    <a href="#" class="nav-link px-0" data-bs-toggle="dropdown" tabindex="-1" aria-label="Language selector">
        <span class="me-2">{{ $currentLang['flag'] ?? '🌐' }}</span>
        <span class="d-none d-lg-inline">{{ $currentLang['native'] ?? __('app.language') }}</span>
    </a>
    
    <div class="dropdown-menu dropdown-menu-arrow dropdown-menu-end">
        @foreach($availableLangs as $locale => $language)
            <a 
                class="dropdown-item {{ is_current_language($locale) ? 'active' : '' }}" 
                href="{{ language_url($locale) }}"
            >
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
