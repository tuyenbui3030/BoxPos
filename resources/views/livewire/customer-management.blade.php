<div>

    <!-- Flash Messages -->
    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible mb-3" role="alert">
            <div class="d-flex">
                <div>
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon alert-icon" width="24" height="24"
                        viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round"
                        stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                        <path d="M5 12l5 5l10 -10" />
                    </svg>
                </div>
                <div>{{ session('success') }}</div>
            </div>
            <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
        </div>
    @endif

    @if (session()->has('info'))
        <div class="alert alert-info alert-dismissible mb-3" role="alert">
            <div class="d-flex">
                <div>
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon alert-icon" width="24" height="24"
                        viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round"
                        stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                        <circle cx="12" cy="12" r="9" />
                        <line x1="12" y1="8" x2="12.01" y2="8" />
                        <polyline points="11,12 12,12 12,16 13,16" />
                    </svg>
                </div>
                <div>{{ session('info') }}</div>
            </div>
            <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
        </div>
    @endif

    <div class="row row-deck row-cards">

        <!-- Filter Sidebar -->
        @if ($showFilters)
            <div class="col-lg-3">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Filters</h3>
                        <div class="card-actions">
                            <button wire:click="clearFilters" class="btn btn-outline-primary btn-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-sm" width="24"
                                    height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                    fill="none" stroke-linecap="round" stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                    <path d="M20 11a8.1 8.1 0 0 0 -15.5 -2m-.5 -4v4h4" />
                                    <path d="M4 13a8.1 8.1 0 0 0 15.5 2m.5 4v-4h-4" />
                                </svg>
                                Clear
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Search -->
                        <div class="mb-3">
                            <label class="form-label">Search</label>
                            <div class="input-icon">
                                <span class="input-icon-addon">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24"
                                        viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                                        stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                        <path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0" />
                                        <path d="M21 21l-6 -6" />
                                    </svg>
                                </span>
                                <input wire:model.live.debounce.300ms="search" type="text" class="form-control"
                                    placeholder="Search customers...">
                            </div>
                        </div>

                        <!-- Customer Group Filter -->
                        <div class="mb-3">
                            <label class="form-label">Customer Group</label>
                            <select wire:model.live="filterCustomerGroup" class="form-select">
                                <option value="">All Groups</option>
                                @foreach ($customerGroups as $group)
                                    <option value="{{ $group }}">{{ $group }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Customer Type Filter -->
                        <div class="mb-3">
                            <label class="form-label">Customer Type</label>
                            <div class="form-selectgroup form-selectgroup-boxes d-flex flex-column">
                                <label class="form-selectgroup-item flex-fill">
                                    <input wire:model.live="filterCustomerType" type="radio" name="customerType"
                                        value="all" class="form-selectgroup-input" checked>
                                    <div class="form-selectgroup-label d-flex align-items-center p-3">
                                        <div class="me-3">
                                            <span class="form-selectgroup-check"></span>
                                        </div>
                                        <div>
                                            <strong>All Types</strong>
                                        </div>
                                    </div>
                                </label>
                                <label class="form-selectgroup-item flex-fill">
                                    <input wire:model.live="filterCustomerType" type="radio" name="customerType"
                                        value="individual" class="form-selectgroup-input">
                                    <div class="form-selectgroup-label d-flex align-items-center p-3">
                                        <div class="me-3">
                                            <span class="form-selectgroup-check"></span>
                                        </div>
                                        <div class="me-3">
                                            <span class="avatar avatar-sm bg-primary-lt">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24"
                                                    height="24" viewBox="0 0 24 24" stroke-width="2"
                                                    stroke="currentColor" fill="none" stroke-linecap="round"
                                                    stroke-linejoin="round">
                                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                    <path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0" />
                                                    <path d="M6 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" />
                                                </svg>
                                            </span>
                                        </div>
                                        <div>
                                            <strong>Individual</strong>
                                            <div class="text-muted">Personal customers</div>
                                        </div>
                                    </div>
                                </label>
                                <label class="form-selectgroup-item flex-fill">
                                    <input wire:model.live="filterCustomerType" type="radio" name="customerType"
                                        value="company" class="form-selectgroup-input">
                                    <div class="form-selectgroup-label d-flex align-items-center p-3">
                                        <div class="me-3">
                                            <span class="form-selectgroup-check"></span>
                                        </div>
                                        <div class="me-3">
                                            <span class="avatar avatar-sm bg-success-lt">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24"
                                                    height="24" viewBox="0 0 24 24" stroke-width="2"
                                                    stroke="currentColor" fill="none" stroke-linecap="round"
                                                    stroke-linejoin="round">
                                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                    <path d="M3 21l18 0" />
                                                    <path d="M5 21v-16a2 2 0 0 1 2 -2h10a2 2 0 0 1 2 2v16" />
                                                    <path d="M9 9l0 4" />
                                                    <path d="M12 9l0 4" />
                                                    <path d="M15 9l0 4" />
                                                </svg>
                                            </span>
                                        </div>
                                        <div>
                                            <strong>Company</strong>
                                            <div class="text-muted">Business customers</div>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Gender Filter -->
                        <div class="mb-3">
                            <label class="form-label">Gender</label>
                            <select wire:model.live="filterGender" class="form-select">
                                <option value="all">All Genders</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                                <option value="other">Other</option>
                            </select>
                        </div>

                        <!-- Current Debt Filter -->
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input wire:model.live="filterOnlyWithDebt" type="checkbox" class="form-check-input"
                                    id="filterDebt">
                                <label class="form-check-label" for="filterDebt">Only customers with
                                    debt</label>
                            </div>
                        </div>

                        <!-- Last Transaction Date Range -->
                        <div class="mb-3">
                            <label class="form-label">Last Transaction</label>
                            <div class="input-icon mb-2">
                                <span class="input-icon-addon">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24"
                                        height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                        fill="none" stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                        <path
                                            d="M4 7a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12z" />
                                        <path d="M16 3v4" />
                                        <path d="M8 3v4" />
                                        <path d="M4 11h16" />
                                    </svg>
                                </span>
                                <input id="datepicker-icon-prepend" class="form-control"
                                    placeholder="Select date range" readonly>
                            </div>
                            @if ($filterLastTransactionFrom || $filterLastTransactionTo)
                                <div class="form-text text-muted">
                                    @if ($filterLastTransactionFrom && $filterLastTransactionTo)
                                        {{ $filterLastTransactionFrom }} to {{ $filterLastTransactionTo }}
                                    @elseif($filterLastTransactionFrom)
                                        From: {{ $filterLastTransactionFrom }}
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Main Content -->
        <div class="{{ $showFilters ? 'col-lg-9' : 'col-12' }}">
            <!-- Customers Table -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Customers</h3>
                    <div class="card-actions">
                        <div class="d-flex">
                            @if (!$showFilters)
                                <div class="input-icon me-3">
                                    <span class="input-icon-addon">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24"
                                            height="24" viewBox="0 0 24 24" stroke-width="2"
                                            stroke="currentColor" fill="none" stroke-linecap="round"
                                            stroke-linejoin="round">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                            <path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0" />
                                            <path d="M21 21l-6 -6" />
                                        </svg>
                                    </span>
                                    <input wire:model.live.debounce.300ms="search" type="text"
                                        class="form-control" placeholder="Search customers...">
                                </div>
                            @endif
                            <div class="btn-list">
                                <div class="btn-list">
                                    <button wire:click="toggleFilters" class="btn btn-outline-primary">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24"
                                            height="24" viewBox="0 0 24 24" stroke-width="2"
                                            stroke="currentColor" fill="none" stroke-linecap="round"
                                            stroke-linejoin="round">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                            <path
                                                d="M4 4h16v2.172a2 2 0 0 1 -.586 1.414l-4.414 4.414v5l-6 2v-7l-4.414 -4.414a2 2 0 0 1 -.586 -1.414v-2.172z" />
                                        </svg>
                                        {{ $showFilters ? 'Hide Filters' : 'Show Filters' }}
                                    </button>
                                    <button wire:click="importCustomers" class="btn btn-outline-secondary">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24"
                                            height="24" viewBox="0 0 24 24" stroke-width="2"
                                            stroke="currentColor" fill="none" stroke-linecap="round"
                                            stroke-linejoin="round">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                            <path d="M14 3v4a1 1 0 0 0 1 1h4" />
                                            <path
                                                d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z" />
                                            <path d="M12 11v6" />
                                            <path d="M9 14l3 -3l3 3" />
                                        </svg>
                                        Import
                                    </button>
                                    <button wire:click="$dispatch('open-create-customer-modal')"
                                        class="btn btn-primary">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24"
                                            height="24" viewBox="0 0 24 24" stroke-width="2"
                                            stroke="currentColor" fill="none" stroke-linecap="round"
                                            stroke-linejoin="round">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                            <path d="M12 5l0 14" />
                                            <path d="M5 12l14 0" />
                                        </svg>
                                        Add Customer
                                    </button>
                                </div>
                                <div class="dropdown">
                                    <button class="btn dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-sm" width="24"
                                            height="24" viewBox="0 0 24 24" stroke-width="2"
                                            stroke="currentColor" fill="none" stroke-linecap="round"
                                            stroke-linejoin="round">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                            <path d="M12 3l0 6l4 -4l-4 -4" />
                                            <path d="M12 21l0 -6l-4 4l4 4" />
                                            <path d="M3 12l6 0l-4 -4l-4 4" />
                                            <path d="M21 12l-6 0l4 -4l4 4" />
                                        </svg>
                                        Export
                                    </button>
                                    <div class="dropdown-menu">
                                        <a class="dropdown-item" href="#">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="icon dropdown-item-icon"
                                                width="24" height="24" viewBox="0 0 24 24" stroke-width="2"
                                                stroke="currentColor" fill="none" stroke-linecap="round"
                                                stroke-linejoin="round">
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                <path d="M14 3v4a1 1 0 0 0 1 1h4" />
                                                <path
                                                    d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z" />
                                                <path d="M9 9l1 0" />
                                                <path d="M9 13l6 0" />
                                                <path d="M9 17l6 0" />
                                            </svg>
                                            Export as CSV
                                        </a>
                                        <a class="dropdown-item" href="#">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="icon dropdown-item-icon"
                                                width="24" height="24" viewBox="0 0 24 24" stroke-width="2"
                                                stroke="currentColor" fill="none" stroke-linecap="round"
                                                stroke-linejoin="round">
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                <path d="M14 3v4a1 1 0 0 0 1 1h4" />
                                                <path
                                                    d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z" />
                                                <path d="M9 9l1 0" />
                                                <path d="M9 13l6 0" />
                                                <path d="M9 17l6 0" />
                                            </svg>
                                            Export as Excel
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead>
                            <tr>
                                <th class="w-1">
                                    <input class="form-check-input m-0 align-middle" type="checkbox"
                                        aria-label="Select all customers">
                                </th>
                                <th>Customer</th>
                                <th>Contact</th>
                                <th>Debt</th>
                                <th>Total Sales</th>
                                <th class="w-1"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($customers as $customer)
                                <tr>
                                    <td>
                                        <input class="form-check-input m-0 align-middle" type="checkbox"
                                            aria-label="Select customer">
                                    </td>
                                    <td>
                                        <div class="d-flex py-1 align-items-center">
                                            <span class="avatar me-2"
                                                style="background-image: url('https://ui-avatars.com/api/?name={{ urlencode($customer->customer_name) }}&background=random')"></span>
                                            <div class="flex-fill">
                                                <div class="font-weight-medium">{{ $customer->customer_name }}
                                                </div>
                                                <div class="text-muted">
                                                    <small>{{ $customer->customer_code }}</small>
                                                    @if ($customer->customer_group)
                                                        <span
                                                            class="badge bg-azure-lt ms-1">{{ $customer->customer_group }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            @if ($customer->phone_number)
                                                <div class="text-muted">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-sm me-1"
                                                        width="24" height="24" viewBox="0 0 24 24"
                                                        stroke-width="2" stroke="currentColor" fill="none"
                                                        stroke-linecap="round" stroke-linejoin="round">
                                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                        <path
                                                            d="M5 4h4l2 5l-2.5 1.5a11 11 0 0 0 5 5l1.5 -2.5l5 2v4a2 2 0 0 1 -2 2a16 16 0 0 1 -15 -15a2 2 0 0 1 2 -2" />
                                                    </svg>
                                                    <a href="tel:{{ $customer->phone_number }}"
                                                        class="text-reset">{{ $customer->phone_number }}</a>
                                                </div>
                                            @endif
                                            @if ($customer->email)
                                                <div class="text-muted">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-sm me-1"
                                                        width="24" height="24" viewBox="0 0 24 24"
                                                        stroke-width="2" stroke="currentColor" fill="none"
                                                        stroke-linecap="round" stroke-linejoin="round">
                                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                        <path
                                                            d="M3 7a2 2 0 0 1 2 -2h14a2 2 0 0 1 2 2v10a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2v-10z" />
                                                        <path d="M3 7l9 6l9 -6" />
                                                    </svg>
                                                    <a href="mailto:{{ $customer->email }}"
                                                        class="text-reset">{{ $customer->email }}</a>
                                                </div>
                                            @endif
                                            @if (!$customer->phone_number && !$customer->email)
                                                <span class="text-muted">No contact info</span>
                                            @endif
                                        </div>
                                    </td>

                                    <td>
                                        @if ($customer->current_debt > 0)
                                            <span
                                                class="text-danger fw-bold">${{ $customer->formatted_current_debt }}</span>
                                        @else
                                            <span class="text-muted">$0.00</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span
                                            class="text-success fw-bold">${{ $customer->formatted_total_sales }}</span>
                                        @if ($customer->formatted_total_sales_minus_returns != $customer->formatted_total_sales)
                                            <div class="text-muted">
                                                <small>Net:
                                                    ${{ $customer->formatted_total_sales_minus_returns }}</small>
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-ghost-secondary btn-sm dropdown-toggle"
                                                type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                Actions
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-end">
                                                <a wire:click="viewCustomer({{ $customer->id }})"
                                                    class="dropdown-item" href="#">
                                                    <svg xmlns="http://www.w3.org/2000/svg"
                                                        class="icon dropdown-item-icon" width="24" height="24"
                                                        viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                                        fill="none" stroke-linecap="round"
                                                        stroke-linejoin="round">
                                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                        <path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" />
                                                        <path
                                                            d="M21 12c-2.4 4 -5.4 6 -9 6c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c3.6 0 6.6 2 9 6" />
                                                    </svg>
                                                    View Details
                                                </a>
                                                <a wire:click="editCustomer({{ $customer->id }})"
                                                    class="dropdown-item" href="#">
                                                    <svg xmlns="http://www.w3.org/2000/svg"
                                                        class="icon dropdown-item-icon" width="24" height="24"
                                                        viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                                        fill="none" stroke-linecap="round"
                                                        stroke-linejoin="round">
                                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                        <path
                                                            d="M7 7h-1a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-1" />
                                                        <path
                                                            d="M20.385 6.585a2.1 2.1 0 0 0 -2.97 -2.97l-8.415 8.385v3h3l8.385 -8.415z" />
                                                        <path d="M16 5l3 3" />
                                                    </svg>
                                                    Edit Customer
                                                </a>
                                                <div class="dropdown-divider"></div>
                                                @if ($customer->phone_number)
                                                    <a href="tel:{{ $customer->phone_number }}"
                                                        class="dropdown-item">
                                                        <svg xmlns="http://www.w3.org/2000/svg"
                                                            class="icon dropdown-item-icon" width="24"
                                                            height="24" viewBox="0 0 24 24" stroke-width="2"
                                                            stroke="currentColor" fill="none"
                                                            stroke-linecap="round" stroke-linejoin="round">
                                                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                            <path
                                                                d="M5 4h4l2 5l-2.5 1.5a11 11 0 0 0 5 5l1.5 -2.5l5 2v4a2 2 0 0 1 -2 2a16 16 0 0 1 -15 -15a2 2 0 0 1 2 -2" />
                                                        </svg>
                                                        Call Customer
                                                    </a>
                                                @endif
                                                @if ($customer->email)
                                                    <a href="mailto:{{ $customer->email }}" class="dropdown-item">
                                                        <svg xmlns="http://www.w3.org/2000/svg"
                                                            class="icon dropdown-item-icon" width="24"
                                                            height="24" viewBox="0 0 24 24" stroke-width="2"
                                                            stroke="currentColor" fill="none"
                                                            stroke-linecap="round" stroke-linejoin="round">
                                                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                            <path
                                                                d="M3 7a2 2 0 0 1 2 -2h14a2 2 0 0 1 2 2v10a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2v-10z" />
                                                            <path d="M3 7l9 6l9 -6" />
                                                        </svg>
                                                        Send Email
                                                    </a>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5">
                                        <div class="empty">
                                            <div class="empty-img">
                                                <img src="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTQ0IiBoZWlnaHQ9IjEwOCIgdmlld0JveD0iMCAwIDE0NCAxMDgiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxwYXRoIGQ9Ik0yOC43NTE5IDYyLjEyOTNMNjkuNzUxOSAyMS4xMjkzQzcwLjE0MjQgMjAuNzM4OCA3MC4xNDI0IDIwLjEwNTYgNjkuNzUxOSAxOS43MTUxTDI4Ljc1MTkgLTIxLjI4NDlDMjguMzYxNCAtMjEuNjc1NCAyNy43MjgyIC0yMS42NzU0IDI3LjMzNzcgLTIxLjI4NDlMMjcuMzM3NyAyMC43MTUxQzI3LjcyODIgMjEuMTA1NiAyOC4zNjE0IDIxLjEwNTYgMjguNzUxOSAyMC43MTUxTDI5Ljc1MTkgMTkuNzE1MUwyOC43NTE5IDYyLjEyOTNaIiBmaWxsPSIjZjhmOWZhIi8+CjxwYXRoIGQ9Ik0xMTUuMjQ4IDYyLjEyOTNMNzQuMjQ4MSAyMS4xMjkzQzczLjg1NzYgMjAuNzM4OCA3My44NTc2IDIwLjEwNTYgNzQuMjQ4MSAxOS43MTUxTDExNS4yNDggLTIxLjI4NDlDMTE1LjYzOSAtMjEuNjc1NCAxMTYuMjcyIC0yMS42NzU0IDExNi42NjIgLTIxLjI4NDlMMTE2LjY2MiAyMC43MTUxQzExNi4yNzIgMjEuMTA1NiAxMTUuNjM5IDIxLjEwNTYgMTE1LjI0OCAyMC43MTUxTDExNC4yNDggMTkuNzE1MUwxMTUuMjQ4IDYyLjEyOTNaIiBmaWxsPSIjZjhmOWZhIi8+CjxwYXRoIGQ9Ik0xMDggNjBIODlWNjEuNUg4N0M4NiA2MS41IDg2IDYyIDg2IDYyLjVWNjNWNjMuNUM4NiA2NCA4NiA2NC41IDg3IDY0LjVIODlWNjZIMTA4VjY0LjVIMTEwQzExMSA2NC41IDExMSA2NCA4MSA2My41VjYzVjYyLjVDMTExIDYyIDExMSA2MS41IDExMCA2MS41SDEwOFY2MFoiIGZpbGw9IiNmOGY5ZmEiLz4KPC9zdmc+"
                                                    class="empty-img" alt="">
                                            </div>
                                            <p class="empty-title">No customers found</p>
                                            <p class="empty-subtitle text-muted">
                                                Try adjusting your search or filter criteria, or add a new
                                                customer to get started.
                                            </p>
                                            <div class="empty-action">
                                                <button wire:click="$dispatch('open-create-customer-modal')"
                                                    class="btn btn-primary">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon"
                                                        width="24" height="24" viewBox="0 0 24 24"
                                                        stroke-width="2" stroke="currentColor" fill="none"
                                                        stroke-linecap="round" stroke-linejoin="round">
                                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                        <path d="M12 5l0 14" />
                                                        <path d="M5 12l14 0" />
                                                    </svg>
                                                    Add your first customer
                                                </button>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($customers->count() > 0)
                    <div class="card-footer d-flex align-items-center">
                        <p class="m-0 text-muted">
                            Showing <span
                                class="badge bg-secondary fw-normal text-white">{{ $customers->firstItem() }}</span>
                            to <span
                                class="badge bg-secondary fw-normal text-white">{{ $customers->lastItem() }}</span>
                            of <span class="badge bg-secondary fw-normal text-white">{{ $customers->total() }}</span>
                            results
                        </p>
                        @if ($customers->hasPages())
                            <ul class="pagination m-0 ms-auto">
                                {{ $customers->links('custom.pagination') }}
                            </ul>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>


    <!-- Create Customer Modal -->
    <livewire:create-customer />

    @push('scripts')
        <script src="{{ asset('js/customer-management.js') }}"></script>
    @endpush
