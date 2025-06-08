<?php

namespace Packages\User\Livewire;

use Livewire\Component;
use Livewire\Attributes\Title;
use Packages\Theme\Traits\HasTheme;

class Dashboard extends Component
{
    use HasTheme;
    
    #[Title('Dashboard')]
    public function render()
    {
        return view('user::livewire.dashboard')
            ->layout('layouts.app', [
                'header' => 'Dashboard'
            ]);
    }
}
