<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\On;

class SearchModal extends Component
{
    public $isOpen = false;
    public $searchQuery = '';
    public $searchResults = [];
    public $isSearching = false;

    public function mount()
    {
        $this->searchQuery = '';
        $this->searchResults = [];
    }

    #[On('open-search-modal')]
    public function openModal()
    {
        $this->isOpen = true;
        $this->dispatch('focus-search-input');
    }

    public function closeModal()
    {
        $this->isOpen = false;
        $this->searchQuery = '';
        $this->searchResults = [];
    }

    public function updatedSearchQuery()
    {
        if (strlen($this->searchQuery) >= 2) {
            $this->performSearch();
        } else {
            $this->searchResults = [];
        }
    }

    public function performSearch()
    {
        $this->isSearching = true;

        // Simulate search - replace with actual search logic
        $this->searchResults = [
            [
                'type' => 'customer',
                'title' => 'John Doe',
                'subtitle' => 'Customer • john@example.com',
                'url' => route('locale.customers', ['locale' => app()->getLocale()]),
                'icon' => '👤'
            ],
            [
                'type' => 'product',
                'title' => 'Coffee Beans',
                'subtitle' => 'Product • $15.99',
                'url' => '#',
                'icon' => '☕'
            ],
            [
                'type' => 'order',
                'title' => 'Order #1234',
                'subtitle' => 'Order • $45.99',
                'url' => '#',
                'icon' => '📋'
            ]
        ];

        $this->isSearching = false;
    }

    public function selectResult($url)
    {
        $this->closeModal();
        return $this->redirect($url, navigate: true);
    }

    public function render()
    {
        return view('livewire.search-modal');
    }
}
