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

        // Dispatch events to update other components
        $this->dispatch('locale-updated', locale: $locale);

        // Redirect to language switch route (handles session + redirect)
        return $this->redirect(route('language.switch', ['locale' => $locale]));
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

    public function render()
    {
        return view('livewire.language-switcher', [
            'currentLanguage' => $this->getCurrentLanguage(),
            'availableLanguages' => $this->availableLanguages
        ]);
    }
}
