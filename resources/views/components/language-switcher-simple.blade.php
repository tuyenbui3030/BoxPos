<!-- Simple Language Switcher Without Dropdown -->
<div class="d-flex gap-1">
    @php
        $currentLang = get_current_language();
        $availableLangs = get_available_languages();
    @endphp
    
    @foreach($availableLangs as $locale => $language)
        <a 
            href="{{ language_url($locale) }}"
            class="btn btn-sm {{ is_current_language($locale) ? 'btn-primary' : 'btn-ghost' }}"
            title="{{ $language['native'] }}"
        >
            {{ $language['flag'] }}
        </a>
    @endforeach
</div>
