<!-- Navigation Menu -->
<div class="collapse navbar-collapse" id="navbar-menu">
    <div class="d-flex flex-column flex-md-row flex-fill align-items-stretch align-items-md-center">
        <ul class="navbar-nav">
            <li class="nav-item {{ is_current_route('dashboard') ? 'active' : '' }}">
                <a class="nav-link spa-link" href="{{ localized_route('dashboard') }}" wire:navigate>
                    <span class="nav-link-icon d-md-none d-lg-inline-block">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                            <polyline points="5 12 3 12 12 3 21 12 19 12"/>
                            <path d="M5 12v7a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-7"/>
                            <path d="M9 21v-6a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v6"/>
                        </svg>
                    </span>
                    <span class="nav-link-title">
                        {{ __('app.menu_home') }}
                    </span>
                </a>
            </li>

    <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle"
           href="#"
           wire:click="toggleProductsDropdown"
           role="button"
           aria-expanded="{{ $showProductsDropdown ? 'true' : 'false' }}">
            <span class="nav-link-icon d-md-none d-lg-inline-block">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                    <path d="M12 3l8 4.5l0 9l-8 4.5l-8 -4.5l0 -9l8 -4.5"/>
                    <path d="M12 12l8 -4.5"/>
                    <path d="M12 12l0 9"/>
                    <path d="M12 12l-8 -4.5"/>
                </svg>
            </span>
            <span class="nav-link-title">
                {{ __('app.menu_products') }}
            </span>
        </a>
        <div class="dropdown-menu"
             style="display: {{ $showProductsDropdown ? 'block' : 'none' }};"
             wire:click.away="closeProductsDropdown">
            <div class="dropdown-menu-columns">
                <div class="dropdown-menu-column">
                    <h6 class="dropdown-header">Danh mục vật liệu</h6>
                    <a class="dropdown-item spa-link" href="/{{ app()->getLocale() }}/material-catalog/categories" wire:navigate>
                        <span class="nav-link-icon d-md-none d-lg-inline-block me-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                <path d="M4 4h6v6h-6z"/>
                                <path d="M14 4h6v6h-6z"/>
                                <path d="M4 14h6v6h-6z"/>
                                <path d="M17 17m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0"/>
                            </svg>
                        </span>
                        Danh mục vật liệu
                    </a>
                    <a class="dropdown-item spa-link" href="/{{ app()->getLocale() }}/material-catalog/materials" wire:navigate>
                        <span class="nav-link-icon d-md-none d-lg-inline-block me-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                <path d="M3 21l18 0"/>
                                <path d="M5 21v-16a2 2 0 0 1 2 -2h10a2 2 0 0 1 2 2v16"/>
                                <path d="M9 9l0 4"/>
                                <path d="M12 7l0 6"/>
                                <path d="M15 11l0 2"/>
                            </svg>
                        </span>
                        Vật liệu xây dựng
                    </a>
                    <a class="dropdown-item spa-link" href="/{{ app()->getLocale() }}/material-catalog/units" wire:navigate>
                        <span class="nav-link-icon d-md-none d-lg-inline-block me-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                <path d="M12 3l8 4.5l0 9l-8 4.5l-8 -4.5l0 -9l8 -4.5"/>
                                <path d="M12 12l8 -4.5"/>
                                <path d="M12 12l0 9"/>
                                <path d="M12 12l-8 -4.5"/>
                            </svg>
                        </span>
                        Đơn vị tính
                    </a>
                    <div class="dropdown-divider"></div>
                    <h6 class="dropdown-header">Khác</h6>
                    <a class="dropdown-item" href="#" onclick="alert('Products page - Coming soon!')">
                        {{ __('app.menu_all_products') }}
                    </a>
                    <a class="dropdown-item" href="#" onclick="alert('Inventory page - Coming soon!')">
                        {{ __('app.menu_inventory') }}
                    </a>
                </div>
            </div>
        </div>
    </li>

    <li class="nav-item {{ is_current_route('customers') ? 'active' : '' }}">
        <a class="nav-link spa-link" href="{{ localized_route('customers') }}" wire:navigate>
            <span class="nav-link-icon d-md-none d-lg-inline-block">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                    <path d="M9 7m-4 0a4 4 0 1 0 8 0a4 4 0 1 0 -8 0"/>
                    <path d="M3 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2"/>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                    <path d="M21 21v-2a4 4 0 0 0 -3 -3.85"/>
                </svg>
            </span>
            <span class="nav-link-title">
                {{ __('app.menu_customers') }}
            </span>
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link" href="#">
            <span class="nav-link-icon d-md-none d-lg-inline-block">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                    <path d="M3 19a9 9 0 0 1 9 0a9 9 0 0 1 9 0"/>
                    <path d="M3 6a9 9 0 0 1 9 0a9 9 0 0 1 9 0"/>
                    <path d="M3 6l0 13"/>
                    <path d="M12 6l0 13"/>
                    <path d="M21 6l0 13"/>
                </svg>
            </span>
            <span class="nav-link-title">
                {{ __('app.menu_orders') }}
            </span>
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link" href="#">
            <span class="nav-link-icon d-md-none d-lg-inline-block">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                    <path d="M7 9m0 2a2 2 0 0 1 2 -2h6a2 2 0 0 1 2 2v6a2 2 0 0 1 -2 2h-6a2 2 0 0 1 -2 -2z"/>
                    <path d="M14 9l0 -4a2 2 0 0 0 -2 -2h-2a2 2 0 0 0 -2 2v4"/>
                    <path d="M12 16l0 .01"/>
                    <path d="M3 13a20 20 0 0 0 18 0"/>
                </svg>
            </span>
            <span class="nav-link-title">
                {{ __('app.menu_pos') }}
            </span>
        </a>
    </li>
        </ul>
    </div>
</div>


