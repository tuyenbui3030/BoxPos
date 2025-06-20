<?php

namespace Packages\User\Livewire;

use Livewire\Component;
use Livewire\Attributes\Title;
use Packages\Appearance\Traits\HasAppearance;

class Dashboard extends Component
{
    use HasAppearance;
    
    #[Title('Dashboard')]
    public function render()
    {
        return view('user::livewire.dashboard')
            ->layout('layouts.app', [
                'header' => 'Dashboard'
            ]);
    }
}
