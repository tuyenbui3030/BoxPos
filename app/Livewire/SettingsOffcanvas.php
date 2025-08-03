<?php

namespace App\Livewire;

use Livewire\Component;
use Packages\Log\Traits\Loggable;
use Packages\Appearance\Traits\HasAppearance;

class SettingsOffcanvas extends Component
{
    use Loggable, HasAppearance;

    public $currentTheme = 'light';
    public $isOpen = false;

    protected $listeners = ['open-settings' => 'openSettings'];

    public function mount()
    {
        $this->currentTheme = $this->getCurrentTheme();
    }

    public function openSettings()
    {
        $this->isOpen = true;
        $this->dispatch('settings-opened');
    }

    public function closeSettings()
    {
        $this->isOpen = false;
        $this->dispatch('settings-closed');
    }

    public function switchLanguage($locale, $currentUrl = null)
    {
        // Copy exact logic from LanguageSwitcher
        
        // Validate locale - chỉ cho phép 'vi' và 'en'
        if (!in_array($locale, ['vi', 'en'])) {
            // ⚠️ MANDATORY: Log error
            $this->logActivity('language_switch_invalid', [
                'invalid_locale' => $locale,
                'user_id' => auth()->user()?->id,
                'ip_address' => request()->ip(),
                'component' => 'SettingsOffcanvas',
            ]);
            return;
        }

        // ⚠️ MANDATORY: Log user action
        $this->logActivity('language_switch_requested', [
            'from_locale' => app()->getLocale(),
            'to_locale' => $locale,
            'user_id' => auth()->user()?->id,
            'ip_address' => request()->ip(),
            'component' => 'SettingsOffcanvas',
        ]);
        // Set session và app locale
        session(['app_locale' => $locale]);
        app()->setLocale($locale);

        // ⚠️ MANDATORY: Log successful switch
        $this->logActivity('language_switched_successfully', [
            'new_locale' => $locale,
            'user_id' => auth()->user()?->id,
            'ip_address' => request()->ip(),
            'component' => 'SettingsOffcanvas',
        ]);

        // Get current path from parameter or fallback to session/referer
        $currentPath = $currentUrl ? parse_url($currentUrl, PHP_URL_PATH) : $this->getCurrentPath();
        $newUrl = $this->buildLocalizedUrl($currentPath, $locale);
        
        // Instead of redirect, dispatch events for SPA
        $this->dispatch('locale-updated', locale: $locale);
        $this->dispatch('redirect-to-url', url: $newUrl);
    }



    /**
     * Get current path from various sources
     */
    private function getCurrentPath(): string
    {
        // Try to get from session first (stored by middleware)
        if (session()->has('current_path')) {
            return session('current_path');
        }
        
        // Fallback to HTTP referer
        $referer = request()->header('referer');
        if ($referer) {
            $path = parse_url($referer, PHP_URL_PATH);
            return ltrim($path, '/');
        }
        
        // Last fallback
        return '';
    }

    /**
     * Build localized URL for language switching - copied from LanguageSwitcher
     */
    public function buildLocalizedUrl(string $currentPath, string $locale): string
    {
        // Remove leading slash if present
        $currentPath = ltrim($currentPath, '/');
        
        // Loại bỏ locale hiện tại khỏi path nếu có
        $cleanPath = $currentPath;
        if (preg_match('/^(vi|en)\/(.*)$/', $currentPath, $matches)) {
            $cleanPath = $matches[2];
        } elseif (preg_match('/^(vi|en)$/', $currentPath)) {
            $cleanPath = '';
        }

        // Tạo URL mới với locale
        if (empty($cleanPath)) {
            return "/$locale";
        }

        return "/$locale/$cleanPath";
    }

    public function render()
    {
        return view('livewire.settings-offcanvas');
    }
}