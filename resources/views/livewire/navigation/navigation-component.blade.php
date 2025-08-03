<!-- Navigation Menu -->
<div class="collapse navbar-collapse" id="navbar-menu" wire:ignore.self>
    <div class="d-flex flex-column flex-md-row flex-fill align-items-stretch align-items-md-center">
        <ul class="navbar-nav">
            @foreach($menuItems as $item)
                @if($this->hasPermission($item['permission']))
                    <li class="nav-item {{ !empty($item['children']) ? 'dropdown' : '' }} {{ $this->isRouteActive($item['route']) ? 'active' : '' }}">
                        @if(!empty($item['children']))
                            <!-- Dropdown Menu Item -->
                            <a class="nav-link dropdown-toggle"
                               href="#"
                               wire:click.prevent="toggleDropdown('{{ $item['key'] }}')"
                               role="button"
                               aria-expanded="{{ $this->isDropdownActive($item['key']) ? 'true' : 'false' }}">
                                <span class="nav-link-icon d-md-none d-lg-inline-block">
                                    @include('components.icons.' . $item['icon'])
                                </span>
                                <span class="nav-link-title">
                                    {{ $item['title'] }}
                                </span>
                                @if($item['badge'])
                                    <span class="badge bg-{{ $item['badge']['color'] ?? 'primary' }} ms-2">
                                        {{ $item['badge']['text'] }}
                                    </span>
                                @endif
                            </a>
                            
                            <!-- Dropdown Menu -->
                            <div class="dropdown-menu {{ $this->isDropdownActive($item['key']) ? 'show' : '' }}"
                                 style="display: {{ $this->isDropdownActive($item['key']) ? 'block' : 'none' }};"
                                 wire:click.away="closeDropdown('{{ $item['key'] }}')">
                                @if(count($item['children']) > 6)
                                    <!-- Mega Menu for many items -->
                                    <div class="dropdown-menu-columns">
                                        @php
                                            $chunks = array_chunk($item['children'], ceil(count($item['children']) / 2));
                                        @endphp
                                        @foreach($chunks as $chunk)
                                            <div class="dropdown-menu-column">
                                                @foreach($chunk as $child)
                                                    @if($this->hasPermission($child['permission']))
                                                        <a class="dropdown-item {{ $this->isRouteActive($child['route']) ? 'active' : '' }}" 
                                                           href="{{ $child['route'] ? route($child['route']) : '#' }}" 
                                                           wire:navigate>
                                                            @if($child['icon'])
                                                                <span class="nav-link-icon d-md-none d-lg-inline-block me-2">
                                                                    @include('components.icons.' . $child['icon'])
                                                                </span>
                                                            @endif
                                                            {{ $child['title'] }}
                                                            @if($child['badge'])
                                                                <span class="badge bg-{{ $child['badge']['color'] ?? 'primary' }} ms-2">
                                                                    {{ $child['badge']['text'] }}
                                                                </span>
                                                            @endif
                                                        </a>
                                                    @endif
                                                @endforeach
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <!-- Regular Dropdown -->
                                    @foreach($item['children'] as $child)
                                        @if($this->hasPermission($child['permission']))
                                            <a class="dropdown-item {{ $this->isRouteActive($child['route']) ? 'active' : '' }}" 
                                               href="{{ $child['route'] ? route($child['route']) : '#' }}" 
                                               wire:navigate>
                                                @if($child['icon'])
                                                    <span class="nav-link-icon d-md-none d-lg-inline-block me-2">
                                                        @include('components.icons.' . $child['icon'])
                                                    </span>
                                                @endif
                                                {{ $child['title'] }}
                                                @if($child['badge'])
                                                    <span class="badge bg-{{ $child['badge']['color'] ?? 'primary' }} ms-2">
                                                        {{ $child['badge']['text'] }}
                                                    </span>
                                                @endif
                                            </a>
                                        @endif
                                    @endforeach
                                @endif
                            </div>
                        @else
                            <!-- Single Menu Item -->
                            <a class="nav-link" 
                               href="{{ $item['route'] ? route($item['route']) : '#' }}" 
                               wire:navigate>
                                <span class="nav-link-icon d-md-none d-lg-inline-block">
                                    @include('components.icons.' . $item['icon'])
                                </span>
                                <span class="nav-link-title">
                                    {{ $item['title'] }}
                                </span>
                                @if($item['badge'])
                                    <span class="badge bg-{{ $item['badge']['color'] ?? 'primary' }} ms-2">
                                        {{ $item['badge']['text'] }}
                                    </span>
                                @endif
                            </a>
                        @endif
                    </li>
                @endif
            @endforeach
        </ul>
    </div>
</div>

<!-- Breadcrumb Navigation (optional, can be included in page header) -->
@if(!empty($breadcrumbs) && count($breadcrumbs) > 1)
    <div class="page-pretitle d-none d-lg-block">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb breadcrumb-arrows">
                @foreach($breadcrumbs as $breadcrumb)
                    <li class="breadcrumb-item {{ $breadcrumb['active'] ? 'active' : '' }}">
                        @if($breadcrumb['active'])
                            {{ $breadcrumb['title'] }}
                        @else
                            <a href="{{ route($breadcrumb['route']) }}" wire:navigate>
                                {{ $breadcrumb['title'] }}
                            </a>
                        @endif
                    </li>
                @endforeach
            </ol>
        </nav>
    </div>
@endif

@push('scripts')
<script>
    // Close dropdowns when clicking outside
    document.addEventListener('click', function(event) {
        if (!event.target.closest('.dropdown')) {
            @this.call('closeAllDropdowns');
        }
    });

    // Handle route changes for breadcrumb updates
    document.addEventListener('livewire:navigated', function() {
        const routeName = window.location.pathname.split('/').filter(Boolean).join('.');
        @this.call('handleRouteChange', routeName);
    });
</script>
@endpush