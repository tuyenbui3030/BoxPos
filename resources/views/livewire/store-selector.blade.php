@if($mobile)
    <!-- Mobile Store Selector -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Select Store</h3>
        </div>
        <div class="card-body">
            @if(count($availableStores) > 5)
                <div class="mb-3">
                    <input type="text" 
                           class="form-control" 
                           placeholder="Search stores..."
                           wire:model.live="search">
                </div>
            @endif
            
            <div class="list-group list-group-flush">
                @foreach($this->filteredStores as $store)
                    <a href="#" 
                       class="list-group-item list-group-item-action {{ $currentStore && $currentStore->id == $store['id'] ? 'active' : '' }}"
                       wire:click.prevent="switchStore({{ $store['id'] }})">
                        <div class="d-flex align-items-center">
                            @if($store['logo'])
                                <img src="{{ asset('storage/' . $store['logo']) }}" 
                                     alt="{{ $store['name'] }}" 
                                     class="avatar avatar-sm me-2">
                            @else
                                <div class="avatar avatar-sm me-2">
                                    <div class="avatar-initials">
                                        {{ substr($store['name'], 0, 2) }}
                                    </div>
                                </div>
                            @endif
                            <div class="flex-grow-1">
                                <div class="fw-semibold">{{ $store['name'] }}</div>
                                @if($store['address'])
                                    <div class="text-muted small">{{ $store['address'] }}</div>
                                @endif
                            </div>
                            @if($currentStore && $currentStore->id == $store['id'])
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon text-primary" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                    <path d="M5 12l5 5l10 -10"/>
                                </svg>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </div>
@else
    <!-- Desktop Store Selector -->
    <div class="nav-item dropdown" wire:ignore.self>
        <a href="#" 
           class="nav-link dropdown-toggle d-flex align-items-center" 
           wire:click.prevent="toggleDropdown"
           aria-expanded="{{ $showDropdown ? 'true' : 'false' }}">
            @if($currentStore)
                @if($currentStore->logo)
                    <img src="{{ asset('storage/' . $currentStore->logo) }}" 
                         alt="{{ $currentStore->name }}" 
                         class="avatar avatar-sm me-2">
                @else
                    <div class="avatar avatar-sm me-2">
                        <div class="avatar-initials">
                            {{ substr($currentStore->name, 0, 2) }}
                        </div>
                    </div>
                @endif
                <span class="d-none d-lg-inline">{{ $currentStore->name }}</span>
            @else
                <svg xmlns="http://www.w3.org/2000/svg" class="icon me-2" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                    <path d="M3 21l18 0"/>
                    <path d="M5 21v-16a2 2 0 0 1 2 -2h10a2 2 0 0 1 2 2v16"/>
                    <path d="M9 9l0 4"/>
                    <path d="M12 7l0 6"/>
                    <path d="M15 11l0 2"/>
                </svg>
                <span class="d-none d-lg-inline">Select Store</span>
            @endif
        </a>
        
        <div class="dropdown-menu dropdown-menu-arrow dropdown-menu-end {{ $showDropdown ? 'show' : '' }}"
             style="display: {{ $showDropdown ? 'block' : 'none' }}; min-width: 280px;"
             wire:click.away="closeDropdown">
            <div class="dropdown-header d-flex justify-content-between align-items-center">
                <span>Switch Store</span>
                <small class="text-muted">{{ count($availableStores) }} available</small>
            </div>
            
            @if(count($availableStores) > 5)
                <div class="px-3 py-2">
                    <input type="text" 
                           class="form-control form-control-sm" 
                           placeholder="Search stores..."
                           wire:model.live="search">
                </div>
            @endif
            
            <div style="max-height: 300px; overflow-y: auto;">
                @forelse($this->filteredStores as $store)
                    <a href="#" 
                       class="dropdown-item {{ $currentStore && $currentStore->id == $store['id'] ? 'active' : '' }}"
                       wire:click.prevent="switchStore({{ $store['id'] }})">
                        <div class="d-flex align-items-center">
                            @if($store['logo'])
                                <img src="{{ asset('storage/' . $store['logo']) }}" 
                                     alt="{{ $store['name'] }}" 
                                     class="avatar avatar-sm me-2">
                            @else
                                <div class="avatar avatar-sm me-2">
                                    <div class="avatar-initials">
                                        {{ substr($store['name'], 0, 2) }}
                                    </div>
                                </div>
                            @endif
                            <div class="flex-grow-1">
                                <div class="fw-semibold">{{ $store['name'] }}</div>
                                @if($store['address'])
                                    <div class="text-muted small">{{ Str::limit($store['address'], 30) }}</div>
                                @endif
                            </div>
                            @if($currentStore && $currentStore->id == $store['id'])
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon text-primary ms-2" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                    <path d="M5 12l5 5l10 -10"/>
                                </svg>
                            @endif
                        </div>
                    </a>
                @empty
                    <div class="dropdown-item-text text-center py-3">
                        <div class="text-muted">
                            @if(!empty($search))
                                No stores found matching "{{ $search }}"
                            @else
                                No stores available
                            @endif
                        </div>
                    </div>
                @endforelse
            </div>
            
            @if(count($availableStores) > 0)
                <div class="dropdown-divider"></div>
                <div class="dropdown-item-text text-center">
                    <small class="text-muted">
                        You have access to {{ count($availableStores) }} store{{ count($availableStores) !== 1 ? 's' : '' }}
                    </small>
                </div>
            @endif
        </div>
    </div>
@endif