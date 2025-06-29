@php
    // Cache for request lifecycle to avoid multiple service calls
    static $projectData = null;

    if ($projectData === null) {
        $projectService = app(\App\Services\ProjectService::class);
        $currentProject = $projectService->getCurrentProject();
        $availableProjects = $projectService->getUserProjects();
        $userRole = $currentProject ? $projectService->getUserRole($currentProject['slug']) : 'N/A';

        $projectData = compact('projectService', 'currentProject', 'availableProjects', 'userRole');
    }

    extract($projectData);
@endphp

<div class="nav-item dropdown" x-data="{ isOpen: false }">
    <a href="#"
       class="nav-link d-flex lh-1 text-reset p-0"
       @click.prevent="isOpen = !isOpen"
       :aria-expanded="isOpen ? 'true' : 'false'"
       aria-label="Open user menu">
        <span class="avatar avatar-sm" style="background-image: url('https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name ?? 'User') }}&background=random')"></span>
        <div class="d-none d-xl-block ps-2">
            <div>{{ auth()->user()->name ?? 'User' }}</div>
            <div class="mt-1 small text-muted">
                @if($currentProject)
                    {{ $currentProject['name'] }} • {{ $userRole }}
                @else
                    {{ auth()->user()->email ?? 'user@theboxpos.com' }}
                @endif
            </div>
        </div>
    </a>
    <div x-show="isOpen"
         x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="dropdown-menu dropdown-menu-arrow dropdown-menu-end"
         :class="{ 'show': isOpen }"
         style="position: absolute; top: 100%; right: 0px; z-index: 1050; display: none;"
         @click.away="isOpen = false">

        <!-- Project Switcher Section -->
        @if($currentProject)
            <div class="dropdown-header">
                <h6 class="dropdown-header-title">Current Project</h6>
                <p class="dropdown-header-subtitle">
                    <span style="margin-right: 4px;">{{ $currentProject['icon'] ?? '📁' }}</span>
                    {{ $currentProject['name'] }} • {{ $userRole }}
                </p>
            </div>

            @if($availableProjects && $availableProjects->count() > 1)
                <div class="dropdown-header">
                    <h6 class="dropdown-header-title">Switch Project</h6>
                </div>

                @foreach($availableProjects as $project)
                    @if($project['id'] !== $currentProject['id'])
                        <a href="{{ route('locale.store.switch', ['locale' => app()->getLocale()]) }}?store_id={{ $project['id'] }}"
                           class="dropdown-item">
                            <div class="d-flex align-items-center">
                                <span class="avatar avatar-sm me-2" style="background-color: {{ $project['color'] ?? '#6c757d' }}; color: white;">
                                    {{ $project['icon'] ?? substr($project['name'], 0, 1) }}
                                </span>
                                <div class="flex-fill">
                                    <div class="font-weight-medium">{{ $project['name'] }}</div>
                                    <div class="text-muted small">{{ $project['description'] ?? 'No description' }}</div>
                                    <span class="badge badge-outline text-muted">{{ ucfirst($projectService->getUserRole($project['slug']) ?? 'user') }}</span>
                                </div>
                            </div>
                        </a>
                    @endif
                @endforeach

                <div class="dropdown-divider"></div>
                <a href="#" class="dropdown-item" onclick="alert('All Projects page - Coming soon!')">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon dropdown-item-icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                        <path d="M3 21l18 0"/>
                        <path d="M5 21v-16a2 2 0 0 1 2 -2h10a2 2 0 0 1 2 2v16"/>
                        <path d="M9 9l2 0"/>
                        <path d="M9 12l4 0"/>
                        <path d="M9 15l6 0"/>
                    </svg>
                    All Projects
                </a>
                <div class="dropdown-divider"></div>
            @endif
        @endif

        <!-- User Menu Items -->
        <a href="{{ localized_route('devices') }}" class="dropdown-item" wire:navigate>
            <svg xmlns="http://www.w3.org/2000/svg" class="icon dropdown-item-icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                <rect x="7" y="4" width="10" height="16" rx="1"/>
                <path d="M11 5h2"/>
                <circle cx="12" cy="17" r="1"/>
            </svg>
            {{ __('app.manage_devices') }}
        </a>
        <div class="dropdown-divider"></div>

        <!-- Logout Button -->
        <form method="POST" action="{{ route('logout') }}" class="d-inline">
            @csrf
            <button type="submit" class="dropdown-item">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon dropdown-item-icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                    <path d="M14 8v-2a2 2 0 0 0 -2 -2h-7a2 2 0 0 0 -2 2v12a2 2 0 0 0 2 2h7a2 2 0 0 0 2 -2v-2"/>
                    <path d="M7 12h14l-3 -3m0 6l3 -3"/>
                </svg>
                {{ __('app.logout') }}
            </button>
        </form>
    </div>
</div>


