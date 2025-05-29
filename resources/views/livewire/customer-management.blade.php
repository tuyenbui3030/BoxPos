<div class="row">
    @if (session()->has('success'))
        <div class="col-12">
            <div class="alert alert-success alert-dismissible" role="alert">
                <div class="d-flex">
                    <div>
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon alert-icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                            <path d="M5 12l5 5l10 -10"/>
                        </svg>
                    </div>
                    <div>{{ session('success') }}</div>
                </div>
                <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
            </div>
        </div>
    @endif

    @if (session()->has('info'))
        <div class="col-12">
            <div class="alert alert-info alert-dismissible" role="alert">
                <div class="d-flex">
                    <div>
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon alert-icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                            <circle cx="12" cy="12" r="9"/>
                            <line x1="12" y1="8" x2="12.01" y2="8"/>
                            <polyline points="11,12 12,12 12,16 13,16"/>
                        </svg>
                    </div>
                    <div>{{ session('info') }}</div>
                </div>
                <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
            </div>
        </div>
    @endif

    <!-- Filter Sidebar -->
    <div class="col-lg-3 {{ $showFilters ? '' : 'd-none' }}">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title">Filters</h3>
                <button wire:click="clearFilters" class="btn btn-sm btn-outline-secondary">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-sm" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                        <path d="M20 11a8.1 8.1 0 0 0 -15.5 -2m-.5 -4v4h4"/>
                        <path d="M4 13a8.1 8.1 0 0 0 15.5 2m.5 4v-4h-4"/>
                    </svg>
                    Clear All
                </button>
            </div>
            <div class="card-body">
                <!-- Customer Group Filter -->
                <div class="mb-3">
                    <label class="form-label">Customer Group</label>
                    <select wire:model.live="filterCustomerGroup" class="form-select">
                        <option value="">All Groups</option>
                        @foreach($customerGroups as $group)
                            <option value="{{ $group }}">{{ $group }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Customer Type Filter -->
                <div class="mb-3">
                    <label class="form-label">Customer Type</label>
                    <div class="form-selectgroup">
                        <label class="form-selectgroup-item">
                            <input wire:model.live="filterCustomerType" type="radio" name="customerType" value="all" class="form-selectgroup-input">
                            <span class="form-selectgroup-label">All</span>
                        </label>
                        <label class="form-selectgroup-item">
                            <input wire:model.live="filterCustomerType" type="radio" name="customerType" value="individual" class="form-selectgroup-input">
                            <span class="form-selectgroup-label">Individual</span>
                        </label>
                        <label class="form-selectgroup-item">
                            <input wire:model.live="filterCustomerType" type="radio" name="customerType" value="company" class="form-selectgroup-input">
                            <span class="form-selectgroup-label">Company</span>
                        </label>
                    </div>
                </div>

                <!-- Gender Filter -->
                <div class="mb-3">
                    <label class="form-label">Gender</label>
                    <div class="form-selectgroup">
                        <label class="form-selectgroup-item">
                            <input wire:model.live="filterGender" type="radio" name="gender" value="all" class="form-selectgroup-input">
                            <span class="form-selectgroup-label">All</span>
                        </label>
                        <label class="form-selectgroup-item">
                            <input wire:model.live="filterGender" type="radio" name="gender" value="male" class="form-selectgroup-input">
                            <span class="form-selectgroup-label">Male</span>
                        </label>
                        <label class="form-selectgroup-item">
                            <input wire:model.live="filterGender" type="radio" name="gender" value="female" class="form-selectgroup-input">
                            <span class="form-selectgroup-label">Female</span>
                        </label>
                    </div>
                </div>

                <!-- Last Transaction Range -->
                <div class="mb-3">
                    <label class="form-label">Last Transaction Range</label>
                    <div class="row">
                        <div class="col-6">
                            <input wire:model.live="filterLastTransactionFrom" type="date" class="form-control" placeholder="From">
                        </div>
                        <div class="col-6">
                            <input wire:model.live="filterLastTransactionTo" type="date" class="form-control" placeholder="To">
                        </div>
                    </div>
                </div>

                <!-- Total Sales Range -->
                <div class="mb-3">
                    <label class="form-label">Total Sales Range</label>
                    <div class="row">
                        <div class="col-6">
                            <input wire:model.live="filterSalesFrom" type="number" step="0.01" class="form-control" placeholder="From">
                        </div>
                        <div class="col-6">
                            <input wire:model.live="filterSalesTo" type="number" step="0.01" class="form-control" placeholder="To">
                        </div>
                    </div>
                </div>

                <!-- Current Debt Filter -->
                <div class="mb-3">
                    <label class="form-check">
                        <input wire:model.live="filterOnlyWithDebt" type="checkbox" class="form-check-input">
                        <span class="form-check-label">Only customers with debt</span>
                    </label>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="col-lg-{{ $showFilters ? '9' : '12' }}">
        <!-- Top Action Bar -->
        <div class="card">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col">
                        <div class="input-group">
                            <span class="input-group-text">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="10" cy="10" r="7"/>
                                    <line x1="21" y1="21" x2="15" y2="15"/>
                                </svg>
                            </span>
                            <input wire:model.live.debounce.300ms="search" type="text" class="form-control" placeholder="Search by code, name, or phone number...">
                        </div>
                    </div>
                    <div class="col-auto">
                        <button wire:click="toggleFilters" class="btn btn-outline-secondary">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="4" y1="21" x2="4" y2="14"/>
                                <line x1="4" y1="10" x2="4" y2="3"/>
                                <line x1="12" y1="21" x2="12" y2="12"/>
                                <line x1="12" y1="8" x2="12" y2="3"/>
                                <line x1="20" y1="21" x2="20" y2="16"/>
                                <line x1="20" y1="12" x2="20" y2="3"/>
                                <line x1="1" y1="14" x2="7" y2="14"/>
                                <line x1="9" y1="8" x2="15" y2="8"/>
                                <line x1="17" y1="16" x2="23" y2="16"/>
                            </svg>
                            {{ $showFilters ? 'Hide Filters' : 'Show Filters' }}
                        </button>
                    </div>
                    <div class="col-auto">
                        <div class="btn-list">
                            <button wire:click="$dispatch('open-create-customer-modal')" class="btn btn-primary">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                    <line x1="12" y1="5" x2="12" y2="19"/>
                                    <line x1="5" y1="12" x2="19" y2="12"/>
                                </svg>
                                Add Customer
                            </button>
                            <button wire:click="importCustomers" class="btn btn-outline-primary">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                    <path d="M14 3v4a1 1 0 0 0 1 1h4"/>
                                    <path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z"/>
                                    <path d="M12 11v6"/>
                                    <path d="M9 14l3 -3l3 3"/>
                                </svg>
                                Import File
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Customers Table -->
        <div class="card mt-3">
            <div class="table-responsive">
                <table class="table table-vcenter card-table">
                    <thead>
                        <tr>
                            <th>Customer Code</th>
                            <th>Customer Name</th>
                            <th>Phone Number</th>
                            <th>Current Debt</th>
                            <th>Total Sales</th>
                            <th>Total Sales - Returns</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($customers as $customer)
                            <tr>
                                <td>
                                    <span class="text-secondary">{{ $customer->customer_code }}</span>
                                </td>
                                <td>
                                    <div class="d-flex py-1 align-items-center">
                                        <span class="avatar me-2" style="background-image: url('https://ui-avatars.com/api/?name={{ urlencode($customer->customer_name) }}&background=random')"></span>
                                        <div class="flex-fill">
                                            <div class="font-weight-medium">{{ $customer->customer_name }}</div>
                                            <div class="d-flex align-items-center flex-wrap gap-1 mt-1">
                                                <div class="d-flex align-items-center text-muted">
                                                    @if($customer->customer_type === 'company')
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-sm me-1" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                                            <line x1="3" y1="21" x2="21" y2="21"/>
                                                            <line x1="5" y1="21" x2="5" y2="12"/>
                                                            <line x1="9" y1="21" x2="9" y2="9"/>
                                                            <line x1="13" y1="21" x2="13" y2="7"/>
                                                            <line x1="17" y1="21" x2="17" y2="4"/>
                                                        </svg>
                                                        <span class="small">Company</span>
                                                    @else
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-sm me-1" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                                            <circle cx="12" cy="7" r="4"/>
                                                            <path d="M6 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2"/>
                                                        </svg>
                                                        <span class="small">Individual</span>
                                                    @endif
                                                </div>
                                                @if($customer->customer_group)
                                                    <span class="badge bg-azure-lt small">{{ $customer->customer_group }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if($customer->phone_number)
                                        <a href="tel:{{ $customer->phone_number }}" class="text-reset">{{ $customer->phone_number }}</a>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($customer->current_debt > 0)
                                        <span class="text-danger fw-bold">${{ $customer->formatted_current_debt }}</span>
                                    @else
                                        <span class="text-muted">$0.00</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="text-success fw-bold">${{ $customer->formatted_total_sales }}</span>
                                </td>
                                <td>
                                    <span class="fw-bold">${{ $customer->formatted_total_sales_minus_returns }}</span>
                                </td>
                                <td>
                                    <div class="btn-list flex-nowrap">
                                        <button wire:click="editCustomer({{ $customer->id }})" class="btn btn-sm btn-outline-primary">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-sm" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                                <path d="M12 15l8.385 -8.415a2.1 2.1 0 0 0 -2.97 -2.97l-8.415 8.385v3h3z"/>
                                                <path d="M16 5l3 3"/>
                                            </svg>
                                            Edit
                                        </button>
                                        <button wire:click="viewCustomer({{ $customer->id }})" class="btn btn-sm btn-outline-secondary">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-sm" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                                <circle cx="12" cy="12" r="2"/>
                                                <path d="M20 12c-2 4-6 6-8 6s-6-2-8-6c2-4 6-6 8-6s6 2 8 6"/>
                                            </svg>
                                            View
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <div class="empty">
                                        <div class="empty-icon">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                                <circle cx="12" cy="7" r="4"/>
                                                <path d="M6 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2"/>
                                            </svg>
                                        </div>
                                        <p class="empty-title">No customers found</p>
                                        <p class="empty-subtitle text-muted">
                                            Try adjusting your search or filter criteria
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($customers->hasPages())
                <div class="card-footer d-flex align-items-center">
                    <p class="m-0 text-muted">
                        Showing {{ $customers->firstItem() }} to {{ $customers->lastItem() }} of {{ $customers->total() }} results
                    </p>
                    <div class="m-0 ms-auto">
                        {{ $customers->links('custom.pagination') }}
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Create Customer Modal -->
    <livewire:create-customer />
</div>
