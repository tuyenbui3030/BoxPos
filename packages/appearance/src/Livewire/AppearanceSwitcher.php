<?php

namespace Packages\Appearance\Livewire;

use Livewire\Component;
use Packages\Appearance\Traits\HasAppearance;
use Packages\Log\Traits\Loggable;

class AppearanceSwitcher extends Component
{
    use HasAppearance, Loggable;
    
    public $theme = 'light';
    
    public function mount()
    {
        $this->logActivity('appearance_switcher_mounted');
        
        // Get theme from session or default to light
        $this->theme = $this->getCurrentTheme();
    }
    
    public function toggleTheme()
    {
        $oldTheme = $this->theme;
        $this->theme = $this->theme === 'light' ? 'dark' : 'light';
        $this->setTheme($this->theme);
        
        $this->logActivity('theme_toggled', [
            'old_theme' => $oldTheme,
            'new_theme' => $this->theme,
            'user_id' => auth()->id(),
        ]);
        
        // Emit event for JavaScript to update DOM immediately
        $this->dispatch('theme-changed', theme: $this->theme);
    }
    
    public function render()
    {
        return view('appearance::livewire.theme-switcher');
    }
}
