<x-app-layout>
    <x-slot name="header">
        {{ __('App Marketplace') }}
    </x-slot>

    <div class="row row-deck row-cards">
        <!-- Page Header -->
        <div class="col-12">
            <div class="card">
                <div class="card-body text-center py-5">
                    <div class="mb-3" style="font-size: 64px;">🏪</div>
                    <h1 class="card-title">{{ __('BoxPos App Marketplace') }}</h1>
                    <p class="text-muted">{{ __('Discover specialized business solutions tailored for your industry') }}</p>
                </div>
            </div>
        </div>

        <!-- My Apps Section -->
        @if($userSubscriptions->count() > 0)
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">{{ __('My Apps') }}</h3>
                        <div class="card-actions">
                            <a href="{{ route('subscriptions.index') }}" class="btn btn-outline-primary btn-sm">
                                {{ __('Manage Subscriptions') }}
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            @foreach($userSubscriptions as $subscription)
                                @php
                                    $app = $subscription->application;
                                    $appIcon = match($app->slug) {
                                        'building-pos' => '🏗️',
                                        'clinic-pos' => '🏥',
                                        'restaurant-pos' => '🍽️',
                                        'beauty-pos' => '💄',
                                        default => '📱'
                                    };
                                @endphp
                                <div class="col-md-6 col-lg-4">
                                    <div class="card card-sm">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center">
                                                <div class="me-3" style="font-size: 32px; color: {{ $app->getPrimaryColor() }}">
                                                    {{ $appIcon }}
                                                </div>
                                                <div class="flex-fill">
                                                    <div class="font-weight-medium">{{ $app->name }}</div>
                                                    <div class="text-muted small">{{ $app->description }}</div>
                                                    <div class="mt-1">
                                                        <span class="badge bg-{{ $subscription->isTrial() ? 'warning' : 'success' }}-lt">
                                                            {{ ucfirst($subscription->plan_type) }}
                                                        </span>
                                                        @if($subscription->expires_at)
                                                            <span class="text-muted small ms-2">
                                                                {{ $subscription->getDaysRemaining() }} {{ __('days left') }}
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="ms-auto">
                                                    <a href="{{ route('subscriptions.show', $subscription) }}" class="btn btn-outline-primary btn-sm">
                                                        {{ __('Manage') }}
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Available Apps -->
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ __('Available Apps') }}</h3>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        @foreach($applications as $app)
                            @php
                                $hasSubscription = $userSubscriptions->has($app->slug);
                                $appIcon = match($app->slug) {
                                    'building-pos' => '🏗️',
                                    'clinic-pos' => '🏥',
                                    'restaurant-pos' => '🍽️',
                                    'beauty-pos' => '💄',
                                    default => '📱'
                                };
                                $statusBadge = match($app->status) {
                                    'active' => ['success', 'Available'],
                                    'beta' => ['warning', 'Beta'],
                                    'coming_soon' => ['info', 'Coming Soon'],
                                    default => ['secondary', 'Unknown']
                                };
                            @endphp
                            
                            <div class="col-md-6 col-lg-4">
                                <div class="card h-100">
                                    <div class="card-body d-flex flex-column">
                                        <!-- App Header -->
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="me-3" style="font-size: 48px; color: {{ $app->getPrimaryColor() }}">
                                                {{ $appIcon }}
                                            </div>
                                            <div class="flex-fill">
                                                <h3 class="card-title mb-1">{{ $app->name }}</h3>
                                                <span class="badge bg-{{ $statusBadge[0] }}-lt">{{ $statusBadge[1] }}</span>
                                            </div>
                                        </div>

                                        <!-- Description -->
                                        <p class="text-muted flex-fill">{{ $app->description }}</p>

                                        <!-- Pricing -->
                                        <div class="mb-3">
                                            @php
                                                $basicPricing = $app->getPricing('basic');
                                                $professionalPricing = $app->getPricing('professional');
                                            @endphp
                                            @if($basicPricing)
                                                <div class="text-muted small">{{ __('Starting from') }}</div>
                                                <div class="h4 mb-0">{{ number_format($basicPricing['monthly_price']) }}đ<small class="text-muted">/{{ __('month') }}</small></div>
                                            @endif
                                        </div>

                                        <!-- Features -->
                                        @if($app->features)
                                            <div class="mb-3">
                                                <div class="text-muted small mb-2">{{ __('Key Features') }}:</div>
                                                <div class="d-flex flex-wrap gap-1">
                                                    @foreach(array_slice($app->features, 0, 3) as $feature => $description)
                                                        <span class="badge bg-light text-dark">{{ $description }}</span>
                                                    @endforeach
                                                    @if(count($app->features) > 3)
                                                        <span class="badge bg-light text-muted">+{{ count($app->features) - 3 }} {{ __('more') }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                        @endif

                                        <!-- Actions -->
                                        <div class="mt-auto">
                                            @if($hasSubscription)
                                                <div class="d-flex align-items-center text-success">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon me-2">
                                                        <polyline points="20,6 9,17 4,12"></polyline>
                                                    </svg>
                                                    {{ __('Subscribed') }}
                                                </div>
                                            @elseif($app->status === 'active')
                                                <div class="btn-list">
                                                    <form action="{{ route('marketplace.trial', $app) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-outline-primary">
                                                            {{ __('Free Trial') }}
                                                        </button>
                                                    </form>
                                                    <a href="{{ route('marketplace.show', $app) }}" class="btn btn-primary">
                                                        {{ __('Subscribe') }}
                                                    </a>
                                                </div>
                                            @elseif($app->status === 'beta')
                                                <a href="{{ route('marketplace.show', $app) }}" class="btn btn-warning">
                                                    {{ __('Join Beta') }}
                                                </a>
                                            @else
                                                <button class="btn btn-secondary" disabled>
                                                    {{ __('Coming Soon') }}
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Help Section -->
        <div class="col-12">
            <div class="card">
                <div class="card-body text-center py-4">
                    <h3>{{ __('Need Help Choosing?') }}</h3>
                    <p class="text-muted">{{ __('Our team can help you find the perfect app for your business needs') }}</p>
                    <div class="btn-list justify-content-center">
                        <a href="#" class="btn btn-outline-primary">
                            {{ __('Contact Sales') }}
                        </a>
                        <a href="#" class="btn btn-outline-primary">
                            {{ __('View Documentation') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>