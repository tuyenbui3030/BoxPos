<div class="nav-item dropdown">
    @php
        $currentLang = get_current_language();
        $availableLangs = get_available_languages();
    @endphp
    
    <!-- Debug: kiểm tra xem data có load được không -->
    <script>
        console.log('Language data:', @json($currentLang), @json($availableLangs));
    </script>
    
    <a href="#" class="nav-link px-0" data-bs-toggle="dropdown" tabindex="-1" aria-label="Language selector" id="languageDropdown">
        <span class="me-2">{{ $currentLang['flag'] ?? '🌐' }}</span>
        <span class="d-none d-lg-inline">{{ $currentLang['native'] ?? __('app.language') }}</span>
        <span class="badge bg-secondary ms-1">{{ count($availableLangs) }}</span>
    </a>
    
    <div class="dropdown-menu dropdown-menu-arrow dropdown-menu-end" aria-labelledby="languageDropdown">
        <h6 class="dropdown-header">{{ __('app.select_language') }}</h6>
        @forelse($availableLangs as $locale => $language)
            <a 
                class="dropdown-item {{ is_current_language($locale) ? 'active' : '' }}" 
                href="{{ language_url($locale) }}"
                data-locale="{{ $locale }}"
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
        @empty
            <span class="dropdown-item text-muted">No languages available</span>
        @endforelse
    </div>
</div>

<!-- Debug script to test dropdown manually -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const dropdown = document.getElementById('languageDropdown');
    if (dropdown) {
        console.log('Language dropdown found');
        
        // Khởi tạo Bootstrap dropdown thủ công
        try {
            if (typeof bootstrap !== 'undefined') {
                const dropdownInstance = new bootstrap.Dropdown(dropdown);
                console.log('Bootstrap dropdown initialized');
                
                dropdown.addEventListener('click', function(e) {
                    e.preventDefault();
                    console.log('Language dropdown clicked');
                    dropdownInstance.toggle();
                });
            } else {
                console.log('Bootstrap not found');
            }
        } catch (error) {
            console.log('Bootstrap dropdown init error:', error);
        }
    } else {
        console.log('Language dropdown NOT found');
    }
});
</script>
