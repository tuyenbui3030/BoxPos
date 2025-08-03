<?php

namespace Packages\Appearance\Traits;

trait HasAppearance
{
    public function getCurrentTheme(): string
    {
        return session('theme', 'light');
    }
    
    public function setTheme(string $theme): void
    {
        if (in_array($theme, ['light', 'dark'])) {
            session(['theme' => $theme]);
            
            // Log theme change if Loggable trait is available
            if (method_exists($this, 'logActivity')) {
                $this->logActivity('theme_set', [
                    'theme' => $theme,
                    'user_id' => auth()->id(),
                ]);
            }
            
            $this->dispatch('theme-changed', theme: $theme);
        }
    }
    
    public function toggleTheme(): void
    {
        $currentTheme = $this->getCurrentTheme();
        $newTheme = $currentTheme === 'light' ? 'dark' : 'light';
        $this->setTheme($newTheme);
    }
}
