<?php

namespace Packages\MaterialCatalog\Livewire\MaterialCategories;

use Livewire\Component;
use Livewire\WithPagination;
use Packages\MaterialCatalog\Models\MaterialCategory;
use Packages\MaterialCatalog\Services\MaterialCategoryService;
use Packages\Log\Traits\Loggable;

class MaterialCategoriesManagement extends Component
{
    use WithPagination, Loggable;

    // Search and filter properties
    public string $search = '';
    public array $filters = [
        'parent_id' => '',
        'is_active' => '',
        'level' => '',
    ];

    // Modal properties
    public bool $showCreateModal = false;
    public bool $showEditModal = false;
    public bool $showDeleteModal = false;

    // Form properties
    public array $form = [
        'parent_id' => null,
        'code' => '',
        'name' => '',
        'description' => '',
        'icon' => '',
        'color' => '#6c757d',
        'sort_order' => 0,
        'is_active' => true,
        'meta_title' => '',
        'meta_description' => '',
    ];

    public ?MaterialCategory $selectedCategory = null;

    // UI state
    public $showFilters = true;

    protected MaterialCategoryService $materialCategoryService;

    public function boot(MaterialCategoryService $materialCategoryService)
    {
        $this->materialCategoryService = $materialCategoryService;
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
        'filters.parent_id' => ['except' => ''],
        'filters.is_active' => ['except' => ''],
        'filters.level' => ['except' => ''],
    ];

    public function toggleFilters()
    {
        $this->showFilters = !$this->showFilters;
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->filters = [
            'parent_id' => '',
            'is_active' => '',
            'level' => '',
        ];
        $this->resetPage();
    }

    public function mount()
    {
        $this->logActivity('material_categories_page_accessed', [
            'user_id' => auth()->id(),
            'store_id' => auth()->user()->current_store_id,
        ]);
    }

    public function getCategoriesProperty()
    {
        return MaterialCategory::query()
            ->when($this->search, fn($q) => $q->search($this->search))
            ->when($this->filters['parent_id'] !== '', function($q) {
                if ($this->filters['parent_id'] === '0') {
                    return $q->whereNull('parent_id'); // Root categories
                } else {
                    return $q->where('parent_id', $this->filters['parent_id']);
                }
            })
            ->when($this->filters['is_active'] !== '', fn($q) =>
                $this->filters['is_active'] ? $q->active() : $q->where('is_active', false))
            ->with(['parent', 'children'])
            ->orderByHierarchy()
            ->paginate(10);
    }

    public function getParentCategoriesProperty()
    {
        return MaterialCategory::query()
            ->active()
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    public function updatedSearch()
    {
        $this->resetPage();
        $this->logActivity('material_categories_search', [
            'search_term' => $this->search,
            'user_id' => auth()->id(),
        ]);
    }

    public function updatedFilters()
    {
        $this->resetPage();
        $this->logActivity('material_categories_filtered', [
            'filters' => $this->filters,
            'user_id' => auth()->id(),
        ]);
    }

    public function openCreateModal(?int $parentId = null)
    {
        $this->resetForm();
        if ($parentId) {
            $this->form['parent_id'] = $parentId;
        }
        $this->showCreateModal = true;
        $this->logActivity('material_category_create_modal_opened', [
            'parent_id' => $parentId,
            'user_id' => auth()->id(),
        ]);
    }

    public function openEditModal(MaterialCategory $category)
    {
        $this->selectedCategory = $category;
        $this->form = [
            'parent_id' => $category->parent_id,
            'code' => $category->code,
            'name' => $category->name,
            'description' => $category->description ?? '',
            'icon' => $category->icon ?? '',
            'color' => $category->color ?? '#6c757d',
            'sort_order' => $category->sort_order,
            'is_active' => $category->is_active,
            'meta_title' => $category->meta_title ?? '',
            'meta_description' => $category->meta_description ?? '',
        ];
        $this->showEditModal = true;
        $this->logActivity('material_category_edit_modal_opened', [
            'category_id' => $category->id,
            'user_id' => auth()->id(),
        ]);
    }

    public function openDeleteModal(MaterialCategory $category)
    {
        $this->selectedCategory = $category;
        $this->showDeleteModal = true;
        $this->logActivity('material_category_delete_modal_opened', [
            'category_id' => $category->id,
            'user_id' => auth()->id(),
        ]);
    }

    public function closeModals()
    {
        $this->showCreateModal = false;
        $this->showEditModal = false;
        $this->showDeleteModal = false;
        $this->selectedCategory = null;
        $this->resetForm();
    }

    public function save()
    {
        $this->validate([
            'form.parent_id' => 'nullable|exists:material_categories,id',
            'form.code' => 'required|string|max:50',
            'form.name' => 'required|string|max:100',
            'form.description' => 'nullable|string|max:500',
            'form.icon' => 'nullable|string|max:50',
            'form.color' => 'nullable|string|max:7',
            'form.sort_order' => 'integer|min:0',
            'form.is_active' => 'boolean',
            'form.meta_title' => 'nullable|string|max:200',
            'form.meta_description' => 'nullable|string|max:500',
        ]);

        try {
            if ($this->selectedCategory) {
                // Update existing category
                $category = $this->materialCategoryService->updateMaterialCategory(
                    $this->selectedCategory,
                    $this->form
                );
                $this->logActivity('material_category_updated', [
                    'category_id' => $category->id,
                    'user_id' => auth()->id(),
                ]);
                session()->flash('success', 'Danh mục đã được cập nhật thành công!');
            } else {
                // Create new category
                $category = $this->materialCategoryService->createMaterialCategory($this->form);
                $this->logActivity('material_category_created', [
                    'category_id' => $category->id,
                    'user_id' => auth()->id(),
                ]);
                session()->flash('success', 'Danh mục đã được tạo thành công!');
            }

            $this->closeModals();
        } catch (\Exception $e) {
            $this->logError($e, [
                'action' => $this->selectedCategory ? 'update_material_category' : 'create_material_category',
                'form_data' => $this->form,
                'user_id' => auth()->id(),
            ]);
            session()->flash('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    public function delete()
    {
        if (!$this->selectedCategory) {
            return;
        }

        try {
            $this->materialCategoryService->deleteMaterialCategory($this->selectedCategory);
            $this->logActivity('material_category_deleted', [
                'category_id' => $this->selectedCategory->id,
                'user_id' => auth()->id(),
            ]);
            session()->flash('success', 'Danh mục đã được xóa thành công!');
            $this->closeModals();
        } catch (\Exception $e) {
            $this->logError($e, [
                'action' => 'delete_material_category',
                'category_id' => $this->selectedCategory->id,
                'user_id' => auth()->id(),
            ]);
            session()->flash('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    public function toggleStatus(MaterialCategory $category)
    {
        try {
            $this->materialCategoryService->toggleStatus($category);
            $this->logActivity('material_category_status_toggled', [
                'category_id' => $category->id,
                'new_status' => !$category->is_active,
                'user_id' => auth()->id(),
            ]);
            session()->flash('success', 'Trạng thái danh mục đã được cập nhật!');
        } catch (\Exception $e) {
            $this->logError($e, [
                'action' => 'toggle_material_category_status',
                'category_id' => $category->id,
                'user_id' => auth()->id(),
            ]);
            session()->flash('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    private function resetForm()
    {
        $this->form = [
            'parent_id' => null,
            'code' => '',
            'name' => '',
            'description' => '',
            'icon' => '',
            'color' => '#6c757d',
            'sort_order' => 0,
            'is_active' => true,
            'meta_title' => '',
            'meta_description' => '',
        ];
    }

    public function getTotalCategories()
    {
        return MaterialCategory::count();
    }

    public function getActiveCategories()
    {
        return MaterialCategory::active()->count();
    }

    public function getRootCategories()
    {
        return MaterialCategory::whereNull('parent_id')->count();
    }

    public function render()
    {
        return view('material-catalog::livewire.material-categories.management', [
            'categories' => $this->categories,
            'parentCategories' => $this->parentCategories,
        ])->layout('layouts.app',  [
            'header' => 'Material Categories Management'
        ]);
    }
}
