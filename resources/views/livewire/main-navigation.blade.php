<div class="row flex-column flex-md-row flex-fill align-items-center">
    <div class="col">
        <ul class="navbar-nav">
            <li class="nav-item {{ is_current_route('dashboard') ? 'active' : '' }}">
                <a class="nav-link spa-link" href="{{ localized_route('dashboard') }}" wire:navigate>
                    <span class="nav-link-icon d-md-none d-lg-inline-block">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24"
                            viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                            stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                            <polyline points="5 12 3 12 12 3 21 12 19 12" />
                            <path d="M5 12v7a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-7" />
                            <path d="M9 21v-6a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v6" />
                        </svg>
                    </span>
                    <span class="nav-link-title">
                        {{ __('app.menu_home') }}
                    </span>
                </a>
            </li>

            <li class="nav-item dropdown" x-data @click.away="$wire.closeProductsDropdown()">
                <a class="nav-link dropdown-toggle {{ $showProductsDropdown ? 'show' : '' }}" href="#"
                    wire:click="toggleProductsDropdown" role="button" data-bs-toggle="dropdown"
                    aria-expanded="{{ $showProductsDropdown ? 'true' : 'false' }}">
                    <span class="nav-link-icon d-md-none d-lg-inline-block">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24"
                            viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                            stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                            <path d="M12 3l8 4.5l0 9l-8 4.5l-8 -4.5l0 -9l8 -4.5" />
                            <path d="M12 12l8 -4.5" />
                            <path d="M12 12l0 9" />
                            <path d="M12 12l-8 -4.5" />
                        </svg>
                    </span>
                    <span class="nav-link-title">
                        {{ __('app.menu_products') }}
                    </span>
                </a>
                <div class="dropdown-menu {{ $showProductsDropdown ? 'show' : '' }}" bis_skin_checked="1"
                    data-bs-popper="static">
                    <h6 class="dropdown-header">{{ __('app.material_management') }}</h6>
                    <a class="dropdown-item spa-link" href="/{{ app()->getLocale() }}/material-catalog/categories"
                        wire:navigate>
                        <span class="dropdown-item-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24"
                                viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                                stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                <path d="M4 4h6v6h-6z" />
                                <path d="M14 4h6v6h-6z" />
                                <path d="M4 14h6v6h-6z" />
                                <path d="M17 17m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0" />
                            </svg>
                        </span>
                        {{ __('app.material_categories') }}
                        <span class="badge badge-sm bg-green-lt text-uppercase ms-auto">New</span>
                    </a>
                    <a class="dropdown-item spa-link" href="/{{ app()->getLocale() }}/material-catalog/materials"
                        wire:navigate>
                        <span class="dropdown-item-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24"
                                viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                                stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                <path d="M3 21l18 0" />
                                <path d="M5 21v-16a2 2 0 0 1 2 -2h10a2 2 0 0 1 2 2v16" />
                                <path d="M9 9l0 4" />
                                <path d="M12 7l0 6" />
                                <path d="M15 11l0 2" />
                            </svg>
                        </span>
                        {{ __('app.construction_materials') }}
                    </a>
                    <a class="dropdown-item spa-link" href="/{{ app()->getLocale() }}/material-catalog/units"
                        wire:navigate>
                        <span class="dropdown-item-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24"
                                viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                                stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                <path
                                    d="M3 12h1m8 -9v1m8 8h1m-9 8v1m-6.4 -15.4l.7 .7m12.1 -.7l-.7 .7m0 11.4l.7 .7m-12.1 -.7l-.7 .7" />
                                <path d="M9 16a5 5 0 1 1 6 0a3.5 3.5 0 0 0 -1 3a2 2 0 0 1 -4 0a3.5 3.5 0 0 0 -1 -3" />
                                <path d="M9.7 17l4.6 0" />
                            </svg>
                        </span>
                        {{ __('app.units_of_measure') }}
                    </a>
                    <div class="dropdown-divider"></div>
                    <h6 class="dropdown-header">{{ __('app.product_operations') }}</h6>
                    <a class="dropdown-item" href="#" onclick="alert('Products page - Coming soon!')">
                        <span class="dropdown-item-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24"
                                viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                                stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                <path d="M7 4v16l13 0v-16" />
                                <path d="M7 4l4 0l0 -2" />
                                <path d="M11 6l0 4" />
                                <path d="M11 12l5 0" />
                                <path d="M16 14l0 2" />
                            </svg>
                        </span>
                        {{ __('app.menu_all_products') }}
                        <span class="badge badge-sm bg-yellow-lt text-uppercase ms-auto">Soon</span>
                    </a>
                    <a class="dropdown-item" href="#" onclick="alert('Inventory page - Coming soon!')">
                        <span class="dropdown-item-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24"
                                viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                                stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                <path
                                    d="M3 4m0 2a2 2 0 0 1 2 -2h14a2 2 0 0 1 2 2v0a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2z" />
                                <path d="M5 6v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-12" />
                                <path d="M10 16h4" />
                            </svg>
                        </span>
                        {{ __('app.menu_inventory') }}
                        <span class="badge badge-sm bg-yellow-lt text-uppercase ms-auto">Soon</span>
                    </a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="#" onclick="alert('Reports page - Coming soon!')">
                        <span class="dropdown-item-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24"
                                viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                                stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                <path
                                    d="M3 12m0 1a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v6a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1z" />
                                <path
                                    d="M9 8m0 1a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v10a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1z" />
                                <path
                                    d="M15 4m0 1a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v14a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1z" />
                                <path d="M4 20l14 0" />
                            </svg>
                        </span>
                        {{ __('app.product_reports') }}
                        <span class="badge badge-sm bg-blue-lt text-uppercase ms-auto">Pro</span>
                    </a>
                </div>
            </li>

            <li class="nav-item {{ is_current_route('customers') ? 'active' : '' }}">
                <a class="nav-link spa-link" href="{{ localized_route('customers') }}" wire:navigate>
                    <span class="nav-link-icon d-md-none d-lg-inline-block">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24"
                            viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                            stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                            <path d="M9 7m-4 0a4 4 0 1 0 8 0a4 4 0 1 0 -8 0" />
                            <path d="M3 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" />
                            <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                            <path d="M21 21v-2a4 4 0 0 0 -3 -3.85" />
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
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24"
                            viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                            stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                            <path d="M3 19a9 9 0 0 1 9 0a9 9 0 0 1 9 0" />
                            <path d="M3 6a9 9 0 0 1 9 0a9 9 0 0 1 9 0" />
                            <path d="M3 6l0 13" />
                            <path d="M12 6l0 13" />
                            <path d="M21 6l0 13" />
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
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24"
                            viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                            stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                            <path d="M7 9m0 2a2 2 0 0 1 2 -2h6a2 2 0 0 1 2 2v6a2 2 0 0 1 -2 2h-6a2 2 0 0 1 -2 -2z" />
                            <path d="M14 9l0 -4a2 2 0 0 0 -2 -2h-2a2 2 0 0 0 -2 2v4" />
                            <path d="M12 16l0 .01" />
                            <path d="M3 13a20 20 0 0 0 18 0" />
                        </svg>
                    </span>
                    <span class="nav-link-title">
                        {{ __('app.menu_pos') }}
                    </span>
                </a>
            </li>
        </ul>
    </div>

    <!-- Settings Button - Right Side -->
    <div class="col-auto">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a href="#" class="nav-link" wire:click.prevent="$dispatch('open-settings')"
                    title="{{ __('app.settings') }}">
                    <span class="nav-link-icon d-md-none d-lg-inline-block">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" class="icon icon-1">
                            <path
                                d="M10.325 4.317c.426 -1.756 2.924 -1.756 3.35 0a1.724 1.724 0 0 0 2.573 1.066c1.543 -.94 3.31 .826 2.37 2.37a1.724 1.724 0 0 0 1.065 2.572c1.756 .426 1.756 2.924 0 3.35a1.724 1.724 0 0 0 -1.066 2.573c.94 1.543 -.826 3.31 -2.37 2.37a1.724 1.724 0 0 0 -2.572 1.065c-.426 1.756 -2.924 1.756 -3.35 0a1.724 1.724 0 0 0 -2.573 -1.066c-1.543 .94 -3.31 -.826 -2.37 -2.37a1.724 1.724 0 0 0 -1.065 -2.572c-1.756 -.426 -1.756 -2.924 0 -3.35a1.724 1.724 0 0 0 1.066 -2.573c-.94 -1.543 .826 -3.31 2.37 -2.37c1 .608 2.296 .07 2.572 -1.065z">
                            </path>
                            <path d="M9 12a3 3 0 1 0 6 0a3 3 0 0 0 -6 0"></path>
                        </svg>
                    </span>
                    <span class="nav-link-title"> {{ __('app.settings') }} </span>
                </a>
            </li>
        </ul>
    </div>
</div>
