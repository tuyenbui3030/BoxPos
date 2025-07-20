<?php

namespace Packages\MaterialCatalog\Livewire\MaterialUnits;

use Livewire\Component;
use Livewire\WithPagination;
use Packages\MaterialCatalog\Models\MaterialUnit;
use Packages\MaterialCatalog\Services\MaterialUnitService;
use Packages\Log\Traits\Loggable;

class MaterialUnitsManagement extends Component
{
    use WithPagination, Loggable;

    // Search and filter properties
    public string $search = '';
    public array $filters = [
        'type' => '',
        'is_active' => '',
        'is_default' => '',
    ];

    // Modal properties
    public bool $showCreateModal = false;
    public bool $showEditModal = false;
    public bool $showDeleteModal = false;

    // Form properties
    public array $form = [
        'code' => '',
        'name' => '',
        'symbol' => '',
        'type' => '',
        'conversion_factor' => 1,
        'is_active' => true,
        'is_default' => false,
        'description' => '',
    ];

    public ?MaterialUnit $selectedUnit = null;

    // UI state
    public $showFilters = true;

    // Available options
    public array $unitTypes = [
        'weight' => 'Khối lượng',
        'volume' => 'Thể tích',
        'length' => 'Chiều dài',
        'area' => 'Diện tích',
        'count' => 'Số lượng',
        'time' => 'Thời gian',
    ];

    protected MaterialUnitService $materialUnitService;

    public function boot(MaterialUnitService $materialUnitService)
    {
        $this->materialUnitService = $materialUnitService;
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilters()
    {
        $this->resetPage();
    }

    protected $queryString = [
        'search' => ['except' => ''],
        'filters.type' => ['except' => ''],
        'filters.is_active' => ['except' => ''],
        'filters.is_default' => ['except' => ''],
    ];

    public function mount()
    {
        $this->logActivity('material_units_page_accessed', [
            'user_id' => auth()->id(),
            'store_id' => auth()->user()->current_store_id,
        ]);
    }

    public function getUnitsProperty()
    {
        return MaterialUnit::query()
            ->when($this->search, fn($q) => $q->search($this->search))
            ->when($this->filters['type'], fn($q) => $q->byType($this->filters['type']))
            ->when($this->filters['is_active'] !== '', fn($q) => 
                $this->filters['is_active'] ? $q->active() : $q->inactive())
            ->when($this->filters['is_default'] !== '', fn($q) => 
                $this->filters['is_default'] ? $q->defaults() : $q->where('is_default', false))
            ->orderByTypeAndName()
            ->paginate(10);
    }

    public function updatedSearch()
    {
        $this->resetPage();
        $this->logActivity('material_units_search', [
            'search_term' => $this->search,
            'user_id' => auth()->id(),
        ]);
    }

    public function updatedFilters()
    {
        $this->resetPage();
        $this->logActivity('material_units_filtered', [
            'filters' => $this->filters,
            'user_id' => auth()->id(),
        ]);
    }

    public function openCreateModal()
    {
        $this->resetForm();
        $this->showCreateModal = true;
        $this->logActivity('material_unit_create_modal_opened', [
            'user_id' => auth()->id(),
        ]);
    }

    public function openEditModal(MaterialUnit $unit)
    {
        $this->selectedUnit = $unit;
        $this->form = [
            'code' => $unit->code,
            'name' => $unit->name,
            'symbol' => $unit->symbol,
            'type' => $unit->type,
            'conversion_factor' => $unit->conversion_factor,
            'is_active' => $unit->is_active,
            'is_default' => $unit->is_default,
            'description' => $unit->description ?? '',
        ];
        $this->showEditModal = true;
        $this->logActivity('material_unit_edit_modal_opened', [
            'unit_id' => $unit->id,
            'user_id' => auth()->id(),
        ]);
    }

    public function openDeleteModal(MaterialUnit $unit)
    {
        $this->selectedUnit = $unit;
        $this->showDeleteModal = true;
        $this->logActivity('material_unit_delete_modal_opened', [
            'unit_id' => $unit->id,
            'user_id' => auth()->id(),
        ]);
    }

    public function closeModals()
    {
        $this->showCreateModal = false;
        $this->showEditModal = false;
        $this->showDeleteModal = false;
        $this->selectedUnit = null;
        $this->resetForm();
    }

    public function save()
    {
        $this->validate([
            'form.code' => 'required|string|max:20',
            'form.name' => 'required|string|max:100',
            'form.symbol' => 'required|string|max:10',
            'form.type' => 'required|in:' . implode(',', array_keys($this->unitTypes)),
            'form.conversion_factor' => 'required|numeric|min:0.001',
            'form.is_active' => 'boolean',
            'form.is_default' => 'boolean',
            'form.description' => 'nullable|string|max:500',
        ]);

        try {
            if ($this->selectedUnit) {
                // Update existing unit
                $unit = $this->materialUnitService->updateMaterialUnit(
                    $this->selectedUnit,
                    $this->form
                );
                $this->logActivity('material_unit_updated', [
                    'unit_id' => $unit->id,
                    'user_id' => auth()->id(),
                ]);
                session()->flash('success', 'Đơn vị tính đã được cập nhật thành công!');
            } else {
                // Create new unit
                $unit = $this->materialUnitService->createMaterialUnit($this->form);
                $this->logActivity('material_unit_created', [
                    'unit_id' => $unit->id,
                    'user_id' => auth()->id(),
                ]);
                session()->flash('success', 'Đơn vị tính đã được tạo thành công!');
            }

            $this->closeModals();
        } catch (\Exception $e) {
            $this->logError($e, [
                'action' => $this->selectedUnit ? 'update_material_unit' : 'create_material_unit',
                'form_data' => $this->form,
                'user_id' => auth()->id(),
            ]);
            session()->flash('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    public function delete()
    {
        if (!$this->selectedUnit) {
            return;
        }

        try {
            $this->materialUnitService->deleteMaterialUnit($this->selectedUnit);
            $this->logActivity('material_unit_deleted', [
                'unit_id' => $this->selectedUnit->id,
                'user_id' => auth()->id(),
            ]);
            session()->flash('success', 'Đơn vị tính đã được xóa thành công!');
            $this->closeModals();
        } catch (\Exception $e) {
            $this->logError($e, [
                'action' => 'delete_material_unit',
                'unit_id' => $this->selectedUnit->id,
                'user_id' => auth()->id(),
            ]);
            session()->flash('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    public function toggleStatus(MaterialUnit $unit)
    {
        try {
            $this->materialUnitService->toggleStatus($unit);
            $this->logActivity('material_unit_status_toggled', [
                'unit_id' => $unit->id,
                'new_status' => !$unit->is_active,
                'user_id' => auth()->id(),
            ]);
            session()->flash('success', 'Trạng thái đơn vị tính đã được cập nhật!');
        } catch (\Exception $e) {
            $this->logError($e, [
                'action' => 'toggle_material_unit_status',
                'unit_id' => $unit->id,
                'user_id' => auth()->id(),
            ]);
            session()->flash('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    private function resetForm()
    {
        $this->form = [
            'code' => '',
            'name' => '',
            'symbol' => '',
            'type' => '',
            'conversion_factor' => 1,
            'is_active' => true,
            'is_default' => false,
            'description' => '',
        ];
    }

    public function clearFilters()
    {
        $this->logActivity('material_unit_filters_cleared', [
            'user_id' => auth()->id(),
            'component' => 'MaterialUnitsManagement',
            'previous_filters' => [
                'search' => $this->search,
                'type' => $this->filters['type'] ?? '',
                'is_active' => $this->filters['is_active'] ?? '',
                'is_default' => $this->filters['is_default'] ?? '',
            ],
        ]);

        $this->reset([
            'search',
            'filters',
        ]);
        $this->resetPage();
    }

    public function toggleFilters()
    {
        $this->logActivity('material_unit_filters_toggled', [
            'user_id' => auth()->id(),
            'component' => 'MaterialUnitsManagement',
            'filters_visible' => !$this->showFilters,
        ]);

        $this->showFilters = !$this->showFilters;
    }

    public function getTotalUnits()
    {
        return MaterialUnit::count();
    }

    public function getActiveUnits()
    {
        return MaterialUnit::active()->count();
    }

    public function getUnitTypesCount()
    {
        return MaterialUnit::distinct('type')->count('type');
    }

    public function getDefaultUnits()
    {
        return MaterialUnit::defaults()->count();
    }

    public function render()
    {
        return view('material-catalog::livewire.material-units.management', [
            'units' => $this->units,
            'unitTypes' => $this->unitTypes,
        ])->layout('layouts.app');
    }
}
