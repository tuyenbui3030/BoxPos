<div>
    @if($show)
    <!-- Confirmation Modal -->
    <div class="modal show d-flex align-items-center justify-content-center" 
         style="display: block; background-color: rgba(0,0,0,0.5); position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; z-index: 1050;" 
         tabindex="-1">
        <div class="modal-dialog {{ $size }}" role="document" style="margin: 0;">
            <div class="modal-content">
                <button type="button" class="btn-close" wire:click="cancel" aria-label="Close"></button>
                
                @if($icon === 'danger')
                    <div class="modal-status bg-danger"></div>
                @elseif($icon === 'warning')
                    <div class="modal-status bg-warning"></div>
                @elseif($icon === 'info')
                    <div class="modal-status bg-info"></div>
                @else
                    <div class="modal-status bg-primary"></div>
                @endif
                
                <div class="modal-body text-center py-4">
                    @if($icon === 'danger')
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon mb-2 text-danger icon-lg" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                            <path d="M12 9v2m0 4v.01"/>
                            <path d="M5 19h14a2 2 0 0 0 1.84 -2.75l-7.1 -12.25a2 2 0 0 0 -3.5 0l-7.1 12.25a2 2 0 0 0 1.75 2.75"/>
                        </svg>
                    @elseif($icon === 'warning')
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon mb-2 text-warning icon-lg" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                            <circle cx="12" cy="12" r="9"/>
                            <line x1="12" y1="8" x2="12" y2="12"/>
                            <line x1="12" y1="16" x2="12.01" y2="16"/>
                        </svg>
                    @elseif($icon === 'info')
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon mb-2 text-info icon-lg" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                            <circle cx="12" cy="12" r="9"/>
                            <line x1="12" y1="8" x2="12.01" y2="8"/>
                            <line x1="12" y1="12" x2="12" y2="16"/>
                        </svg>
                    @else
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon mb-2 text-primary icon-lg" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                            <circle cx="12" cy="12" r="9"/>
                            <line x1="12" y1="8" x2="12.01" y2="8"/>
                            <line x1="12" y1="12" x2="12" y2="16"/>
                        </svg>
                    @endif
                    
                    <h3>{{ $title }}</h3>
                    <div class="text-secondary">{{ $message }}</div>
                </div>
                
                <div class="modal-footer">
                    <div class="w-100">
                        <div class="row">
                            <div class="col">
                                <button type="button" 
                                        class="btn w-100" 
                                        wire:click="cancel">
                                    {{ $cancelText }}
                                </button>
                            </div>
                            <div class="col">
                                <button type="button" 
                                        class="btn {{ $confirmButtonClass }} w-100" 
                                        wire:click="confirm"
                                        wire:loading.attr="disabled"
                                        wire:target="confirm">
                                    <span wire:loading.remove wire:target="confirm">{{ $confirmText }}</span>
                                    <span wire:loading wire:target="confirm">
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
