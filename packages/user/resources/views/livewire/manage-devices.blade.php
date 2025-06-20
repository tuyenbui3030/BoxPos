<div>
    <!-- Flash Messages -->
    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <div class="d-flex">
                <div>
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon alert-icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                        <path d="M5 12l5 5l10 -10"/>
                    </svg>
                </div>
                <div>{{ session('success') }}</div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <div class="d-flex">
                <div>
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon alert-icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                        <circle cx="12" cy="12" r="9"/>
                        <line x1="12" y1="8" x2="12" y2="12"/>
                        <line x1="12" y1="16" x2="12.01" y2="16"/>
                    </svg>
                </div>
                <div>{{ session('error') }}</div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Page Actions -->
    <div class="row">
        <div class="col-auto ms-auto">
            <div class="btn-list">
                <button wire:click="revokeAllOtherDevices" 
                        wire:confirm="Are you sure you want to revoke access for all other devices? This will log out all other sessions."
                        class="btn btn-outline-danger">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                        <path d="M3 12a9 9 0 1 0 18 0a9 9 0 0 0 -18 0"/>
                        <path d="M9 12l6 0"/>
                    </svg>
                    Revoke All Other Devices
                </button>
            </div>
        </div>
    </div>

    <!-- Devices List -->
    <div class="row row-cards">
        @forelse($devices as $device)
            <div class="col-md-6 col-lg-4">
                <div class="card">
                    <div class="card-status-top {{ $device->is_current_device ? 'bg-green' : ($device->is_trusted ? 'bg-blue' : 'bg-yellow') }}"></div>
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="me-3">
                                @if($device->device_type === 'mobile')
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-lg text-muted" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                        <rect x="7" y="4" width="10" height="16" rx="1"/>
                                        <line x1="11" y1="5" x2="13" y2="5"/>
                                        <line x1="12" y1="17" x2="12.01" y2="17"/>
                                    </svg>
                                @elseif($device->device_type === 'tablet')
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-lg text-muted" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                        <rect x="4" y="3" width="16" height="18" rx="2"/>
                                        <line x1="12" y1="17" x2="12.01" y2="17"/>
                                    </svg>
                                @else
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-lg text-muted" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                        <rect x="3" y="4" width="18" height="12" rx="1"/>
                                        <line x1="7" y1="20" x2="17" y2="20"/>
                                        <line x1="9" y1="16" x2="15" y2="16"/>
                                    </svg>
                                @endif
                            </div>
                            <div class="flex-fill">
                                <div class="font-weight-medium">{{ $device->device_name ?: 'Unknown Device' }}</div>
                                <div class="text-muted">{{ $device->device_info }}</div>
                            </div>
                            @if($device->is_current_device)
                                <span class="badge bg-green text-white">Current</span>
                            @elseif($device->is_trusted)
                                <span class="badge bg-blue text-white">Trusted</span>
                            @endif
                        </div>

                        <div class="mb-2">
                            <small class="text-muted">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-sm me-1" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                    <circle cx="12" cy="12" r="2"/>
                                    <path d="M20 12c-2 4-6 6-8 6s-6-2-8-6c2-4 6-6 8-6s6 2 8 6"/>
                                </svg>
                                IP: {{ $device->ip_address }}
                            </small>
                        </div>

                        <div class="mb-2">
                            <small class="text-muted">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-sm me-1" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                    <circle cx="12" cy="12" r="9"/>
                                    <polyline points="12,7 12,12 15,15"/>
                                </svg>
                                Last active: {{ $device->last_activity ? $device->last_activity->diffForHumans() : 'Never' }}
                            </small>
                        </div>

                        <div class="mb-3">
                            <small class="text-muted">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-sm me-1" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                    <rect x="3" y="5" width="18" height="14" rx="2"/>
                                    <polyline points="3,7 12,13 21,7"/>
                                </svg>
                                Last login: {{ $device->last_login_at ? $device->last_login_at->diffForHumans() : 'Never' }}
                            </small>
                        </div>

                        @if(!$device->is_current_device)
                            <div class="card-actions">
                                @if($device->is_trusted)
                                    <button wire:click="untrustDevice({{ $device->id }})" class="btn btn-sm btn-outline-warning">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-sm" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                            <path d="M12 3a12 12 0 0 0 8.5 3a12 12 0 0 1 -8.5 15a12 12 0 0 1 -8.5 -15a12 12 0 0 0 8.5 -3"/>
                                            <path d="M9 12l2 2l4 -4"/>
                                        </svg>
                                        Untrust
                                    </button>
                                @else
                                    <button wire:click="trustDevice({{ $device->id }})" class="btn btn-sm btn-outline-primary">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-sm" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                            <path d="M12 3a12 12 0 0 0 8.5 3a12 12 0 0 1 -8.5 15a12 12 0 0 1 -8.5 -15a12 12 0 0 0 8.5 -3"/>
                                        </svg>
                                        Trust
                                    </button>
                                @endif
                                
                                <button wire:click="confirmRemoveDevice({{ $device->id }})" class="btn btn-sm btn-outline-danger">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-sm" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                        <polyline points="3 6 5 6 21 6"/>
                                        <path d="M19 6v14a2 2 0 0 1 -2 2h-10a2 2 0 0 1 -2 -2v-14m3 0v-2a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v2"/>
                                    </svg>
                                    Remove
                                </button>
                            </div>
                        @else
                            <div class="card-actions">
                                <small class="text-muted">This is your current device</small>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="empty">
                    <div class="empty-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="4" width="18" height="12" rx="1"/>
                            <line x1="7" y1="20" x2="17" y2="20"/>
                            <line x1="9" y1="16" x2="15" y2="16"/>
                        </svg>
                    </div>
                    <p class="empty-title">No devices found</p>
                    <p class="empty-subtitle text-muted">
                        You haven't used "Remember me" on any devices yet.
                    </p>
                </div>
            </div>
        @endforelse
    </div>

    <!-- Confirmation Modal -->
    @if($showConfirmModal)
        <div class="modal modal-blur fade show" style="display: block;" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
                <div class="modal-content">
                    <button type="button" class="btn-close" wire:click="closeModal" aria-label="Close"></button>
                    <div class="modal-status bg-danger"></div>
                    <div class="modal-body text-center py-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon mb-2 text-danger icon-lg" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                            <path d="M12 9v2m0 4v.01"/>
                            <path d="M5 19h14a2 2 0 0 0 1.84 -2.75l-7.1 -12.25a2 2 0 0 0 -3.48 0l-7.1 12.25a2 2 0 0 0 1.75 2.75"/>
                        </svg>
                        <h3>Are you sure?</h3>
                        <div class="text-muted">This will remove the device and log out any active sessions on that device.</div>
                    </div>
                    <div class="modal-footer">
                        <div class="w-100">
                            <div class="row">
                                <div class="col">
                                    <button wire:click="closeModal" class="btn w-100">Cancel</button>
                                </div>
                                <div class="col">
                                    <button wire:click="removeDevice" class="btn btn-danger w-100">Remove Device</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-backdrop fade show"></div>
    @endif
</div>
