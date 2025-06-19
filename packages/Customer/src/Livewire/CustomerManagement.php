<?php

namespace Packages\Customer\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Packages\Customer\Models\Customer;
use Packages\User\Models\User;
use Packages\Log\Traits\Loggable;

class CustomerManagement extends Component
{
    use WithPagination, Loggable; // ⚠️ MANDATORY: Use Loggable trait

    protected $paginationTheme = 'custom.pagination';

    // Search and pagination
    #[Url]
    public $search = '';
    public $perPage = 10;

    // Filter properties
    #[Url]
    public $filterCustomerGroup = '';
    #[Url]
    public $filterCustomerType = 'all';
    #[Url]
    public $filterGender = 'all';
    #[Url]
    public $filterLastTransactionFrom = '';
    #[Url]
    public $filterLastTransactionTo = '';
    #[Url]
    public $filterSalesFrom = '';
    #[Url]
    public $filterSalesTo = '';
    #[Url]
    public $filterOnlyWithDebt = false;

    // UI state
    public $showFilters = true;

    protected $queryString = [
        'search' => ['except' => ''],
        'filterCustomerGroup' => ['except' => ''],
        'filterCustomerType' => ['except' => 'all'],
        'filterGender' => ['except' => 'all'],
        'filterLastTransactionFrom' => ['except' => ''],
        'filterLastTransactionTo' => ['except' => ''],
        'filterSalesFrom' => ['except' => ''],
        'filterSalesTo' => ['except' => ''],
        'filterOnlyWithDebt' => ['except' => false],
    ];

    public function mount()
    {
        // Set default date ranges if needed
    }

    protected $listeners = ['customer-created' => 'refreshCustomers'];

    public function refreshCustomers()
    {
        // Reset to first page and refresh the customer list
        $this->resetPage();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterCustomerGroup()
    {
        $this->resetPage();
    }

    public function updatingFilterCustomerType()
    {
        $this->resetPage();
    }

    public function updatingFilterGender()
    {
        $this->resetPage();
    }

    public function updatingFilterOnlyWithDebt()
    {
        $this->resetPage();
    }

    public function updatingFilterLastTransactionFrom()
    {
        $this->resetPage();
    }

    public function updatingFilterLastTransactionTo()
    {
        $this->resetPage();
    }

    public function updatingFilterSalesFrom()
    {
        $this->resetPage();
    }

    public function updatingFilterSalesTo()
    {
        $this->resetPage();
    }

    public function updatingPerPage()
    {
        $this->resetPage();
    }

    public function gotoPage($page, $pageName = 'page')
    {
        $this->setPage($page, $pageName);
    }

    public function clearFilters()
    {
        // ⚠️ MANDATORY: Log user action
        $this->logActivity('customer_filters_cleared', [
            'user_id' => auth()->id(),
            'component' => 'CustomerManagement',
            'previous_filters' => [
                'search' => $this->search,
                'customer_group' => $this->filterCustomerGroup,
                'customer_type' => $this->filterCustomerType,
                'gender' => $this->filterGender,
                'has_debt_filter' => $this->filterOnlyWithDebt,
            ],
        ]);

        $this->reset([
            'search',
            'filterCustomerGroup',
            'filterCustomerType',
            'filterGender',
            'filterLastTransactionFrom',
            'filterLastTransactionTo',
            'filterSalesFrom',
            'filterSalesTo',
            'filterOnlyWithDebt',
        ]);
        $this->resetPage();
        
        // Dispatch event to clear the date picker
        $this->dispatch('clearDatePicker');
        
        // Add delay after the action is complete for smoother UX
        $this->js('
            setTimeout(() => {
                // Smooth transition completed
            }, 300);
        ');
    }

    public function toggleFilters()
    {
        // ⚠️ MANDATORY: Log user action
        $this->logActivity('customer_filters_toggled', [
            'user_id' => auth()->id(),
            'component' => 'CustomerManagement',
            'filters_visible' => !$this->showFilters, // Will be the new state
        ]);

        $this->showFilters = !$this->showFilters;
    }

    public function importCustomers()
    {
        // ⚠️ MANDATORY: Log user action
        $this->logActivity('customer_import_attempted', [
            'user_id' => auth()->id(),
            'component' => 'CustomerManagement',
            'status' => 'placeholder_not_implemented',
        ]);

        // Placeholder for import functionality
        session()->flash('info', 'Import functionality will be implemented soon!');
    }

    public function editCustomer($customerId)
    {
        // ⚠️ MANDATORY: Log user action
        $this->logActivity('customer_edit_attempted', [
            'user_id' => auth()->id(),
            'customer_id' => $customerId,
            'component' => 'CustomerManagement',
            'status' => 'placeholder_not_implemented',
        ]);

        // Placeholder for edit functionality
        session()->flash('info', 'Edit customer functionality will be implemented soon!');
    }

    public function viewCustomer($customerId)
    {
        // ⚠️ MANDATORY: Log user action
        $this->logActivity('customer_view_attempted', [
            'user_id' => auth()->id(),
            'customer_id' => $customerId,
            'component' => 'CustomerManagement',
            'status' => 'placeholder_not_implemented',
        ]);

        // Placeholder for view functionality
        session()->flash('info', 'View customer functionality will be implemented soon!');
    }

    public function getCustomersProperty()
    {
        // ⚠️ MANDATORY: Log search activity (throttled to avoid excessive logging)
        if ($this->search && strlen($this->search) >= 2) {
            $this->logActivity('customer_search_performed', [
                'user_id' => auth()->id(),
                'component' => 'CustomerManagement',
                'search_term' => $this->search,
                'active_filters' => [
                    'customer_group' => $this->filterCustomerGroup,
                    'customer_type' => $this->filterCustomerType,
                    'gender' => $this->filterGender,
                    'has_debt' => $this->filterOnlyWithDebt,
                ],
            ]);
        }

        $query = Customer::query()
            ->withCreator()
            ->when($this->search, fn($query) => $query->search($this->search))
            ->when($this->filterCustomerGroup, fn($query) => $query->byGroup($this->filterCustomerGroup))
            ->when($this->filterCustomerType !== 'all', fn($query) => $query->byType($this->filterCustomerType))
            ->when($this->filterGender !== 'all', fn($query) => $query->byGender($this->filterGender))
            ->when($this->filterLastTransactionFrom && $this->filterLastTransactionTo, 
                fn($query) => $query->lastTransactionBetween($this->filterLastTransactionFrom, $this->filterLastTransactionTo))
            ->when($this->filterSalesFrom !== '' && $this->filterSalesTo !== '', 
                fn($query) => $query->salesBetween($this->filterSalesFrom, $this->filterSalesTo))
            ->when($this->filterSalesFrom !== '' && $this->filterSalesTo === '', 
                fn($query) => $query->salesAbove($this->filterSalesFrom))
            ->when($this->filterSalesFrom === '' && $this->filterSalesTo !== '', 
                fn($query) => $query->salesBelow($this->filterSalesTo))
            ->when($this->filterOnlyWithDebt, fn($query) => $query->withDebt())
            ->orderByName();

        return $query->paginate($this->perPage);
    }

    public function getCustomerGroupsProperty()
    {
        return Customer::select('customer_group')
            ->whereNotNull('customer_group')
            ->distinct()
            ->pluck('customer_group')
            ->sort();
    }

    public function getCreatorsProperty()
    {
        return User::select('name')
            ->distinct()
            ->pluck('name')
            ->sort();
    }

    #[Title('Customer Management')]
    public function render()
    {
        return view('customer::livewire.customer-management', [
            'customers' => $this->customers,
            'customerGroups' => $this->customerGroups,
            'creators' => $this->creators,
        ])->layout('layouts.app', [
            'header' => 'Customer Management'
        ]);
    }
}
