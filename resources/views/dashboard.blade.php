@extends('layouts.app')

@section('content')
<div class="container-xxl">
    <div class="row row-deck row-cards">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ __('app.dashboard') }}</h3>
                </div>
                <div class="card-body">
                    @if($currentProject)
                        <div class="row">
                            <div class="col-md-8">
                                <h4>{{ __('app.welcome_message') }}</h4>
                                <p class="text-muted">{{ __('app.dashboard_description') }}</p>
                                
                                <div class="mt-4">
                                    <h5>{{ __('app.current_project') }}</h5>
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center">
                                                <span class="avatar avatar-md me-3" style="background-color: {{ $currentProject['color'] ?? '#6c757d' }}; color: white;">
                                                    {{ $currentProject['icon'] ?? '🏢' }}
                                                </span>
                                                <div>
                                                    <h6 class="mb-0">{{ $currentProject['name'] }}</h6>
                                                    <small class="text-muted">{{ $currentProject['description'] ?? 'No description' }}</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h4 class="card-title">{{ __('app.quick_actions') }}</h4>
                                    </div>
                                    <div class="card-body">
                                        <div class="list-group list-group-flush">
                                            <a href="{{ localized_route('customers') }}" class="list-group-item list-group-item-action">
                                                <div class="d-flex align-items-center">
                                                    <span class="me-3">👥</span>
                                                    <div>
                                                        <div>{{ __('app.menu_customers') }}</div>
                                                        <small class="text-muted">{{ __('app.manage_customers') }}</small>
                                                    </div>
                                                </div>
                                            </a>
                                            <a href="#" class="list-group-item list-group-item-action">
                                                <div class="d-flex align-items-center">
                                                    <span class="me-3">📦</span>
                                                    <div>
                                                        <div>{{ __('app.menu_products') }}</div>
                                                        <small class="text-muted">{{ __('app.manage_products') }}</small>
                                                    </div>
                                                </div>
                                            </a>
                                            <a href="#" class="list-group-item list-group-item-action">
                                                <div class="d-flex align-items-center">
                                                    <span class="me-3">📋</span>
                                                    <div>
                                                        <div>{{ __('app.menu_orders') }}</div>
                                                        <small class="text-muted">{{ __('app.manage_orders') }}</small>
                                                    </div>
                                                </div>
                                            </a>
                                            <a href="#" class="list-group-item list-group-item-action">
                                                <div class="d-flex align-items-center">
                                                    <span class="me-3">📊</span>
                                                    <div>
                                                        <div>{{ __('app.menu_reports') }}</div>
                                                        <small class="text-muted">{{ __('app.view_reports') }}</small>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Stats Cards -->
                        <div class="row mt-4">
                            <div class="col-sm-6 col-lg-3">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center">
                                            <div class="subheader">{{ __('app.total_customers') }}</div>
                                            <div class="ms-auto lh-1">
                                                <div class="dropdown">
                                                    <a class="dropdown-toggle text-muted" href="#" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">{{ __('app.last_7_days') }}</a>
                                                    <div class="dropdown-menu dropdown-menu-end">
                                                        <a class="dropdown-item active" href="#">{{ __('app.last_7_days') }}</a>
                                                        <a class="dropdown-item" href="#">{{ __('app.last_30_days') }}</a>
                                                        <a class="dropdown-item" href="#">{{ __('app.last_3_months') }}</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="h1 mb-3">75</div>
                                        <div class="d-flex mb-2">
                                            <div>{{ __('app.conversion_rate') }}</div>
                                            <div class="ms-auto">
                                                <span class="text-green d-inline-flex align-items-center lh-1">
                                                    7% <svg xmlns="http://www.w3.org/2000/svg" class="icon ms-1" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><polyline points="3,17 9,11 13,15 21,7"/><polyline points="14,7 21,7 21,14"/></svg>
                                                </span>
                                            </div>
                                        </div>
                                        <div class="progress progress-sm">
                                            <div class="progress-bar bg-primary" style="width: 75%" role="progressbar" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100" aria-label="75% Complete">
                                                <span class="visually-hidden">75% Complete</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-sm-6 col-lg-3">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center">
                                            <div class="subheader">{{ __('app.total_orders') }}</div>
                                            <div class="ms-auto lh-1">
                                                <div class="dropdown">
                                                    <a class="dropdown-toggle text-muted" href="#" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">{{ __('app.last_7_days') }}</a>
                                                    <div class="dropdown-menu dropdown-menu-end">
                                                        <a class="dropdown-item active" href="#">{{ __('app.last_7_days') }}</a>
                                                        <a class="dropdown-item" href="#">{{ __('app.last_30_days') }}</a>
                                                        <a class="dropdown-item" href="#">{{ __('app.last_3_months') }}</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="h1 mb-3">132</div>
                                        <div class="d-flex mb-2">
                                            <div>{{ __('app.growth_rate') }}</div>
                                            <div class="ms-auto">
                                                <span class="text-green d-inline-flex align-items-center lh-1">
                                                    12% <svg xmlns="http://www.w3.org/2000/svg" class="icon ms-1" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><polyline points="3,17 9,11 13,15 21,7"/><polyline points="14,7 21,7 21,14"/></svg>
                                                </span>
                                            </div>
                                        </div>
                                        <div class="progress progress-sm">
                                            <div class="progress-bar bg-green" style="width: 84%" role="progressbar" aria-valuenow="84" aria-valuemin="0" aria-valuemax="100" aria-label="84% Complete">
                                                <span class="visually-hidden">84% Complete</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-sm-6 col-lg-3">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center">
                                            <div class="subheader">{{ __('app.total_revenue') }}</div>
                                            <div class="ms-auto lh-1">
                                                <div class="dropdown">
                                                    <a class="dropdown-toggle text-muted" href="#" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">{{ __('app.last_7_days') }}</a>
                                                    <div class="dropdown-menu dropdown-menu-end">
                                                        <a class="dropdown-item active" href="#">{{ __('app.last_7_days') }}</a>
                                                        <a class="dropdown-item" href="#">{{ __('app.last_30_days') }}</a>
                                                        <a class="dropdown-item" href="#">{{ __('app.last_3_months') }}</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="h1 mb-3">$4,300</div>
                                        <div class="d-flex mb-2">
                                            <div>{{ __('app.vs_previous_period') }}</div>
                                            <div class="ms-auto">
                                                <span class="text-green d-inline-flex align-items-center lh-1">
                                                    8% <svg xmlns="http://www.w3.org/2000/svg" class="icon ms-1" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><polyline points="3,17 9,11 13,15 21,7"/><polyline points="14,7 21,7 21,14"/></svg>
                                                </span>
                                            </div>
                                        </div>
                                        <div class="progress progress-sm">
                                            <div class="progress-bar bg-yellow" style="width: 65%" role="progressbar" aria-valuenow="65" aria-valuemin="0" aria-valuemax="100" aria-label="65% Complete">
                                                <span class="visually-hidden">65% Complete</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-sm-6 col-lg-3">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center">
                                            <div class="subheader">{{ __('app.active_users') }}</div>
                                            <div class="ms-auto lh-1">
                                                <div class="dropdown">
                                                    <a class="dropdown-toggle text-muted" href="#" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">{{ __('app.last_7_days') }}</a>
                                                    <div class="dropdown-menu dropdown-menu-end">
                                                        <a class="dropdown-item active" href="#">{{ __('app.last_7_days') }}</a>
                                                        <a class="dropdown-item" href="#">{{ __('app.last_30_days') }}</a>
                                                        <a class="dropdown-item" href="#">{{ __('app.last_3_months') }}</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="h1 mb-3">25</div>
                                        <div class="d-flex mb-2">
                                            <div>{{ __('app.vs_previous_period') }}</div>
                                            <div class="ms-auto">
                                                <span class="text-red d-inline-flex align-items-center lh-1">
                                                    -1% <svg xmlns="http://www.w3.org/2000/svg" class="icon ms-1" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><polyline points="3,7 9,13 13,9 21,17"/><polyline points="14,17 21,17 21,10"/></svg>
                                                </span>
                                            </div>
                                        </div>
                                        <div class="progress progress-sm">
                                            <div class="progress-bar bg-red" style="width: 45%" role="progressbar" aria-valuenow="45" aria-valuemin="0" aria-valuemax="100" aria-label="45% Complete">
                                                <span class="visually-hidden">45% Complete</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="empty">
                            <div class="empty-img"><img src="{{ asset('tabler/static/illustrations/undraw_printing_invoices_5r4r.svg') }}" height="128" alt="">
                            </div>
                            <p class="empty-title">{{ __('app.no_project_selected') }}</p>
                            <p class="empty-subtitle text-muted">
                                {{ __('app.no_project_description') }}
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
