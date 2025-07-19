<div class="nav-item dropdown" x-data="{ isOpen: false }">
    <!-- Hiển thị ngôn ngữ hiện tại -->
    <a href="#"
       class="nav-link d-flex lh-1 text-reset p-0"
       @click.prevent="isOpen = !isOpen"
       :aria-expanded="isOpen">
        <span class="avatar avatar-sm">{{ $currentLanguage['flag'] }}</span>
        <div class="d-none d-xl-block ps-2">
            <div>{{ $currentLanguage['native'] }}</div>
        </div>
    </a>

    <!-- Dropdown menu -->
    <div x-show="isOpen"
         x-cloak
         x-transition
         @click.away="isOpen = false"
         class="dropdown-menu dropdown-menu-end"
         style="position: absolute; top: 100%; right: 0; z-index: 1050; min-width: 200px;"
         :class="{ 'show': isOpen }">

        <h6 class="dropdown-header">Chọn ngôn ngữ / Select Language</h6>

        <!-- Tiếng Việt -->
        @php
            $viUrl = $this->buildLocalizedUrl(request()->path(), 'vi');
        @endphp
        <a href="{{ $viUrl }}"
           class="dropdown-item {{ $currentLocale === 'vi' ? 'active' : '' }}"
           @click="isOpen = false"
           title="Chuyển sang tiếng Việt">
            <span class="me-2">🇻🇳</span>
            <span>Tiếng Việt</span>
            @if($currentLocale === 'vi')
                <svg class="icon ms-auto text-success" width="18" height="18" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none">
                    <path d="M5 12l5 5l10 -10"/>
                </svg>
            @endif
        </a>

        <!-- English -->
        @php
            $enUrl = $this->buildLocalizedUrl(request()->path(), 'en');
        @endphp
        <a href="{{ $enUrl }}"
           class="dropdown-item {{ $currentLocale === 'en' ? 'active' : '' }}"
           @click="isOpen = false"
           title="Switch to English">
            <span class="me-2">🇺🇸</span>
            <span>English</span>
            @if($currentLocale === 'en')
                <svg class="icon ms-auto text-success" width="18" height="18" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none">
                    <path d="M5 12l5 5l10 -10"/>
                </svg>
            @endif
        </a>
    </div>
</div>