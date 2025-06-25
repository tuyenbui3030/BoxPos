<?php

namespace Packages\User\Livewire;

use Livewire\Component;
use Livewire\Attributes\Title;
use Packages\Appearance\Traits\HasAppearance;
use App\Traits\HandlesLocaleUpdates;

class Dashboard extends Component
{
    use HasAppearance, HandlesLocaleUpdates;
    
    protected $listeners = [
        'locale-updated' => 'handleLocaleUpdate'
    ];
    
    #[Title('Dashboard')]
    public function render()
    {
        return view('user::livewire.dashboard')
            ->layout('layouts.app', [
                'header' => __('app.dashboard')
            ]);
    }
}
