<!-- Store Switcher - Tabler UI -->
<div class="nav-item dropdown" wire:ignore.self>
    @if($currentStore)
        <a href="#" class="nav-link d-flex lh-1 text-reset p-0" data-bs-toggle="dropdown" aria-label="Open store menu">
            <span class="avatar avatar-sm" style="background-image: url({{ asset('static/avatars/store.jpg') }})"></span>
            <div class="d-none d-xl-block ps-2">
                <div>{{ $currentStore->name }}</div>
                <div class="mt-1 small text-muted">{{ ucfirst($this->getUserRole()) }}</div>
            </div>
        </a>

        <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
            <div class="dropdown-header">
                <h6 class="dropdown-header-title">Switch Store</h6>
                <p class="dropdown-header-subtitle">Select a store to manage</p>
            </div>

            @forelse($availableStores as $store)
                <button
                    wire:click="switchStore({{ $store->id }})"
                    class="dropdown-item {{ $store->id === $currentStore->id ? 'active' : '' }}"
                >
                    <div class="d-flex align-items-center">
                        <span class="avatar avatar-sm me-2">
                            {{ substr($store->name, 0, 1) }}
                        </span>
                        <div class="flex-fill">
                            <div class="font-weight-medium">{{ $store->name }}</div>
                            <div class="text-muted small">{{ $store->description ?? 'No description' }}</div>
                            @if($store->pivot)
                                <span class="badge badge-outline text-muted">{{ ucfirst($store->pivot->role ?? 'N/A') }}</span>
                            @endif
                        </div>
                        @if($store->id === $currentStore->id)
                            <span class="text-success">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                    <path d="M5 12l5 5l10 -10"/>
                                </svg>
                            </span>
                        @endif
                    </div>
                </button>
            @empty
                <div class="dropdown-item-text text-muted">
                    No stores available
                </div>
            @endforelse


            <div class="dropdown-divider"></div>
            <button wire:click="goToStoreSelection" class="dropdown-item">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon dropdown-item-icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                    <path d="M3 21l18 0"/>
                    <path d="M5 21v-16a2 2 0 0 1 2 -2h10a2 2 0 0 1 2 2v16"/>
                    <path d="M9 9l2 0"/>
                    <path d="M9 12l4 0"/>
                    <path d="M9 15l6 0"/>
                </svg>
                All Stores
            </button>
            @if($this->canManageStores())
                <button wire:click="goToStoreManagement" class="dropdown-item">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon dropdown-item-icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                        <path d="M10.325 4.317c.426 -1.756 2.924 -1.756 3.35 0a1.724 1.724 0 0 0 2.573 1.066c1.543 -.94 3.31 .826 2.37 2.37a1.724 1.724 0 0 0 1.065 2.572c1.756 .426 1.756 2.924 0 3.35a1.724 1.724 0 0 0 -1.066 2.573c.94 1.543 -.826 3.31 -2.37 2.37a1.724 1.724 0 0 0 -2.572 1.065c-.426 1.756 -2.924 1.756 -3.35 0a1.724 1.724 0 0 0 -2.573 -1.066c-1.543 .94 -3.31 -.826 -2.37 -2.37a1.724 1.724 0 0 0 -1.065 -2.572c-1.756 -.426 -1.756 -2.924 0 -3.35a1.724 1.724 0 0 0 1.066 -2.573c-.94 -1.543 .826 -3.31 2.37 -2.37c1 .608 2.296 .07 2.572 -1.065z"/>
                        <path d="M9 12a3 3 0 1 0 6 0a3 3 0 0 0 -6 0"/>
                    </svg>
                    Manage Stores
                </button>
            @endif
        </div>
    @else
        <a href="#" wire:click="goToStoreSelection" class="nav-link d-flex lh-1 text-reset p-0">
            <span class="avatar avatar-sm bg-danger text-white">!</span>
            <div class="d-none d-xl-block ps-2">
                <div class="text-danger">No Store</div>
                <div class="mt-1 small text-muted">Select store</div>
            </div>
        </a>
    @endif

    <!-- Loading State -->
    <div wire:loading wire:target="switchStore" class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center bg-white bg-opacity-75">
        <div class="spinner-border spinner-border-sm text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>
</div>
