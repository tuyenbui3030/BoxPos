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



    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('store-switched-reload', (event) => {
                console.log('Store switched event received:', event);
                setTimeout(() => {
                    window.location.href = event.redirectUrl;
                }, 100);
            });
        });
    </script>
</head>
<body>
    <div class="page">
        <!-- Navbar -->
        <header class="navbar navbar-expand-md navbar-light d-print-none">
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

    <!-- Vendor Libraries JS -->
    @vite('resources/js/vendors.js')

    <!-- Livewire Scripts -->
    @livewireScripts

    <!-- Configure Livewire for localized routes -->
    @if(request()->route('locale'))
    <script>
        console.log('Livewire localization script loaded for locale: {{ request()->route("locale") }}');

        // Set global configuration
        window.livewire_app_url = '/{{ request()->route("locale") }}';
        window.livewire_update_url = '/{{ request()->route("locale") }}/livewire/update';

        console.log('Livewire app URL:', window.livewire_app_url);
        console.log('Livewire update URL:', window.livewire_update_url);

        // Try multiple approaches to configure Livewire

        // Approach 1: Configure immediately if Livewire is already loaded
        if (window.Livewire) {
            console.log('Livewire found, configuring immediately');
            window.Livewire.updateUrl = window.livewire_update_url;
        }

        // Approach 2: Configure on DOMContentLoaded
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM loaded, checking for Livewire');
            if (window.Livewire) {
                console.log('Configuring Livewire on DOM ready');
                window.Livewire.updateUrl = window.livewire_update_url;
            }
        });

        // Approach 3: Configure on Livewire init
        document.addEventListener('livewire:init', function() {
            console.log('Livewire init event fired');
            if (window.Livewire) {
                console.log('Configuring Livewire on init');
                window.Livewire.updateUrl = window.livewire_update_url;

                // Hook into requests
                window.Livewire.hook('request', ({ uri, options, payload, respond, succeed, fail }) => {
                    console.log('Original request URI:', uri);
                    if (uri === '/livewire/update') {
                        uri = window.livewire_update_url;
                        console.log('Corrected request URI:', uri);
                    }
                    return { uri, options, payload, respond, succeed, fail };
                });
            }
        });

        // Approach 4: Try to override after a delay
        setTimeout(function() {
            if (window.Livewire) {
                console.log('Configuring Livewire after delay');
                window.Livewire.updateUrl = window.livewire_update_url;
            }
        }, 1000);

        // Listen for redirect events from language switcher
        document.addEventListener('livewire:init', function() {
            window.Livewire.on('redirect-to-url', (event) => {
                console.log('Redirecting to:', event.url);
                window.location.href = event.url;
            });
        });
    </script>
    @endif

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
