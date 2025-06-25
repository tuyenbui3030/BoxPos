<?php

namespace App\Livewire;

use Livewire\Component;
use Packages\Log\Traits\Loggable;

class LanguageSwitcher extends Component
{
    use Loggable;

    public $isDropdownOpen = false;

    public function toggleDropdown()
    {
        $this->isDropdownOpen = !$this->isDropdownOpen;
        
        $this->logActivity('language_switcher_toggled', [
            'state' => $this->isDropdownOpen ? 'opened' : 'closed'
        ]);
    }

    public function closeDropdown()
    {
        $this->isDropdownOpen = false;
    }

    public function switchLanguage($locale)
    {
        \Log::info('Livewire switchLanguage called', ['locale' => $locale]);
        
        $this->logActivity('language_switch_requested', [
            'from' => app()->getLocale(),
            'to' => $locale
        ]);

        $this->closeDropdown();
        
        // Switch language in session
        session(['locale' => $locale]);
        app()->setLocale($locale);
        
        \Log::info('Language switched successfully', ['locale' => $locale]);
        
        // Log successful switch
        $this->logActivity('language_switched', [
            'new_locale' => $locale
        ]);
        
        // Dispatch global browser event that all components can hear
        $this->js("window.dispatchEvent(new CustomEvent('locale-updated', { detail: { locale: '$locale' } }))");
        
        // Also dispatch Livewire event to any components that are listening
        $this->dispatch('locale-updated', locale: $locale);
        
        // Force re-render of this component with new language
        $this->js('$wire.$refresh()');
    }

    public function render()
    {
        $currentLanguage = get_current_language();
        $availableLanguages = get_available_languages();
        
        return view('livewire.language-switcher', compact('currentLanguage', 'availableLanguages'));
    }
}
