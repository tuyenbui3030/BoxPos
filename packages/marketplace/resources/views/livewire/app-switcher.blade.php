<div class="nav-item dropdown d-none d-md-flex me-3" x-data="{ open: false }">
    <a href="#" 
       class="nav-link px-0" 
       @click.prevent="open = !open"
       :class="{ 'show': open }"
       tabindex="-1" 
       aria-label="Show apps menu"
       title="{{ __('Switch Apps') }}">
        <!-- Current App Icon or Default Apps Icon -->
        @if($currentApp)
            <div class="d-flex align-items-center">
                <div style="font-size: 20px; color: {{ $currentApp->getPrimaryColor() }}">
                    {{ match($currentApp->slug) {
                        'building-pos' => '🏗️',
                        'clinic-pos' => '🏥', 
                        'restaurant-pos' => '🍽️',
                        'beauty-pos' => '💄',
                        default => '📱'
                    } }}
                </div>
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon ms-1">
                    <polyline points="6,9 12,15 18,9"></polyline>
                </svg>
            </div>
        @else
            <!-- Default Apps Grid Icon -->
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon">
                <path d="M4 4m0 1a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v4a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1z"></path>
                <path d="M4 14m0 1a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v4a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1z"></path>
                <path d="M14 14m0 1a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v4a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1z"></path>
                <path d="M14 7l6 0"></path>
                <path d="M17 4l0 6"></path>
            </svg>
        @endif
    </a>
    
    <div class="dropdown-menu dropdown-menu-arrow dropdown-menu-end dropdown-menu-card" 
         x-show="open" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         @click.outside="open = false"
         :class="{ 'show': open }"
         style="display: none;"
         x-show.transition="open">
        <div class="card">
            <div class="card-header">
                <div class="card-title">{{ __('My Apps') }}</div>
                <div class="card-actions btn-actions">
                    <a href="{{ route('marketplace.index') }}" class="btn-action" title="{{ __('Browse Apps') }}">
                        <!-- Plus Icon -->
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon">
                            <path d="M12 5l0 14"></path>
                            <path d="M5 12l14 0"></path>
                        </svg>
                    </a>
                </div>
            </div>
            
            <div class="card-body scroll-y p-2" style="max-height: 50vh">
                @if($userSubscriptions->count() > 0)
                    <div class="row g-0">
                        @foreach($userSubscriptions as $subscription)
                            @php
                                $app = $subscription->application;
                                $isCurrentApp = $currentApp && $currentApp->id === $app->id;
                                $appIcon = match($app->slug) {
                                    'building-pos' => '🏗️',
                                    'clinic-pos' => '🏥',
                                    'restaurant-pos' => '🍽️',
                                    'beauty-pos' => '💄',
                                    default => '📱'
                                };
                            @endphp
                            
                            <div class="col-4">
                                <a href="#" 
                                   wire:click.prevent="switchToApp('{{ $app->slug }}')"
                                   @click="open = false"
                                   wire:loading.attr="disabled"
                                   wire:target="switchToApp"
                                   class="d-flex flex-column flex-center text-center py-2 px-2 link-hoverable app-switcher-item {{ $isCurrentApp ? 'bg-primary-lt current-app' : 'text-secondary' }} position-relative"
                                   title="{{ $app->description }}"
                                   style="transition: all 0.2s ease;"
                                   @mouseenter="$el.style.transform = 'scale(1.05)'"
                                   @mouseleave="$el.style.transform = 'scale(1)'"
                                    <div class="w-6 h-6 mx-auto mb-2 d-flex align-items-center justify-content-center {{ $isCurrentApp ? 'current-app-icon' : '' }}" 
                                         style="font-size: 24px; color: {{ $app->getPrimaryColor() }}"
                                        {{ $appIcon }}
                                    </div>
                                    <span class="h5 mb-1">{{ $app->name }}</span>
                                    <small class="text-muted">
                                        {{ ucfirst($subscription->plan_type) }}
                                        @if($subscription->isTrial())
                                            <span class="badge badge-sm bg-warning-lt">Trial</span>
                                        @endif
                                    </small>
                                    @if($subscription->expires_at)
                                        <small class="text-muted">
                                            {{ $subscription->getDaysRemaining() }} {{ __('days left') }}
                                        </small>
                                    @endif
                                    
                                    <!-- Loading indicator -->
                                    <div wire:loading wire:target="switchToApp" 
                                         class="position-absolute top-50 start-50 translate-middle">
                                        <div class="spinner-border spinner-border-sm text-primary" role="status">
                                            <span class="visually-hidden">{{ __('Loading...') }}</span>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        @endforeach
                    </div>
                    
                    <!-- Current App Info -->
                    @if($currentApp)
                        <div class="border-top mt-2 pt-2">
                            <div class="d-flex align-items-center">
                                <div class="me-2" style="font-size: 20px; color: {{ $currentApp->getPrimaryColor() }}">
                                    {{ match($currentApp->slug) {
                                        'building-pos' => '🏗️',
                                        'clinic-pos' => '🏥', 
                                        'restaurant-pos' => '🍽️',
                                        'beauty-pos' => '💄',
                                        default => '📱'
                                    } }}
                                </div>
                                <div class="flex-fill">
                                    <div class="font-weight-medium">{{ $currentApp->name }}</div>
                                    <div class="text-muted small">{{ __('Current App') }}</div>
                                </div>
                                @php
                                    $currentSubscription = $userSubscriptions->get($currentApp->slug);
                                @endphp
                                @if($currentSubscription)
                                    <div class="text-end">
                                        <span class="badge bg-{{ $currentSubscription->isTrial() ? 'warning' : 'success' }}-lt">
                                            {{ ucfirst($currentSubscription->plan_type) }}
                                        </span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                    
                @else
                    <!-- No Apps -->
                    <div class="text-center py-4">
                        <div class="mb-3" style="font-size: 48px; opacity: 0.5">📱</div>
                        <h4>{{ __('No Apps Yet') }}</h4>
                        <p class="text-muted">{{ __('Browse our marketplace to get started') }}</p>
                        <a href="{{ route('marketplace.index') }}" 
                           class="btn btn-primary btn-sm"
                           @click="open = false">
                            {{ __('Browse Apps') }}
                        </a>
                    </div>
                @endif
                
                <!-- Marketplace Link -->
                <div class="border-top mt-2 pt-2">
                    <a href="{{ route('marketplace.index') }}" 
                       class="d-flex align-items-center text-muted text-decoration-none"
                       @click="open = false">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon me-2">
                            <circle cx="9" cy="21" r="1"></circle>
                            <circle cx="20" cy="21" r="1"></circle>
                            <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                        </svg>
                        {{ __('Browse App Marketplace') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('livewire:init', () => {
    // Listen for app switch events
    Livewire.on('app-switched', (event) => {
        // Show success notification
        if (typeof window.showNotification === 'function') {
            window.showNotification('success', 'App switched successfully!');
        }
        
        // Optional: Add some visual feedback
        const appSwitcher = document.querySelector('[x-data*="open"]');
        if (appSwitcher) {
            appSwitcher.style.transform = 'scale(1.1)';
            setTimeout(() => {
                appSwitcher.style.transform = 'scale(1)';
            }, 200);
        }
    });
    
    // Handle app switch errors
    Livewire.on('app-switch-error', (event) => {
        if (typeof window.showNotification === 'function') {
            window.showNotification('error', event.message || 'Failed to switch app');
        }
    });
});

// Add keyboard navigation
document.addEventListener('keydown', function(e) {
    // Press 'A' to toggle app switcher
    if (e.key === 'a' || e.key === 'A') {
        if (e.ctrlKey || e.metaKey) {
            e.preventDefault();
            const appSwitcher = document.querySelector('[x-data*="open"] a');
            if (appSwitcher) {
                appSwitcher.click();
            }
        }
    }
    
    // Press Escape to close dropdown
    if (e.key === 'Escape') {
        const dropdown = document.querySelector('[x-data*="open"]');
        if (dropdown && dropdown.__x) {
            dropdown.__x.$data.open = false;
        }
    }
});
</script>