<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>{{ config('app.name', 'Laravel') }}</title>
    
    <!-- Tabler Core CSS -->
    <link rel="stylesheet" href="{{ asset('tabler/css/tabler.min.css') }}">
    
    <!-- Tabler Vendor CSS -->
    <link rel="stylesheet" href="{{ asset('tabler/css/tabler-flags.min.css') }}">
    <link rel="stylesheet" href="{{ asset('tabler/css/tabler-payments.min.css') }}">
    <link rel="stylesheet" href="{{ asset('tabler/css/tabler-vendors.min.css') }}">
    
    <!-- Custom Tabler overrides -->
    <link rel="stylesheet" href="{{ asset('tabler/css/tabler-custom.css') }}">
    
    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Livewire Styles -->
    @livewireStyles
</head>
<body class="border-top-wide border-primary d-flex flex-column theme-light">
    <div class="page page-center">
        <div class="container container-tight py-4">
            <div class="text-center mb-4">
                <a href="{{ url('/') }}" class="navbar-brand navbar-brand-autodark">
                    <h1>{{ config('app.name', 'Laravel') }}</h1>
                </a>
            </div>
            
            @isset($slot)
                {{ $slot }}
            @else
                @yield('content')
            @endisset
        </div>
    </div>
    
    <!-- Tabler JS -->
    <script src="{{ asset('tabler/js/tabler.min.js') }}"></script>
    
    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- Livewire Scripts -->
    @livewireScripts
</body>
</html>
