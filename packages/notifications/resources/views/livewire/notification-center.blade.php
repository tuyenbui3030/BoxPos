<div class="nav-item dropdown" wire:ignore.self>
    <a href="#" 
       class="nav-link px-0 {{ $showDropdown ? 'show' : '' }}" 
       wire:click.prevent="toggleDropdown"
       tabindex="-1" 
       aria-label="Show notifications"
       data-bs-auto-close="outside"
       aria-expanded="{{ $showDropdown ? 'true' : 'false' }}">
        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M10 5a2 2 0 1 1 4 0a7 7 0 0 1 4 6v3a4 4 0 0 0 2 3h-16a4 4 0 0 0 2 -3v-3a7 7 0 0 1 4 -6"/>
            <path d="M9 17v1a3 3 0 0 0 6 0v-1"/>
        </svg>
        @if($unreadCount > 0)
            <span class="badge bg-red"></span>
        @endif
    </a>

    <div class="dropdown-menu dropdown-menu-arrow dropdown-menu-end dropdown-menu-card {{ $showDropdown ? 'show' : '' }}"
         style="display: {{ $showDropdown ? 'block' : 'none' }};"
         wire:click.away="closeDropdown"
         data-bs-popper="static">
        <div class="card">
            <div class="card-header d-flex">
                <h3 class="card-title">Notifications</h3>
                <div class="btn-close ms-auto" wire:click="closeDropdown"></div>
            </div>
            
            <div class="list-group list-group-flush list-group-hoverable">
                @forelse($notifications as $notification)
                    <div class="list-group-item">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <span class="status-dot {{ is_null($notification['read_at']) ? 'status-dot-animated bg-red' : '' }} d-block"></span>
                            </div>
                            <div class="col text-truncate">
                                <a href="#" class="text-body d-block">{{ $notification['title'] }}</a>
                                <div class="d-block text-secondary text-truncate mt-n1">
                                    {{ $notification['message'] }}
                                </div>
                                <small class="text-muted">
                                    {{ $notification['created_at']->diffForHumans() }}
                                </small>
                            </div>
                            <div class="col-auto">
                                @if(is_null($notification['read_at']))
                                    <a href="#" class="list-group-item-actions" wire:click.prevent="markAsRead({{ $notification['id'] }})">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon text-muted" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M12 17.75l-6.172 3.245l1.179 -6.873l-5 -4.867l6.9 -1l3.086 -6.253l3.086 6.253l6.9 1l-5 4.867l1.179 6.873z"/>
                                        </svg>
                                    </a>
                                @else
                                    <span class="text-muted">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M5 12l5 5l10 -10"/>
                                        </svg>
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="list-group-item">
                        <div class="text-center py-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-lg text-muted mb-2" width="48" height="48" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                <path d="M10 5a2 2 0 0 1 4 0a7 7 0 0 1 4 6v3a4 4 0 0 0 2 3h-16a4 4 0 0 0 2 -3v-3a7 7 0 0 1 4 -6"/>
                                <path d="M9 17v1a3 3 0 0 0 6 0v-1"/>
                            </svg>
                            <p class="text-muted mb-0">No notifications</p>
                        </div>
                    </div>
                @endforelse
            </div>
            
            @if(count($notifications) > 0)
                <div class="card-body">
                    <div class="row">
                        <div class="col">
                            <a href="#" class="btn btn-outline-primary w-100" wire:click.prevent="archiveAll">
                                Archive all
                            </a>
                        </div>
                        <div class="col">
                            <a href="#" class="btn btn-outline-primary w-100" wire:click.prevent="markAllAsRead">
                                Mark all as read
                            </a>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>