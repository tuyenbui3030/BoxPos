<?php

namespace App\Livewire;

use Livewire\Component;
use Packages\Log\Traits\Loggable;

class NavigationComponent extends Component
{
    use Loggable;

    public function mount()
    {
        $this->logActivity('navigation_component_mounted');
    }

    public function render()
    {
        return view('livewire.navigation-component');
    }
}