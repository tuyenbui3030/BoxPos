<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="{{ session('theme', 'light') }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="user-authenticated" content="{{ auth()->check() ? 'true' : 'false' }}">
    @if(request()->route('locale'))
        <meta name="livewire-update-uri" content="/{{ request()->route('locale') }}/livewire/update">
        <meta name="livewire-asset-url" content="/{{ request()->route('locale') }}">
    @endif

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
        <!-- Top Header -->
        <livewire:header-component />
        
        <!-- Navigation Header -->
        <livewire:navigation-component />

        <div class="page-wrapper">
            <x-layout.page-header :title="$header ?? null" :actions="$headerActions ?? null" />

            <!-- Page body -->
            <div class="page-body">
                <div class="container-xxl">
                    @hasSection('content')
                        @yield('content')
                    @else
                        {{ $slot }}
                    @endif
                </div>
            </div>

            <x-layout.footer />
        </div>
    </div>

    <!-- Global Search Modal -->
    <livewire:search-modal />

    <!-- Global Confirmation Modal -->
    <livewire:confirmation-modal />

    <!-- Settings Offcanvas -->
    <livewire:settings-offcanvas />

    <!-- Vendor Libraries JS -->
    @vite('resources/js/vendors.js')

    <!-- Livewire Scripts -->
    @livewireScripts

    <!-- Configure Livewire for localized routes -->
    @if(request()->route('locale'))
    <script>
        // Set Livewire configuration before it loads
        window.livewire_app_url = '/{{ request()->route("locale") }}';
        window.livewire_update_url = '/{{ request()->route("locale") }}/livewire/update';

        // Configure Livewire immediately when available
        if (typeof window.Livewire !== 'undefined') {
            window.Livewire.updateUrl = window.livewire_update_url;
        }

        // Configure on Livewire init
        document.addEventListener('livewire:init', function() {
            console.log('Configuring Livewire for locale: {{ request()->route("locale") }}');

            if (window.Livewire) {
                // Set the update URL directly
                window.Livewire.updateUrl = window.livewire_update_url;
                console.log('Livewire update URL set to:', window.livewire_update_url);
            }
        });

        // Also try to set it after a short delay
        setTimeout(function() {
            if (window.Livewire) {
                window.Livewire.updateUrl = window.livewire_update_url;
                console.log('Livewire update URL set (delayed) to:', window.livewire_update_url);
            }
        }, 100);
    </script>
    @endif

    <!-- Language switcher redirect handler -->
    <!-- <script>
        document.addEventListener('livewire:init', function() {
            window.Livewire.on('redirect-to-url', (event) => {
                // Use window.location.href for full page reload to avoid Livewire routing conflicts
                window.location.href = '/en/dashboard';
            });
        });
    </script> -->

    <!-- Main JS (includes Tabler JS and Alpine.js) -->
    @vite('resources/js/app.js')

    <!-- Global Language Switch Handler -->
    <script>
        document.addEventListener('livewire:init', () => {
            // Listen for Livewire locale-updated events
            Livewire.on('locale-updated', (event) => {
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
            });

            // Also listen for custom browser events
            Livewire.on('redirect-to-url', (event) => {
                console.log('Language switch redirect to:', event.url);
                // Use pushState instead of location.href to avoid page reload
                window.history.pushState({}, '', event.url);
            });
        });
    </script>

    <!-- Session Keep Alive Script - Temporarily disabled -->
    {{-- <script src="{{ asset('js/session-keep-alive.js') }}"></script> --}}

    @stack('scripts')
</body>
</html>
