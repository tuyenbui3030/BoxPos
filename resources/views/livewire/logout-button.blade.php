<div>
    {{-- Logout Button --}}
    <button type="button" 
            class="{{ $buttonClass }}" 
            wire:click="confirmLogout"
            wire:loading.attr="disabled"
            wire:target="confirmLogout">
        @if($showIcon)
        <svg xmlns="http://www.w3.org/2000/svg" 
             class="icon dropdown-item-icon" 
             width="24" 
             height="24" 
             viewBox="0 0 24 24" 
             stroke-width="2" 
             stroke="currentColor" 
             fill="none" 
             stroke-linecap="round" 
             stroke-linejoin="round">
            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
            <path d="M14 8v-2a2 2 0 0 0 -2 -2h-7a2 2 0 0 0 -2 2v12a2 2 0 0 0 2 2h7a2 2 0 0 0 2 -2v-2"/>
            <path d="M7 12h14l-3 -3m0 6l3 -3"/>
        </svg>
        @endif
        
        <span wire:loading.remove wire:target="confirmLogout">{{ $buttonText }}</span>
        <span wire:loading wire:target="confirmLogout">
            <span class="spinner-border spinner-border-sm me-1" role="status"></span>
            Opening...
        </span>
    </button>

    {{-- Logout Confirmation Modal --}}
    @if($showConfirmModal)
    <div class="modal show d-flex align-items-center justify-content-center" 
         style="display: block; background-color: rgba(0,0,0,0.5); position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; z-index: 1050;" 
         tabindex="-1">
        <div class="modal-dialog modal-sm" role="document" style="margin: 0;">
            <div class="modal-content">
                <button type="button" 
                        class="btn-close" 
                        wire:click="cancelLogout" 
                        aria-label="Close"></button>
                <div class="modal-status bg-danger"></div>
                <div class="modal-body text-center py-4">
                    <svg xmlns="http://www.w3.org/2000/svg" 
                         class="icon mb-2 text-danger icon-lg" 
                         width="24" 
                         height="24" 
                         viewBox="0 0 24 24" 
                         stroke-width="2" 
                         stroke="currentColor" 
                         fill="none" 
                         stroke-linecap="round" 
                         stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                        <path d="M14 8v-2a2 2 0 0 0 -2 -2h-7a2 2 0 0 0 -2 2v12a2 2 0 0 0 2 2h7a2 2 0 0 0 2 -2v-2"/>
                        <path d="M7 12h14l-3 -3m0 6l3 -3"/>
                    </svg>
                    <h3>Confirm Logout</h3>
                    <div class="text-secondary">
                        Are you sure you want to logout? You will need to login again to access your account.
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="w-100">
                        <div class="row">
                            <div class="col">
                                <button type="button" 
                                        class="btn w-100" 
                                        wire:click="cancelLogout">
                                    Cancel
                                </button>
                            </div>
                            <div class="col">
                                <button type="button" 
                                        class="btn btn-danger w-100" 
                                        wire:click="logout"
                                        wire:loading.attr="disabled"
                                        wire:target="logout">
                                    <span wire:loading.remove wire:target="logout">Logout</span>
                                    <span wire:loading wire:target="logout">
                                        <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                                        Processing...
                                    </span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
