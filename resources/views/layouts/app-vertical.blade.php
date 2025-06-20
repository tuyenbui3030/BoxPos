<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="{{ session('theme', 'light') }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>{{ isset($header) ? $header . ' - ' : '' }}{{ config('app.name', 'BoxPos') }}</title>
    
    <!-- Tabler CSS -->
    <link rel="stylesheet" href="{{ asset('tabler/css/tabler.min.css') }}">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    
    <!-- Container Improvements CSS -->
    <link rel="stylesheet" href="{{ asset('css/container-improvements.css') }}">
    
    <!-- Livewire Styles -->
    @livewireStyles
    
    @stack('styles')
</head>
<body>
    <div class="page">
        <!-- Sidebar -->
        <aside class="navbar navbar-vertical navbar-expand-lg navbar-dark">
            <div class="container-fluid">
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#sidebar-menu" aria-controls="sidebar-menu" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                
                <h1 class="navbar-brand navbar-brand-autodark">
                    <a href="{{ route('dashboard') }}">
                        <img src="{{ asset('favicon.ico') }}" width="32" height="32" alt="{{ config('app.name') }}" class="navbar-brand-image">
                        {{ config('app.name', 'BoxPos') }}
                    </a>
                </h1>
                
                <div class="navbar-nav flex-row d-lg-none">
                    <div class="nav-item d-none d-lg-flex me-3">
                        <div class="btn-list">
                            <a href="#" class="btn" rel="noreferrer">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                    <circle cx="10" cy="10" r="7"/>
                                    <line x1="21" y1="21" x2="15" y2="15"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                    
                    <!-- Theme Toggle -->
                    <livewire:theme-switcher />
                    
                    <!-- User Dropdown -->
                    <livewire:user-dropdown />
                </div>
                
                <div class="collapse navbar-collapse" id="sidebar-menu">
                    <ul class="navbar-nav pt-lg-3">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                                <span class="nav-link-icon d-md-none d-lg-inline-block">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                        <polyline points="5 12 3 12 12 3 21 12 19 12"/>
                                        <path d="M5 12v7a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-7"/>
                                        <path d="M9 21v-6a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v6"/>
                                    </svg>
                                </span>
                                <span class="nav-link-title">
                                    Dashboard
                                </span>
                            </a>
                        </li>
                        
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#navbar-base" data-bs-toggle="dropdown" data-bs-auto-close="false" role="button" aria-expanded="false">
                                <span class="nav-link-icon d-md-none d-lg-inline-block">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                        <path d="M12 3l8 4.5l0 9l-8 4.5l-8 -4.5l0 -9l8 -4.5"/>
                                        <path d="M12 12l8 -4.5"/>
                                        <path d="M12 12l0 9"/>
                                        <path d="M12 12l-8 -4.5"/>
                                    </svg>
                                </span>
                                <span class="nav-link-title">
                                    Products
                                </span>
                            </a>
                            <div class="dropdown-menu">
                                <div class="dropdown-menu-columns">
                                    <div class="dropdown-menu-column">
                                        <a class="dropdown-item" href="#">
                                            All Products
                                        </a>
                                        <a class="dropdown-item" href="#">
                                            Categories
                                        </a>
                                        <a class="dropdown-item" href="#">
                                            Inventory
                                        </a>
                                        <a class="dropdown-item" href="#">
                                            Add Product
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link" href="#">
                                <span class="nav-link-icon d-md-none d-lg-inline-block">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                        <path d="M9 7m-4 0a4 4 0 1 0 8 0a4 4 0 1 0 -8 0"/>
                                        <path d="M3 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2"/>
                                        <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                                        <path d="M21 21v-2a4 4 0 0 0 -3 -3.85"/>
                                    </svg>
                                </span>
                                <span class="nav-link-title">
                                    Customers
                                </span>
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link" href="#">
                                <span class="nav-link-icon d-md-none d-lg-inline-block">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                        <path d="M3 19a9 9 0 0 1 9 0a9 9 0 0 1 9 0"/>
                                        <path d="M3 6a9 9 0 0 1 9 0a9 9 0 0 1 9 0"/>
                                        <path d="M3 6l0 13"/>
                                        <path d="M12 6l0 13"/>
                                        <path d="M21 6l0 13"/>
                                    </svg>
                                </span>
                                <span class="nav-link-title">
                                    Orders
                                </span>
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link" href="#">
                                <span class="nav-link-icon d-md-none d-lg-inline-block">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                        <path d="M7 9m0 2a2 2 0 0 1 2 -2h6a2 2 0 0 1 2 2v6a2 2 0 0 1 -2 2h-6a2 2 0 0 1 -2 -2z"/>
                                        <path d="M14 9l0 -4a2 2 0 0 0 -2 -2h-2a2 2 0 0 0 -2 2v4"/>
                                        <path d="M12 16l0 .01"/>
                                        <path d="M3 13a20 20 0 0 0 18 0"/>
                                    </svg>
                                </span>
                                <span class="nav-link-title">
                                    POS Terminal
                                </span>
                            </a>
                        </li>
                        
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#navbar-help" data-bs-toggle="dropdown" data-bs-auto-close="false" role="button" aria-expanded="false">
                                <span class="nav-link-icon d-md-none d-lg-inline-block">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                        <path d="M14 3v4a1 1 0 0 0 1 1h4"/>
                                        <path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z"/>
                                    </svg>
                                </span>
                                <span class="nav-link-title">
                                    Reports
                                </span>
                            </a>
                            <div class="dropdown-menu">
                                <a class="dropdown-item" href="#">
                                    Sales Report
                                </a>
                                <a class="dropdown-item" href="#">
                                    Product Report
                                </a>
                                <a class="dropdown-item" href="#">
                                    Customer Report
                                </a>
                                <a class="dropdown-item" href="#">
                                    Financial Report
                                </a>
                            </div>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link" href="#">
                                <span class="nav-link-icon d-md-none d-lg-inline-block">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                        <path d="M10.325 4.317c.426 -1.756 2.924 -1.756 3.35 0a1.724 1.724 0 0 0 2.573 1.066c1.543 -.94 3.31 .826 2.37 2.37a1.724 1.724 0 0 0 1.065 2.572c1.756 .426 1.756 2.924 0 3.35a1.724 1.724 0 0 0 -1.066 2.573c.94 1.543 -.826 3.31 -2.37 2.37a1.724 1.724 0 0 0 -2.572 1.065c-.426 1.756 -2.924 1.756 -3.35 0a1.724 1.724 0 0 0 -2.573 -1.066c-1.543 .94 -3.31 -.826 -2.37 -2.37a1.724 1.724 0 0 0 -1.065 -2.572c-1.756 -.426 -1.756 -2.924 0 -3.35a1.724 1.724 0 0 0 1.066 -2.573c-.94 -1.543 .826 -3.31 2.37 -2.37c1 .608 2.296 .07 2.572 -1.065z"/>
                                        <circle cx="12" cy="12" r="3"/>
                                    </svg>
                                </span>
                                <span class="nav-link-title">
                                    Settings
                                </span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </aside>
        
        <!-- Main content -->
        <div class="page-wrapper">
            <!-- Page header -->
            @if(isset($header))
            <div class="page-header d-print-none">
                <div class="container-xxl">
                    <div class="row g-2 align-items-center">
                        <div class="col">
                            <div class="page-pretitle">
                                Overview
                            </div>
                            <h2 class="page-title">
                                {{ $header }}
                            </h2>
                        </div>
                        <!-- Page title actions -->
                        <div class="col-auto ms-auto d-print-none">
                            @isset($headerActions)
                                {{ $headerActions }}
                            @endisset
                        </div>
                    </div>
                </div>
            </div>
            @endif
            
            <!-- Page body -->
            <div class="page-body">
                <div class="container-xxl">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </div>
    
    <!-- Tabler JS -->
    <script src="{{ asset('tabler/js/tabler.min.js') }}"></script>
    
    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- Livewire Scripts -->
    @livewireScripts
    
    @stack('scripts')
</body>
</html>
