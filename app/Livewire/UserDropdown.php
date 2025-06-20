<?php

namespace App\Livewire;

use Livewire\Component;
use Packages\Log\Traits\Loggable;

class UserDropdown extends Component
{
    use Loggable; // ⚠️ MANDATORY: Use Loggable trait
    public $isDropdownOpen = false;

    public function toggleDropdown()
    {
        $this->isDropdownOpen = !$this->isDropdownOpen;
        
        // Debug log
        \Log::info('UserDropdown toggled: ' . ($this->isDropdownOpen ? 'opened' : 'closed'));
    }

    public function closeDropdown()
    {
        $this->isDropdownOpen = false;
        \Log::info('UserDropdown closed');
    }

    public function render()
    {
        return view('livewire.user-dropdown');
    }
}
