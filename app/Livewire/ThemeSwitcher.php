<?php

namespace App\Livewire;

use Livewire\Component;
use App\Traits\HasTheme;

class ThemeSwitcher extends Component
{
    use HasTheme;
    
    public $theme = 'light';
    
    public function mount()
    {
        // Get theme from session or default to light
        $this->theme = $this->getCurrentTheme();
    }
    
    public function toggleTheme()
    {
        $this->theme = $this->theme === 'light' ? 'dark' : 'light';
        $this->setTheme($this->theme);
        
        // Emit event for JavaScript to update DOM immediately
        $this->dispatch('theme-changed', theme: $this->theme);
    }
    
    public function render()
    {
        return view('livewire.theme-switcher');
    }
}
