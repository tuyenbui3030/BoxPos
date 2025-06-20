<div>
    {{-- Reusable Confirmation Modal --}}
    @if($show)
    <div class="modal show d-flex align-items-center justify-content-center" 
         style="display: block; background-color: rgba(0,0,0,0.5); position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; z-index: 1050;" 
         tabindex="-1"
         x-data="{ show: true }"
         x-show="show"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        <div class="modal-dialog {{ $size }} modal-dialog-centered" role="document" style="margin: 0;">
            <div class="modal-content"
                 x-transition:enter="transition ease-out duration-200 transform"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-150 transform"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95">
                <button type="button" 
                        class="btn-close" 
                        wire:click="cancel" 
                        aria-label="Close"></button>
                <div class="modal-status {{ $this->getModalStatusClass() }}"></div>
                <div class="modal-body text-center py-4">
                    {!! $this->getIconSvg() !!}
                    <h3>{{ $title }}</h3>
                    <div class="text-secondary">
                        {{ $message }}
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="w-100">
                        <div class="row">
                            <div class="col">
                                <button type="button" 
                                        class="btn w-100" 
                                        wire:click="cancel"
                                        wire:loading.attr="disabled"
                                        wire:target="cancel">
                                    <span wire:loading.remove wire:target="cancel">{{ $cancelText }}</span>
                                    <span wire:loading wire:target="cancel">
                                        <span class="spinner-border spinner-border-sm me-1" role="status"></span>
                                        Cancelling...
                                    </span>
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
