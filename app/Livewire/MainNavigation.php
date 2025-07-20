<?php

namespace App\Livewire;

use Livewire\Component;
use App\Traits\HandlesLocaleUpdates;

class MainNavigation extends Component
{
    use HandlesLocaleUpdates;

    public $showProductsDropdown = false;

    protected $listeners = [
        'locale-updated' => 'handleLocaleUpdate'
    ];

    public function toggleProductsDropdown()
    {
        $this->showProductsDropdown = !$this->showProductsDropdown;
    }

    public function closeProductsDropdown()
    {
        $this->showProductsDropdown = false;
    }

    public function render()
    {
        return view('livewire.main-navigation');
    }
}
