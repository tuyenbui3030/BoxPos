<div class="page-wrapper">
    <!-- Page header -->
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <h2 class="page-title">
                        Select Your Project
                    </h2>
                    <div class="text-muted mt-1">Choose a project to manage. You can switch between projects anytime.</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Page body -->
    <div class="page-body">
        <div class="container-xl">
            @if($this->hasNoStores)
                <!-- No Projects Available -->
                <div class="empty">
                    <div class="empty-img">
                        <img src="{{ asset('static/illustrations/undraw_printing_invoices_5r4r.svg') }}" height="128" alt="">
                    </div>
                    <p class="empty-title">No projects available</p>
                    <p class="empty-subtitle text-muted">
                        You don't have access to any projects yet. Please contact your administrator to get access.
                    </p>
                    <div class="empty-action">
                        <button wire:click="refreshStores" class="btn btn-primary">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                <path d="M4 4v5h.582m15.356 2a8.001 8.001 0 0 0 -15.356 -2m15.356 2h-4.153m0 0l-.347 .347a1 1 0 0 1 -1.414 0l-.347 -.347m1.761 0h2.347a1 1 0 0 0 .993 -.883l.007 -.117v-2.347m0 0v-1.372a1 1 0 0 0 -.883 -.993l-.117 -.007h-1.372"/>
                            </svg>
                            Refresh
                        </button>
                    </div>
                </div>
            @else
                <!-- Search -->
                @if($stores->count() > 4)
                    <div class="row mb-4">
                        <div class="col-md-6 col-lg-4 mx-auto">
                            <div class="input-icon">
                                <span class="input-icon-addon">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                        <circle cx="10" cy="10" r="7"/>
                                        <line x1="21" y1="21" x2="15" y2="15"/>
                                    </svg>
                                </span>
                                <input wire:model.live="search" type="text" class="form-control" placeholder="Search projects...">
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Projects Grid -->
                @if($filteredStores->count() > 0)
                    <div class="row row-cards">
                        @foreach($filteredStores as $store)
                            <div class="col-sm-6 col-lg-4">
                                <div class="card card-sm">
                                    <div class="card-body">
                                        <div class="row align-items-center">
                                            <div class="col-auto">
                                                <span class="bg-primary text-white avatar">
                                                    {{ substr($store->name, 0, 1) }}
                                                </span>
                                            </div>
                                            <div class="col">
                                                <div class="font-weight-medium">
                                                    {{ $store->name }}
                                                </div>
                                                <div class="text-muted">
                                                    {{ $this->getUserRoleInStore($store) }} access
                                                </div>
                                            </div>
                                            <div class="col-auto">
                                                <span class="badge badge-outline text-{{ $store->status === 'active' ? 'success' : 'secondary' }}">
                                                    {{ ucfirst($store->status) }}
                                                </span>
                                            </div>
                                        </div>
                                        
                                        @if($store->description)
                                            <div class="mt-3">
                                                <div class="text-muted">{{ $store->description }}</div>
                                            </div>
                                        @endif

                                        <div class="mt-3">
                                            <div class="row">
                                                <div class="col">
                                                    <button 
                                                        wire:click="selectStore({{ $store->id }})"
                                                        class="btn btn-primary w-100"
                                                        wire:loading.attr="disabled"
                                                        wire:target="selectStore({{ $store->id }})"
                                                    >
                                                        <span wire:loading.remove wire:target="selectStore({{ $store->id }})">
                                                            Select Project
                                                        </span>
                                                        <span wire:loading wire:target="selectStore({{ $store->id }})">
                                                            <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                                                            Selecting...
                                                        </span>
                                                    </button>
                                                </div>
                                                @if($this->canManageStore($store))
                                                    <div class="col-auto">
                                                        <button 
                                                            wire:click="manageStore({{ $store->id }})"
                                                            class="btn btn-outline-primary"
                                                        >
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                                                <path d="M10.325 4.317c.426 -1.756 2.924 -1.756 3.35 0a1.724 1.724 0 0 0 2.573 1.066c1.543 -.94 3.31 .826 2.37 2.37a1.724 1.724 0 0 0 1.065 2.572c1.756 .426 1.756 2.924 0 3.35a1.724 1.724 0 0 0 -1.066 2.573c.94 1.543 -.826 3.31 -2.37 2.37a1.724 1.724 0 0 0 -2.572 1.065c-.426 1.756 -2.924 1.756 -3.35 0a1.724 1.724 0 0 0 -2.573 -1.066c-1.543 .94 -3.31 -.826 -2.37 -2.37a1.724 1.724 0 0 0 -1.065 -2.572c-1.756 -.426 -1.756 -2.924 0 -3.35a1.724 1.724 0 0 0 1.066 -2.573c-.94 -1.543 .826 -3.31 2.37 -2.37c1 .608 2.296 .07 2.572 -1.065z"/>
                                                                <path d="M9 12a3 3 0 1 0 6 0a3 3 0 0 0 -6 0"/>
                                                            </svg>
                                                        </button>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <!-- No Projects Found -->
                    <div class="empty">
                        <div class="empty-img">
                            <img src="{{ asset('static/illustrations/undraw_printing_invoices_5r4r.svg') }}" height="128" alt="">
                        </div>
                        <p class="empty-title">No projects found</p>
                        <p class="empty-subtitle text-muted">
                            @if($search)
                                No projects match your search criteria.
                            @else
                                You don't have access to any projects yet.
                            @endif
                        </p>
                        @if($search)
                            <div class="empty-action">
                                <button wire:click="$set('search', '')" class="btn btn-primary">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                        <path d="M18 6l-12 12"/>
                                        <path d="M6 6l12 12"/>
                                    </svg>
                                    Clear Search
                                </button>
                            </div>
                        @endif
                    </div>
                @endif

                <!-- Footer Actions -->
                <div class="row mt-4">
                    <div class="col text-center">
                        @if($this->canCreateStores())
                            <a href="#" wire:click="createNewStore" class="btn btn-outline-primary me-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                    <line x1="12" y1="5" x2="12" y2="19"/>
                                    <line x1="5" y1="12" x2="19" y2="12"/>
                                </svg>
                                Create New Project
                            </a>
                        @endif
                        
                        <a href="{{ route('locale.dashboard', ['locale' => app()->getLocale()]) }}" class="btn btn-outline-secondary">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                <path d="M5 12l14 0"/>
                                <path d="M5 12l6 6"/>
                                <path d="M5 12l6 -6"/>
                            </svg>
                            Back to Dashboard
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
