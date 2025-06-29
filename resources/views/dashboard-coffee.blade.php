@extends('layouts.app')

@section('title', 'Coffee Shop Inventory')

@section('content')
<div class="page-wrapper">
    <!-- Page header -->
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <h2 class="page-title">
                        {{ $currentProject['icon'] ?? '☕' }} {{ $currentProject['name'] ?? 'Coffee Shop Inventory' }}
                    </h2>
                    <div class="text-muted mt-1">{{ $currentProject['description'] ?? 'Coffee shop inventory management system' }}</div>
                </div>
                <div class="col-auto ms-auto d-print-none">
                    <div class="btn-list">
                        <a href="#" class="btn btn-primary d-none d-sm-inline-block">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                <line x1="12" y1="5" x2="12" y2="19"/>
                                <line x1="5" y1="12" x2="19" y2="12"/>
                            </svg>
                            Add Inventory
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Page body -->
    <div class="page-body">
        <div class="container-xl">
            <!-- Inventory Stats -->
            <div class="row row-deck row-cards">
                <div class="col-sm-6 col-lg-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="subheader">Coffee Beans</div>
                                <div class="ms-auto">
                                    <span class="status-dot status-dot-animated bg-green"></span>
                                </div>
                            </div>
                            <div class="h1 mb-3">245 kg</div>
                            <div class="d-flex mb-2">
                                <div>Stock level</div>
                                <div class="ms-auto">
                                    <span class="text-green d-inline-flex align-items-center lh-1">
                                        Good
                                    </span>
                                </div>
                            </div>
                            <div class="progress progress-sm">
                                <div class="progress-bar bg-success" style="width: 78%" role="progressbar" aria-valuenow="78" aria-valuemin="0" aria-valuemax="100">
                                    <span class="visually-hidden">78% Complete</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-lg-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="subheader">Milk & Dairy</div>
                                <div class="ms-auto">
                                    <span class="status-dot status-dot-animated bg-yellow"></span>
                                </div>
                            </div>
                            <div class="h1 mb-3">45 L</div>
                            <div class="d-flex mb-2">
                                <div>Stock level</div>
                                <div class="ms-auto">
                                    <span class="text-yellow d-inline-flex align-items-center lh-1">
                                        Low
                                    </span>
                                </div>
                            </div>
                            <div class="progress progress-sm">
                                <div class="progress-bar bg-warning" style="width: 35%" role="progressbar" aria-valuenow="35" aria-valuemin="0" aria-valuemax="100">
                                    <span class="visually-hidden">35% Complete</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-lg-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="subheader">Pastries</div>
                                <div class="ms-auto">
                                    <span class="status-dot status-dot-animated bg-green"></span>
                                </div>
                            </div>
                            <div class="h1 mb-3">127 pcs</div>
                            <div class="d-flex mb-2">
                                <div>Fresh items</div>
                                <div class="ms-auto">
                                    <span class="text-green d-inline-flex align-items-center lh-1">
                                        Fresh
                                    </span>
                                </div>
                            </div>
                            <div class="progress progress-sm">
                                <div class="progress-bar bg-success" style="width: 85%" role="progressbar" aria-valuenow="85" aria-valuemin="0" aria-valuemax="100">
                                    <span class="visually-hidden">85% Complete</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-lg-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="subheader">Supplies</div>
                                <div class="ms-auto">
                                    <span class="status-dot status-dot-animated bg-red"></span>
                                </div>
                            </div>
                            <div class="h1 mb-3">23 items</div>
                            <div class="d-flex mb-2">
                                <div>Stock level</div>
                                <div class="ms-auto">
                                    <span class="text-red d-inline-flex align-items-center lh-1">
                                        Critical
                                    </span>
                                </div>
                            </div>
                            <div class="progress progress-sm">
                                <div class="progress-bar bg-danger" style="width: 15%" role="progressbar" aria-valuenow="15" aria-valuemin="0" aria-valuemax="100">
                                    <span class="visually-hidden">15% Complete</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Inventory Management -->
            <div class="row row-deck row-cards mt-4">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Current Inventory</h3>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-vcenter card-table">
                                <thead>
                                    <tr>
                                        <th>Item</th>
                                        <th>Category</th>
                                        <th>Stock</th>
                                        <th>Status</th>
                                        <th>Last Updated</th>
                                        <th class="w-1"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>
                                            <div class="d-flex py-1 align-items-center">
                                                <span class="avatar me-2" style="background-image: url({{ asset('static/avatars/coffee-bean.jpg') }})"></span>
                                                <div class="flex-fill">
                                                    <div class="font-weight-medium">Arabica Beans</div>
                                                    <div class="text-muted">Premium quality</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-muted">Coffee Beans</td>
                                        <td class="text-muted">145 kg</td>
                                        <td>
                                            <span class="badge bg-success me-1"></span>
                                            In Stock
                                        </td>
                                        <td class="text-muted">2 hours ago</td>
                                        <td>
                                            <div class="btn-list flex-nowrap">
                                                <a href="#" class="btn">
                                                    Edit
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="d-flex py-1 align-items-center">
                                                <span class="avatar me-2" style="background-image: url({{ asset('static/avatars/milk.jpg') }})"></span>
                                                <div class="flex-fill">
                                                    <div class="font-weight-medium">Whole Milk</div>
                                                    <div class="text-muted">Fresh dairy</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-muted">Dairy</td>
                                        <td class="text-muted">25 L</td>
                                        <td>
                                            <span class="badge bg-warning me-1"></span>
                                            Low Stock
                                        </td>
                                        <td class="text-muted">1 hour ago</td>
                                        <td>
                                            <div class="btn-list flex-nowrap">
                                                <a href="#" class="btn">
                                                    Edit
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="d-flex py-1 align-items-center">
                                                <span class="avatar me-2" style="background-image: url({{ asset('static/avatars/croissant.jpg') }})"></span>
                                                <div class="flex-fill">
                                                    <div class="font-weight-medium">Croissants</div>
                                                    <div class="text-muted">Fresh baked</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-muted">Pastries</td>
                                        <td class="text-muted">47 pcs</td>
                                        <td>
                                            <span class="badge bg-success me-1"></span>
                                            Fresh
                                        </td>
                                        <td class="text-muted">30 minutes ago</td>
                                        <td>
                                            <div class="btn-list flex-nowrap">
                                                <a href="#" class="btn">
                                                    Edit
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="d-flex py-1 align-items-center">
                                                <span class="avatar me-2" style="background-image: url({{ asset('static/avatars/cup.jpg') }})"></span>
                                                <div class="flex-fill">
                                                    <div class="font-weight-medium">Paper Cups</div>
                                                    <div class="text-muted">12oz disposable</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-muted">Supplies</td>
                                        <td class="text-muted">23 pcs</td>
                                        <td>
                                            <span class="badge bg-danger me-1"></span>
                                            Critical
                                        </td>
                                        <td class="text-muted">3 hours ago</td>
                                        <td>
                                            <div class="btn-list flex-nowrap">
                                                <a href="#" class="btn">
                                                    Edit
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Quick Actions</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-12">
                                    <a href="#" class="btn btn-outline-primary w-100 mb-3">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon me-2" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                            <circle cx="12" cy="12" r="9"/>
                                            <line x1="12" y1="8" x2="12" y2="16"/>
                                            <line x1="8" y1="12" x2="16" y2="12"/>
                                        </svg>
                                        Add New Item
                                    </a>
                                </div>
                                <div class="col-12">
                                    <a href="#" class="btn btn-outline-success w-100 mb-3">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon me-2" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                            <path d="M9 11l3 3l8 -8"/>
                                            <path d="M21 12c0 4.97 -4.03 9 -9 9s-9 -4.03 -9 -9s4.03 -9 9 -9c2.12 0 4.07 .74 5.61 1.97"/>
                                        </svg>
                                        Stock Check
                                    </a>
                                </div>
                                <div class="col-12">
                                    <a href="#" class="btn btn-outline-warning w-100 mb-3">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon me-2" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                            <path d="M3 12a9 9 0 1 0 18 0a9 9 0 0 0 -18 0"/>
                                            <path d="M12 7v5l3 3"/>
                                        </svg>
                                        Reorder Alert
                                    </a>
                                </div>
                                <div class="col-12">
                                    <a href="#" class="btn btn-outline-info w-100 mb-3">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon me-2" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                            <path d="M7 7h-1a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-1"/>
                                            <path d="M20.385 6.585a2.1 2.1 0 0 0 -2.97 -2.97l-8.415 8.385v3h3l8.385 -8.415z"/>
                                            <path d="M16 5l3 3"/>
                                        </svg>
                                        Generate Report
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Customers -->
                    <div class="card mt-4">
                        <div class="card-header">
                            <h3 class="card-title">Recent Customers</h3>
                        </div>
                        <div class="card-body p-0">
                            <div class="list-group list-group-flush">
                                <div class="list-group-item">
                                    <div class="row align-items-center">
                                        <div class="col-auto">
                                            <span class="avatar">AB</span>
                                        </div>
                                        <div class="col text-truncate">
                                            <a href="#" class="text-body d-block">Alice Barista</a>
                                            <div class="d-block text-muted text-truncate mt-n1">Regular customer</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="list-group-item">
                                    <div class="row align-items-center">
                                        <div class="col-auto">
                                            <span class="avatar">BR</span>
                                        </div>
                                        <div class="col text-truncate">
                                            <a href="#" class="text-body d-block">Bob Roaster</a>
                                            <div class="d-block text-muted text-truncate mt-n1">Supplier</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
