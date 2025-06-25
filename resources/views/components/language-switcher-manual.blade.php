<div class="nav-item dropdown" id="languageDropdownContainer">
    @php
        $currentLang = get_current_language();
        $availableLangs = get_available_languages();
    @endphp
    
    <!-- Debug: kiểm tra xem data có load được không -->
    <script>
        console.log('Language data:', @json($currentLang), @json($availableLangs));
    </script>
    
    <a href="#" class="nav-link px-0" id="languageDropdown">
        <span class="me-2">{{ $currentLang['flag'] ?? '🌐' }}</span>
        <span class="d-none d-lg-inline">{{ $currentLang['native'] ?? __('app.language') }}</span>
        <span class="badge bg-secondary ms-1">{{ count($availableLangs) }}</span>
    </a>
    
    <div class="dropdown-menu dropdown-menu-arrow dropdown-menu-end" id="languageDropdownMenu" style="display: none;">
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

<!-- Manual dropdown implementation -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const dropdown = document.getElementById('languageDropdown');
    const dropdownMenu = document.getElementById('languageDropdownMenu');
    const container = document.getElementById('languageDropdownContainer');
    
    if (dropdown && dropdownMenu) {
        console.log('Language dropdown elements found');
        
        let isOpen = false;
        
        dropdown.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            console.log('Language dropdown clicked');
            
            if (isOpen) {
                dropdownMenu.style.display = 'none';
                container.classList.remove('show');
                dropdownMenu.classList.remove('show');
                isOpen = false;
                console.log('Dropdown closed');
            } else {
                dropdownMenu.style.display = 'block';
                container.classList.add('show');
                dropdownMenu.classList.add('show');
                isOpen = true;
                console.log('Dropdown opened');
            }
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!container.contains(e.target)) {
                dropdownMenu.style.display = 'none';
                container.classList.remove('show');
                dropdownMenu.classList.remove('show');
                isOpen = false;
            }
        });
        
        // Close dropdown when selecting an item
        dropdownMenu.addEventListener('click', function(e) {
            if (e.target.classList.contains('dropdown-item')) {
                console.log('Language selected:', e.target.dataset.locale);
            }
        });
    } else {
        console.log('Language dropdown elements NOT found');
    }
});
</script>
