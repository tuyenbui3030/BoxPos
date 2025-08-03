<div>
    <!-- Settings Offcanvas -->
    @if($isOpen)
    <div class="offcanvas offcanvas-end show" 
         tabindex="-1" 
         id="offcanvasSettings" 
         aria-labelledby="offcanvasSettingsLabel"
         style="visibility: visible;">
        <div class="offcanvas-header">
            <h2 class="offcanvas-title" id="offcanvasSettingsLabel">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-1">
                    <path d="M10.325 4.317c.426 -1.756 2.924 -1.756 3.35 0a1.724 1.724 0 0 0 2.573 1.066c1.543 -.94 3.31 .826 2.37 2.37a1.724 1.724 0 0 0 1.065 2.572c1.756 .426 1.756 2.924 0 3.35a1.724 1.724 0 0 0 -1.066 2.573c.94 1.543 -.826 3.31 -2.37 2.37a1.724 1.724 0 0 0 -2.572 1.065c-.426 1.756 -2.924 1.756 -3.35 0a1.724 1.724 0 0 0 -2.573 -1.066c-1.543 .94 -3.31 -.826 -2.37 -2.37a1.724 1.724 0 0 0 -1.065 -2.572c-1.756 -.426 -1.756 -2.924 0 -3.35a1.724 1.724 0 0 0 1.066 -2.573c-.94 -1.543 .826 -3.31 2.37 -2.37c1 .608 2.296 .07 2.572 -1.065z"></path>
                    <path d="M9 12a3 3 0 1 0 6 0a3 3 0 0 0 -6 0"></path>
                </svg>
                {{ __('app.settings') }}
            </h2>
            <button type="button" class="btn-close" wire:click="closeSettings" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            <!-- Theme Selection -->
            <div class="mb-4">
                <h3 class="mb-3">{{ __('app.setting_theme') }}</h3>
                <div class="list-group list-group-flush">
                    @php
                        $themes = [
                            'light' => [
                                'name' => __('app.light_theme'),
                                'description' => __('app.light_theme_desc'),
                                'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 12m-4 0a4 4 0 1 0 8 0a4 4 0 1 0 -8 0"></path><path d="M3 12h1m8 -9v1m8 8h1m-9 8v1m-6.4 -15.4l.7 .7m12.1 -.7l-.7 .7m0 11.4l.7 .7m-12.1 -.7l-.7 .7"></path></svg>'
                            ],
                            'dark' => [
                                'name' => __('app.dark_theme'),
                                'description' => __('app.dark_theme_desc'),
                                'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3c.132 0 .263 0 .393 0a7.5 7.5 0 0 0 7.92 12.446a9 9 0 1 1 -8.313 -12.454z"></path></svg>'
                            ]
                        ];
                        $currentTheme = $this->currentTheme;
                    @endphp
                    
                    @foreach($themes as $themeKey => $theme)
                        <a href="#" 
                           wire:click.prevent="setTheme('{{ $themeKey }}')"
                           class="list-group-item list-group-item-action d-flex align-items-center {{ $currentTheme === $themeKey ? 'active' : '' }}">
                            <span class="avatar avatar-sm me-3 d-flex align-items-center justify-content-center">
                                {!! $theme['icon'] !!}
                            </span>
                            <div class="flex-fill">
                                <div class="font-weight-medium">{{ $theme['name'] }}</div>
                                <div class="text-secondary">{{ $theme['description'] }}</div>
                            </div>
                            @if($currentTheme === $themeKey)
                                <svg class="icon text-success" width="18" height="18" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none">
                                    <path d="M5 12l5 5l10 -10"/>
                                </svg>
                            @endif
                        </a>
                    @endforeach
                </div>
            </div>

            <!-- Language Selection -->
            <div class="mb-4">
                <h3 class="mb-3">{{ __('app.language') }}</h3>
                <div class="list-group list-group-flush">
                    @php
                        $languages = [
                            'vi' => ['flag' => '🇻🇳', 'name' => 'Tiếng Việt', 'native' => 'Tiếng Việt'],
                            'en' => ['flag' => '🇺🇸', 'name' => 'English', 'native' => 'English']
                        ];
                        $currentLocale = app()->getLocale();
                    @endphp
                    
                    @foreach($languages as $locale => $language)
                        <a href="#" 
                           onclick="switchLanguageWithPath('{{ $locale }}')" 
                           class="list-group-item list-group-item-action d-flex align-items-center {{ $currentLocale === $locale ? 'active' : '' }}">
                            <span class="avatar avatar-sm me-3">{{ $language['flag'] }}</span>
                            <div class="flex-fill">
                                <div class="font-weight-medium">{{ $language['native'] }}</div>
                                <div class="text-secondary">{{ $language['name'] }}</div>
                            </div>
                            @if($currentLocale === $locale)
                                <svg class="icon text-success" width="18" height="18" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none">
                                    <path d="M5 12l5 5l10 -10"/>
                                </svg>
                            @endif
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    
    <!-- Backdrop -->
    <div class="offcanvas-backdrop fade show" wire:click="closeSettings"></div>
    @endif
</div>

<script>
// document.addEventListener('livewire:init', () => {
//     // Apply theme changes immediately
//     Livewire.on('theme-changed', (event) => {
//         document.documentElement.setAttribute('data-bs-theme', event.theme);
//     });
    
//     // Handle language switch redirect without page reload
//     Livewire.on('redirect-to-url', (event) => {
//         console.log('Language switch redirect to:', event.url);
//         // Use pushState instead of location.href to avoid page reload
//         window.history.pushState({}, '', event.url);
//     });
    
//     // Handle locale update - refresh all Livewire components
//     Livewire.on('locale-updated', (event) => {
//         console.log('Locale updated to:', event.locale);
        
//         // Refresh all Livewire components
//         document.querySelectorAll('[wire\\:id]').forEach(element => {
//             try {
//                 const wireId = element.getAttribute('wire:id');
//                 if (wireId && window.Livewire.find(wireId)) {
//                     window.Livewire.find(wireId).$refresh();
//                 }
//             } catch (error) {
//                 console.log('Could not refresh component:', error);
//             }
//         });
//     });
// });

function switchLanguageWithPath(locale) {
    // Get current URL and pass it to Livewire
    const currentUrl = window.location.href;
    @this.call('switchLanguage', locale, currentUrl);
}
</script>