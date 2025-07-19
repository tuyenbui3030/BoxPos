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

    <!-- Livewire Scripts -->
    @livewireScripts

    <!-- Vite JS (includes Alpine.js and all dependencies) -->
    @vite(['resources/js/app.js'])
</body>
</html>
