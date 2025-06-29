<?php

namespace App\Livewire;

use Livewire\Component;
use Packages\Log\Traits\Loggable;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class LanguageSwitcher extends Component
{
    use Loggable; // ⚠️ MANDATORY: Use Loggable trait

    public $currentLocale;
    public $availableLanguages = [
        'vi' => [
            'name' => 'Vietnamese',
            'native' => 'Tiếng Việt',
            'flag' => '🇻🇳',
            'direction' => 'ltr'
        ],
        'en' => [
            'name' => 'English',
            'native' => 'English',
            'flag' => '🇺🇸',
            'direction' => 'ltr'
        ]
    ];

    public function mount()
    {
        $this->currentLocale = app()->getLocale();
    }

    public function switchLanguage($locale)
    {
        // Validate locale - chỉ cho phép 'vi' và 'en'
        if (!in_array($locale, ['vi', 'en'])) {
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

        // Set session và app locale
        session(['app_locale' => $locale]);
        app()->setLocale($locale);
        $this->currentLocale = $locale;

        // ⚠️ MANDATORY: Log successful switch
        $this->logActivity('language_switched_successfully', [
            'new_locale' => $locale,
            'user_id' => auth()->user()?->id,
            'ip_address' => request()->ip(),
            'component' => 'LanguageSwitcher',
        ]);

        // Redirect đến URL với locale mới
        $currentPath = request()->path();
        $newUrl = $this->buildLocalizedUrl($currentPath, $locale);

        return redirect($newUrl);
    }

    public function getCurrentLanguage()
    {
        return $this->availableLanguages[$this->currentLocale] ?? $this->availableLanguages['en'];
    }

    /**
     * Tạo URL với locale prefix đơn giản - public để có thể gọi từ view
     */
    public function buildLocalizedUrl(string $currentPath, string $locale): string
    {
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
        return view('livewire.language-switcher', [
            'currentLanguage' => $this->getCurrentLanguage(),
            'availableLanguages' => $this->availableLanguages
        ]);
    }
}
