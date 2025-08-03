<?php

namespace Packages\MaterialCatalog\Livewire\BuildingMaterials;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Packages\MaterialCatalog\Models\BuildingMaterial;
use Packages\MaterialCatalog\Models\MaterialCategory;
use Packages\MaterialCatalog\Models\MaterialUnit;
use Packages\MaterialCatalog\Services\BuildingMaterialService;
use Packages\Log\Traits\Loggable;

class BuildingMaterialsManagement extends Component
{
    use WithPagination, WithFileUploads, Loggable;

    // Search and filter properties
    public string $search = '';
    public array $filters = [
        'category_id' => '',
        'brand' => '',
        'is_active' => '',
        'is_featured' => '',
        'is_hazardous' => '',
    ];

    // Modal properties
    public bool $showCreateModal = false;
    public bool $showEditModal = false;
    public bool $showDeleteModal = false;
    public bool $showDetailModal = false;

    // Form properties
    public array $form = [
        'category_id' => '',
        'primary_unit_id' => '',
        'material_code' => '',
        'name' => '',
        'description' => '',
        'short_description' => '',
        'brand' => '',
        'model' => '',
        'origin_country' => 'Vietnam',
        'weight_per_unit' => null,
        'is_hazardous' => false,
        'is_active' => true,
        'is_featured' => false,
        'track_serial_numbers' => false,
        'track_batch_numbers' => false,
        'requires_quality_check' => false,
        'shelf_life_days' => null,
        'tags' => [],
        'internal_notes' => '',
    ];

    public $images = [];
    public ?BuildingMaterial $selectedMaterial = null;

    // UI state
    public $showFilters = true;

    protected BuildingMaterialService $buildingMaterialService;

    public function boot(BuildingMaterialService $buildingMaterialService)
    {
        $this->buildingMaterialService = $buildingMaterialService;
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
        'filters.category_id' => ['except' => ''],
        'filters.brand' => ['except' => ''],
        'filters.is_active' => ['except' => ''],
        'filters.is_featured' => ['except' => ''],
        'filters.is_hazardous' => ['except' => ''],
    ];

    public function toggleFilters()
    {
        $this->showFilters = !$this->showFilters;
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->filters = [
            'category_id' => '',
            'brand' => '',
            'is_active' => '',
            'is_featured' => '',
            'is_hazardous' => '',
        ];
        $this->resetPage();
    }

    public function mount()
    {
        $this->logActivity('building_materials_page_accessed', [
            'user_id' => auth()->id(),
            'store_id' => auth()->user()->current_store_id,
        ]);
    }

    public function getMaterialsProperty()
    {
        return BuildingMaterial::query()
            ->with(['category', 'primaryUnit'])
            ->when($this->search, fn($q) => $q->search($this->search))
            ->when($this->filters['category_id'], fn($q) => $q->byCategory($this->filters['category_id']))
            ->when($this->filters['brand'], fn($q) => $q->byBrand($this->filters['brand']))
            ->when($this->filters['is_active'] !== '', fn($q) => 
                $this->filters['is_active'] ? $q->active() : $q->where('is_active', false))
            ->when($this->filters['is_featured'] !== '', fn($q) => 
                $this->filters['is_featured'] ? $q->featured() : $q->where('is_featured', false))
            ->when($this->filters['is_hazardous'] !== '', fn($q) => 
                $this->filters['is_hazardous'] ? $q->where('is_hazardous', true) : $q->where('is_hazardous', false))
            ->orderBy('name')
            ->paginate(10);
    }

    public function getCategoriesProperty()
    {
        return MaterialCategory::active()
            ->orderByHierarchy()
            ->get();
    }

    public function getUnitsProperty()
    {
        return MaterialUnit::active()
            ->orderByTypeAndName()
            ->get();
    }

    public function getBrandsProperty()
    {
        return BuildingMaterial::query()
            ->whereNotNull('brand')
            ->where('brand', '!=', '')
            ->distinct()
            ->pluck('brand')
            ->sort()
            ->values();
    }

    public function updatedSearch()
    {
        $this->resetPage();
        $this->logActivity('building_materials_search', [
            'search_term' => $this->search,
            'user_id' => auth()->id(),
        ]);
    }

    public function updatedFilters()
    {
        $this->resetPage();
        $this->logActivity('building_materials_filtered', [
            'filters' => $this->filters,
            'user_id' => auth()->id(),
        ]);
    }

    public function openCreateModal()
    {
        $this->resetForm();
        $this->form['material_code'] = BuildingMaterial::generateMaterialCode(auth()->user()->current_store_id);
        $this->showCreateModal = true;
        $this->logActivity('building_material_create_modal_opened', [
            'user_id' => auth()->id(),
        ]);
    }

    public function openEditModal(BuildingMaterial $material)
    {
        $this->selectedMaterial = $material;
        $this->form = [
            'category_id' => $material->category_id,
            'primary_unit_id' => $material->primary_unit_id,
            'material_code' => $material->material_code,
            'name' => $material->name,
            'description' => $material->description ?? '',
            'short_description' => $material->short_description ?? '',
            'brand' => $material->brand ?? '',
            'model' => $material->model ?? '',
            'origin_country' => $material->origin_country ?? 'Vietnam',
            'weight_per_unit' => $material->weight_per_unit,
            'is_hazardous' => $material->is_hazardous,
            'is_active' => $material->is_active,
            'is_featured' => $material->is_featured,
            'track_serial_numbers' => $material->track_serial_numbers,
            'track_batch_numbers' => $material->track_batch_numbers,
            'requires_quality_check' => $material->requires_quality_check,
            'shelf_life_days' => $material->shelf_life_days,
            'tags' => $material->tags ?? [],
            'internal_notes' => $material->internal_notes ?? '',
        ];
        $this->showEditModal = true;
        $this->logActivity('building_material_edit_modal_opened', [
            'material_id' => $material->id,
            'user_id' => auth()->id(),
        ]);
    }

    public function openDetailModal(BuildingMaterial $material)
    {
        $this->selectedMaterial = $material->load(['category', 'primaryUnit', 'specifications']);
        $this->showDetailModal = true;
        $this->logActivity('building_material_detail_modal_opened', [
            'material_id' => $material->id,
            'user_id' => auth()->id(),
        ]);
    }

    public function openDeleteModal(BuildingMaterial $material)
    {
        $this->selectedMaterial = $material;
        $this->showDeleteModal = true;
        $this->logActivity('building_material_delete_modal_opened', [
            'material_id' => $material->id,
            'user_id' => auth()->id(),
        ]);
    }

    public function closeModals()
    {
        $this->showCreateModal = false;
        $this->showEditModal = false;
        $this->showDeleteModal = false;
        $this->showDetailModal = false;
        $this->selectedMaterial = null;
        $this->images = [];
        $this->resetForm();
    }

    public function save()
    {
        $this->validate([
            'form.category_id' => 'required|exists:material_categories,id',
            'form.primary_unit_id' => 'required|exists:material_units,id',
            'form.material_code' => 'required|string|max:20',
            'form.name' => 'required|string|max:200',
            'form.description' => 'nullable|string|max:1000',
            'form.short_description' => 'nullable|string|max:500',
            'form.brand' => 'nullable|string|max:100',
            'form.model' => 'nullable|string|max:100',
            'form.origin_country' => 'nullable|string|max:100',
            'form.weight_per_unit' => 'nullable|numeric|min:0',
            'form.shelf_life_days' => 'nullable|integer|min:1',
            'images.*' => 'nullable|image|max:2048',
        ]);

        try {
            if ($this->selectedMaterial) {
                // Update existing material
                $material = $this->buildingMaterialService->updateBuildingMaterial(
                    $this->selectedMaterial,
                    $this->form,
                    $this->images
                );
                $this->logActivity('building_material_updated', [
                    'material_id' => $material->id,
                    'user_id' => auth()->id(),
                ]);
                session()->flash('success', 'Vật liệu đã được cập nhật thành công!');
            } else {
                // Create new material
                $material = $this->buildingMaterialService->createBuildingMaterial(
                    $this->form,
                    $this->images
                );
                $this->logActivity('building_material_created', [
                    'material_id' => $material->id,
                    'user_id' => auth()->id(),
                ]);
                session()->flash('success', 'Vật liệu đã được tạo thành công!');
            }

            $this->closeModals();
        } catch (\Exception $e) {
            $this->logError($e, [
                'action' => $this->selectedMaterial ? 'update_building_material' : 'create_building_material',
                'form_data' => $this->form,
                'user_id' => auth()->id(),
            ]);
            session()->flash('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    public function delete()
    {
        if (!$this->selectedMaterial) {
            return;
        }

        try {
            $this->buildingMaterialService->deleteBuildingMaterial($this->selectedMaterial);
            $this->logActivity('building_material_deleted', [
                'material_id' => $this->selectedMaterial->id,
                'user_id' => auth()->id(),
            ]);
            session()->flash('success', 'Vật liệu đã được xóa thành công!');
            $this->closeModals();
        } catch (\Exception $e) {
            $this->logError($e, [
                'action' => 'delete_building_material',
                'material_id' => $this->selectedMaterial->id,
                'user_id' => auth()->id(),
            ]);
            session()->flash('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    public function toggleStatus(BuildingMaterial $material)
    {
        try {
            $this->buildingMaterialService->toggleStatus($material);
            $this->logActivity('building_material_status_toggled', [
                'material_id' => $material->id,
                'new_status' => !$material->is_active,
                'user_id' => auth()->id(),
            ]);
            session()->flash('success', 'Trạng thái vật liệu đã được cập nhật!');
        } catch (\Exception $e) {
            $this->logError($e, [
                'action' => 'toggle_building_material_status',
                'material_id' => $material->id,
                'user_id' => auth()->id(),
            ]);
            session()->flash('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    private function resetForm()
    {
        $this->form = [
            'category_id' => '',
            'primary_unit_id' => '',
            'material_code' => '',
            'name' => '',
            'description' => '',
            'short_description' => '',
            'brand' => '',
            'model' => '',
            'origin_country' => 'Vietnam',
            'weight_per_unit' => null,
            'is_hazardous' => false,
            'is_active' => true,
            'is_featured' => false,
            'track_serial_numbers' => false,
            'track_batch_numbers' => false,
            'requires_quality_check' => false,
            'shelf_life_days' => null,
            'tags' => [],
            'internal_notes' => '',
        ];
    }

    public function getTotalMaterials()
    {
        return BuildingMaterial::count();
    }

    public function getActiveMaterials()
    {
        return BuildingMaterial::active()->count();
    }

    public function getFeaturedMaterials()
    {
        return BuildingMaterial::where('is_featured', true)->count();
    }

    public function getHazardousMaterials()
    {
        return BuildingMaterial::where('is_hazardous', true)->count();
    }

    public function render()
    {
        return view('material-catalog::livewire.building-materials.management', [
            'materials' => $this->materials,
            'categories' => $this->categories,
            'units' => $this->units,
            'brands' => $this->brands,
        ])->layout('layouts.app', [
            'header' => 'Sản phẩm trong kho'
        ]);
    }
}
