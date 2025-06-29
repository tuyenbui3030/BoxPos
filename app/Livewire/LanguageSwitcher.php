<?php

namespace App\Livewire;

use Livewire\Component;
use Packages\Log\Traits\Loggable;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class LanguageSwitcher extends Component
{
    use Loggable; // ⚠️ MANDATORY: Use Loggable trait

    public $isDropdownOpen = false;
    public $currentLocale;
    public $availableLanguages = [];

    protected $listeners = ['locale-updated' => 'handleLocaleUpdate'];

    public function mount()
    {
        $this->currentLocale = app()->getLocale();
        $this->availableLanguages = config('app.available_locales', []);
    }

    public function toggleDropdown()
    {
        $this->isDropdownOpen = !$this->isDropdownOpen;

        // ⚠️ MANDATORY: Log user action
        $this->logActivity('language_dropdown_toggled', [
            'is_open' => $this->isDropdownOpen,
            'user_id' => auth()->user()?->id,
            'ip_address' => request()->ip(),
            'component' => 'LanguageSwitcher',
        ]);
    }

    public function closeDropdown()
    {
        $this->isDropdownOpen = false;
    }

    public function switchLanguage($locale)
    {
        // Validate locale
        if (!array_key_exists($locale, $this->availableLanguages)) {
            // ⚠️ MANDATORY: Log error
            $this->logActivity('language_switch_invalid', [
                'invalid_locale' => $locale,
                'user_id' => auth()->user()?->id,
                'ip_address' => request()->ip(),
                'component' => 'LanguageSwitcher',
            ]);
            return;
        }

        // ⚠️ MANDATORY: Log user action
        $this->logActivity('language_switch_requested', [
            'from_locale' => $this->currentLocale,
            'to_locale' => $locale,
            'user_id' => auth()->user()?->id,
            'ip_address' => request()->ip(),
            'component' => 'LanguageSwitcher',
        ]);

        $this->closeDropdown();

        // ⚠️ MANDATORY: Log successful switch
        $this->logActivity('language_switched_successfully', [
            'new_locale' => $locale,
            'user_id' => auth()->user()?->id,
            'ip_address' => request()->ip(),
            'component' => 'LanguageSwitcher',
        ]);

        // Update current locale immediately for better UX
        $this->currentLocale = $locale;

        // Set session and app locale immediately
        session(['app_locale' => $locale]);
        app()->setLocale($locale);

        // Dispatch events to update other components
        $this->dispatch('locale-updated', locale: $locale);

        // Get current URL and build localized URL
        $currentUrl = request()->url();
        $localizedUrl = $this->getLocalizedUrl($currentUrl, $locale);

        // Use JavaScript redirect to avoid Livewire navigation conflicts
        $this->dispatch('redirect-to-url', url: $localizedUrl);
    }

    public function handleLocaleUpdate($locale)
    {
        $this->currentLocale = $locale;
        $this->closeDropdown();
    }

    public function getCurrentLanguage()
    {
        // Ensure we have available languages
        if (empty($this->availableLanguages)) {
            $this->availableLanguages = config('app.available_locales', []);
        }

        // Get current language data
        $currentLang = $this->availableLanguages[$this->currentLocale] ?? null;

        // Fallback to English if current locale not found
        if (!$currentLang) {
            $currentLang = $this->availableLanguages['en'] ?? null;
        }

        // Final fallback
        if (!$currentLang) {
            $currentLang = [
                'name' => 'English',
                'native' => 'English',
                'flag' => '🇺🇸',
                'direction' => 'ltr'
            ];
        }

        return $currentLang;
    }

    /**
     * Get localized URL by replacing locale prefix
     */
    private function getLocalizedUrl(string $url, string $locale): string
    {
        $parsedUrl = parse_url($url);
        $path = $parsedUrl['path'] ?? '/';

        // Remove existing locale prefix if any
        $availableLocales = array_keys($this->availableLanguages);
        foreach ($availableLocales as $existingLocale) {
            if (str_starts_with($path, "/$existingLocale/") || $path === "/$existingLocale") {
                $path = substr($path, strlen("/$existingLocale"));
                break;
            }
        }

        // Ensure path starts with /
        if (!str_starts_with($path, '/')) {
            $path = '/' . $path;
        }

        // Add new locale prefix
        $localizedPath = "/$locale" . $path;

        // Rebuild URL
        $scheme = $parsedUrl['scheme'] ?? 'http';
        $host = $parsedUrl['host'] ?? request()->getHost();
        $port = isset($parsedUrl['port']) ? ':' . $parsedUrl['port'] : '';

        return "$scheme://$host$port$localizedPath";
    }

    public function render()
    {
        return view('livewire.language-switcher', [
            'currentLanguage' => $this->getCurrentLanguage(),
            'availableLanguages' => $this->availableLanguages
        ]);
    }
}
