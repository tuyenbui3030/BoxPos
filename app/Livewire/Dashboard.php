<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Title;
use App\Traits\HasTheme;

class Dashboard extends Component
{
    use HasTheme;
    
    #[Title('Dashboard')]
    public function render()
    {
        return view('livewire.dashboard')
            ->layout('layouts.app', [
                'header' => 'Dashboard'
            ]);
    }
}
