<div>
    <div class="nav-item dropdown" style="position: relative;">
        <a href="#" 
           class="nav-link d-flex lh-1 text-reset p-0" 
           aria-label="Open user menu"
           wire:click="toggleDropdown"
           aria-expanded="{{ $isDropdownOpen ? 'true' : 'false' }}">
            <span class="avatar avatar-sm" style="background-image: url('https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name ?? 'User') }}&background=random')"></span>
            <div class="d-none d-xl-block ps-2">
                <div>{{ auth()->user()->name ?? 'User' }}</div>
                <div class="mt-1 small text-muted">{{ auth()->user()->email ?? 'user@theboxpos.com' }}</div>
            </div>
        </a>
        
        @if($isDropdownOpen)
        <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow show" 
             style="position: absolute; top: 100%; right: 0; z-index: 1050; display: block;">
            <a href="/devices" class="dropdown-item" wire:navigate wire:click="closeDropdown">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon dropdown-item-icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                    <rect x="7" y="4" width="10" height="16" rx="1"/>
                    <path d="M11 5h2"/>
                    <circle cx="12" cy="17" r="1"/>
                </svg>
                {{ __('app.manage_devices') }}
            </a>
            <div class="dropdown-divider"></div>
            <livewire:logout-button :button-class="'dropdown-item'" />
        </div>
        @endif
    </div>

    {{-- Click outside to close dropdown --}}
    @if($isDropdownOpen)
    <div class="position-fixed top-0 start-0 w-100 h-100" 
         style="z-index: 1040;" 
         wire:click="closeDropdown"></div>
    @endif
</div>
