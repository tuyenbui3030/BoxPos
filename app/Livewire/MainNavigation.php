<?php

namespace App\Livewire;

use Livewire\Component;
use App\Traits\HandlesLocaleUpdates;

class MainNavigation extends Component
{
    use HandlesLocaleUpdates;
    
    protected $listeners = [
        'locale-updated' => 'handleLocaleUpdate'
    ];

    public function render()
    {
        return view('livewire.main-navigation');
    }
}
