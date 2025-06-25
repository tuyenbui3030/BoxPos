<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="{{ session('theme', 'light') }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="user-authenticated" content="{{ auth()->check() ? 'true' : 'false' }}">
    
    <title>{{ isset($header) ? $header . ' - ' : '' }}{{ config('app.name', 'BoxPos') }}</title>
    
    <!-- Vendor Libraries CSS -->
    @vite('resources/css/vendors.css')
    
    <!-- Main CSS (includes Tabler CSS) -->
    @vite('resources/css/app.css')
        
    <!-- Livewire Styles -->
    @livewireStyles
    
    @stack('styles')
</head>
<body>
    <div class="page">
        <!-- Navbar -->
        <header class="navbar navbar-expand-md d-print-none">
            <div class="container-xxl">
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbar-menu" aria-controls="navbar-menu" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <x-navigation.brand-logo />
                
                <x-navigation.header-tools />
                
                <livewire:main-navigation />
            </div>
        </header>
        
        <div class="page-wrapper">
            <x-layout.page-header :title="$header ?? null" :actions="$headerActions ?? null" />
            
            <!-- Page body -->
            <div class="page-body">
                <div class="container-xxl">
                    {{ $slot }}
                </div>
            </div>
            
            <x-layout.footer />
        </div>
    </div>
    
    <x-modals.search-modal />
    
    <!-- Global Confirmation Modal -->
    <livewire:confirmation-modal />
    
    <!-- Vendor Libraries JS -->
    @vite('resources/js/vendors.js')
    
    <!-- Livewire Scripts - Must be before app.js for proper initialization with wire:navigate -->
    @livewireScripts
    
    <!-- Main JS (includes Tabler JS and Alpine.js) -->
    @vite('resources/js/app.js')

    <!-- Global Language Switch Handler -->
    <script>
        document.addEventListener('livewire:init', () => {
            // Listen for Livewire locale-updated events
            Livewire.on('locale-updated', (event) => {
                console.log('Livewire locale updated:', event);
                
                // Refresh all Livewire components on the page
                setTimeout(() => {
                    // Find all Livewire components and refresh them
                    document.querySelectorAll('[wire\\:id]').forEach(element => {
                        try {
                            const wireId = element.getAttribute('wire:id');
                            if (wireId && window.Livewire.find(wireId)) {
                                window.Livewire.find(wireId).$refresh();
                            }
                        } catch (error) {
                            console.log('Could not refresh component:', error);
                        }
                    });
                }, 100); // Small delay to ensure the locale is properly set
            });
            
            // Also listen for custom browser events
            window.addEventListener('locale-updated', (event) => {
                console.log('Browser locale updated:', event.detail);
                
                // Refresh all Livewire components on the page
                setTimeout(() => {
                    document.querySelectorAll('[wire\\:id]').forEach(element => {
                        try {
                            const wireId = element.getAttribute('wire:id');
                            if (wireId && window.Livewire.find(wireId)) {
                                window.Livewire.find(wireId).$refresh();
                            }
                        } catch (error) {
                            console.log('Could not refresh component:', error);
                        }
                    });
                }, 100);
            });
        });
    </script>
    
    <!-- Session Keep Alive Script - Temporarily disabled -->
    {{-- <script src="{{ asset('js/session-keep-alive.js') }}"></script> --}}
    
    @stack('scripts')
</body>
</html>
