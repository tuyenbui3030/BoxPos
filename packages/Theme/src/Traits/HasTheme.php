<?php

namespace Packages\Theme\Traits;

trait HasTheme
{
    public function getCurrentTheme()
    {
        return session('theme', 'light');
    }
    
    public function setTheme($theme)
    {
        if (in_array($theme, ['light', 'dark'])) {
            session(['theme' => $theme]);
            $this->dispatch('theme-changed', theme: $theme);
        }
    }
    
    public function toggleTheme()
    {
        $currentTheme = $this->getCurrentTheme();
        $newTheme = $currentTheme === 'light' ? 'dark' : 'light';
        $this->setTheme($newTheme);
    }
}
