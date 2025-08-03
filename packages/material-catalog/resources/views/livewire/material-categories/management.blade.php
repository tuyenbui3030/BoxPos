<div x-data="materialCategoriesManagement()">

    <!-- Flash Messages -->
    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible mb-3" role="alert" x-data="{ show: true }" x-show="show"
            x-init="setTimeout(() => show = false, 5000)" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 transform translate-y-2"
            x-transition:enter-end="opacity-100 transform translate-y-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 transform translate-y-0"
            x-transition:leave-end="opacity-0 transform translate-y-2">
            <div class="d-flex">
                <div>
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon alert-icon" width="24" height="24"
                        viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                        <path d="M5 12l5 5l10 -10" />
                    </svg>
                </div>
                <div>
                    {{ session('success') }}
                </div>
            </div>
            <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="alert alert-danger alert-dismissible mb-3" role="alert" x-data="{ show: true }" x-show="show"
            x-init="setTimeout(() => show = false, 5000)" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 transform translate-y-2"
            x-transition:enter-end="opacity-100 transform translate-y-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 transform translate-y-0"
            x-transition:leave-end="opacity-0 transform translate-y-2">
            <div class="d-flex">
                <div>
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon alert-icon" width="24" height="24"
                        viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                        <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" />
                        <path d="M12 9v4" />
                        <path d="M12 17h.01" />
                    </svg>
                </div>
                <div>
                    {{ session('error') }}
                </div>
            </div>
            <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
        </div>
    @endif

    <!-- Main Content Layout -->
    <div class="row">
        <!-- Filter Sidebar -->
        @if ($showFilters)
            <div class="col-lg-3" x-show="true" x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 transform -translate-x-4"
                x-transition:enter-end="opacity-100 transform translate-x-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 transform translate-x-0"
                x-transition:leave-end="opacity-0 transform -translate-x-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Bộ lọc</h3>
                    <div class="card-actions" x-data="{ isClearing: false }">
                        <button
                            x-on:click="isClearing = true; $wire.clearFilters().finally(() => isClearing = false)"
                            class="btn btn-outline-primary btn-sm"
                            x-bind:disabled="isClearing">
                            <span x-show="!isClearing">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-sm" width="24"
                                    height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                    fill="none" stroke-linecap="round" stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                    <path d="M20 11a8.1 8.1 0 0 0 -15.5 -2m-.5 -4v4h4" />
                                    <path d="M4 13a8.1 8.1 0 0 0 15.5 2m.5 4v-4h-4" />
                                </svg>
                                Xóa bộ lọc
                            </span>
                            <span x-show="isClearing" x-cloak>
                                <svg class="animate-spin icon icon-sm" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                        stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                    </path>
                                </svg>
                                Đang xóa...
                            </span>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                        <!-- Search -->
                        <div class="mb-3">
                            <label class="form-label">Tìm kiếm</label>
                            <div class="input-icon">
                                <span class="input-icon-addon">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24"
                                        height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                        fill="none" stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                        <path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0" />
                                        <path d="M21 21l-6 -6" />
                                    </svg>
                                </span>
                                <input wire:model.live.debounce.300ms="search" type="text" class="form-control"
                                    placeholder="Tìm theo mã, tên danh mục...">
                            </div>
                        </div>

                        <!-- Parent Category Filter -->
                        <div class="mb-3">
                            <label class="form-label">Danh mục cha</label>
                            <select wire:model.live="filters.parent_id" class="form-select">
                                <option value="">Tất cả danh mục</option>
                                <option value="0">Danh mục gốc</option>
                                @foreach($parentCategories as $parent)
                                    <option value="{{ $parent->id }}">{{ $parent->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Status Filter -->
                        <div class="mb-3">
                            <label class="form-label">Trạng thái</label>
                            <div class="form-selectgroup">
                                <label class="form-selectgroup-item">
                                    <input type="radio" name="status_filter" value="" class="form-selectgroup-input"
                                           wire:model.live="filters.is_active" {{ $filters['is_active'] === '' ? 'checked' : '' }} />
                                    <span class="form-selectgroup-label">Tất cả</span>
                                </label>
                                <label class="form-selectgroup-item">
                                    <input type="radio" name="status_filter" value="1" class="form-selectgroup-input"
                                           wire:model.live="filters.is_active" {{ $filters['is_active'] === '1' ? 'checked' : '' }} />
                                    <span class="form-selectgroup-label">Hoạt động</span>
                                </label>
                                <label class="form-selectgroup-item">
                                    <input type="radio" name="status_filter" value="0" class="form-selectgroup-input"
                                           wire:model.live="filters.is_active" {{ $filters['is_active'] === '0' ? 'checked' : '' }} />
                                    <span class="form-selectgroup-label">Tạm dừng</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Main Content -->
        <div class="{{ $showFilters ? 'col-lg-9' : 'col-12' }}">

            {{-- Categories Table Card --}}
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        Danh sách danh mục
                    </h3>
                    <div class="card-actions">
                        <div class="d-flex">
                            <div class="input-icon me-3">
                                <span class="input-icon-addon">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24"
                                        height="24" viewBox="0 0 24 24" stroke-width="2"
                                        stroke="currentColor" fill="none" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                        <path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0" />
                                        <path d="M21 21l-6 -6" />
                                    </svg>
                                </span>
                                <input wire:model.live.debounce.300ms="search" type="text"
                                    class="form-control" placeholder="Tìm kiếm danh mục...">
                            </div>
                            <div class="btn-list">
                                <button wire:click="openCreateModal" class="btn btn-primary">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                        <path d="M12 5l0 14" />
                                        <path d="M5 12l14 0" />
                                    </svg>
                                    Thêm danh mục
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead>
                            <tr>
                                <th class="w-1">STT</th>
                                <th>Danh mục</th>
                                <th>Mã</th>
                                <th>Danh mục cha</th>
                                <th>Thứ tự</th>
                                <th>Trạng thái</th>
                                <th class="w-1">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($categories as $index => $category)
                                <tr>
                                    <td>
                                        <span class="text-muted">{{ ($categories->currentPage() - 1) * $categories->perPage() + $index + 1 }}</span>
                                    </td>
                                    <td>
                                        <div class="d-flex py-1 align-items-center">
                                            @if($category->icon)
                                                <span class="avatar avatar-sm me-2" style="background-color: {{ $category->color ?? '#6c757d' }}">
                                                    <i class="{{ $category->icon }}"></i>
                                                </span>
                                            @endif
                                            <div class="flex-fill">
                                                <div class="font-weight-medium">
                                                    {{ str_repeat('— ', $category->level) }}{{ $category->name }}
                                                </div>
                                                @if($category->description)
                                                    <div class="text-muted">{{ Str::limit($category->description, 50) }}</div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="text-muted">{{ $category->code }}</span>
                                    </td>
                                    <td>
                                        @if($category->parent)
                                            <span class="badge bg-blue-lt">{{ $category->parent->name }}</span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        {{ $category->sort_order }}
                                    </td>
                                    <td>
                                        <label class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" 
                                                   {{ $category->is_active ? 'checked' : '' }}
                                                   wire:click="toggleStatus({{ $category->id }})">
                                        </label>
                                    </td>
                                    <td>
                                        <div class="btn-list flex-nowrap">
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                    Thao tác
                                                </button>
                                                <div class="dropdown-menu">
                                                    <a class="dropdown-item" href="#" wire:click.prevent="openEditModal({{ $category->id }})">
                                                        <i class="icon ti ti-edit me-2"></i>
                                                        Sửa
                                                    </a>
                                                    <a class="dropdown-item" href="#" wire:click.prevent="openCreateModal({{ $category->id }})">
                                                        <i class="icon ti ti-plus me-2"></i>
                                                        Thêm danh mục con
                                                    </a>
                                                    <div class="dropdown-divider"></div>
                                                    <a class="dropdown-item text-danger" href="#" wire:click.prevent="openDeleteModal({{ $category->id }})">
                                                        <i class="icon ti ti-trash me-2"></i>
                                                        Xóa
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8">
                                        <div class="empty">
                                            <div class="empty-img">
                                                <img src="{{ asset('static/illustrations/undraw_void_3ggu.svg') }}" height="128"
                                                    class="empty-img" alt="">
                                            </div>
                                            <p class="empty-title">Chưa có danh mục nào</p>
                                            <p class="empty-subtitle text-muted">
                                                Hãy thêm danh mục đầu tiên để bắt đầu phân loại vật liệu
                                            </p>
                                            <div class="empty-action">
                                                <button wire:click="openCreateModal" class="btn btn-primary">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon"
                                                        width="24" height="24" viewBox="0 0 24 24"
                                                        stroke-width="2" stroke="currentColor" fill="none"
                                                        stroke-linecap="round" stroke-linejoin="round">
                                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                        <path d="M12 5l0 14" />
                                                        <path d="M5 12l14 0" />
                                                    </svg>
                                                    Thêm danh mục đầu tiên
                                                </button>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($categories->hasPages())
                    <div class="card-footer d-flex align-items-center">
                        <p class="m-0 text-muted">
                            Showing <span class="badge bg-secondary fw-normal text-white">{{ $categories->firstItem() }}</span>
                            to <span class="badge bg-secondary fw-normal text-white">{{ $categories->lastItem() }}</span>
                            of <span class="badge bg-secondary fw-normal text-white">{{ $categories->total() }}</span>
                            results
                        </p>
                        @if ($categories->hasPages())
                            <ul class="pagination m-0 ms-auto">
                                {{ $categories->links('custom.pagination') }}
                            </ul>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Create/Edit Modal --}}
    @if($showCreateModal || $showEditModal)
        <div class="modal modal-blur fade show" style="display: block;" tabindex="-1">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            {{ $showCreateModal ? 'Thêm danh mục' : 'Sửa danh mục' }}
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeModals"></button>
                    </div>
                    <form wire:submit="save">
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Danh mục cha</label>
                                        <select class="form-select @error('form.parent_id') is-invalid @enderror" wire:model="form.parent_id">
                                            <option value="">Không có (Danh mục gốc)</option>
                                            @foreach($parentCategories as $parent)
                                                <option value="{{ $parent->id }}">{{ $parent->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('form.parent_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label required">Mã danh mục</label>
                                        <input type="text" class="form-control @error('form.code') is-invalid @enderror" 
                                               wire:model="form.code" placeholder="VD: cement, steel, brick...">
                                        @error('form.code')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label required">Tên danh mục</label>
                                <input type="text" class="form-control @error('form.name') is-invalid @enderror" 
                                       wire:model="form.name" placeholder="VD: Xi măng, Thép, Gạch...">
                                @error('form.name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Icon</label>
                                        <input type="text" class="form-control @error('form.icon') is-invalid @enderror" 
                                               wire:model="form.icon" placeholder="VD: ti ti-building-warehouse">
                                        @error('form.icon')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="form-hint">Sử dụng Tabler Icons (ti ti-icon-name)</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label">Màu sắc</label>
                                        <input type="color" class="form-control form-control-color @error('form.color') is-invalid @enderror" 
                                               wire:model="form.color">
                                        @error('form.color')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label">Thứ tự</label>
                                        <input type="number" class="form-control @error('form.sort_order') is-invalid @enderror" 
                                               wire:model="form.sort_order" min="0">
                                        @error('form.sort_order')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Mô tả</label>
                                <textarea class="form-control @error('form.description') is-invalid @enderror" 
                                          wire:model="form.description" rows="3" placeholder="Mô tả chi tiết về danh mục..."></textarea>
                                @error('form.description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Meta Title (SEO)</label>
                                        <input type="text" class="form-control @error('form.meta_title') is-invalid @enderror" 
                                               wire:model="form.meta_title" placeholder="Tiêu đề SEO...">
                                        @error('form.meta_title')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Trạng thái</label>
                                        <div>
                                            <label class="form-check">
                                                <input class="form-check-input" type="checkbox" wire:model="form.is_active">
                                                <span class="form-check-label">Hoạt động</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Meta Description (SEO)</label>
                                <textarea class="form-control @error('form.meta_description') is-invalid @enderror" 
                                          wire:model="form.meta_description" rows="2" placeholder="Mô tả SEO..."></textarea>
                                @error('form.meta_description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" wire:click="closeModals">Hủy</button>
                            <button type="submit" class="btn btn-primary">
                                {{ $showCreateModal ? 'Tạo mới' : 'Cập nhật' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="modal-backdrop fade show"></div>
    @endif

    {{-- Delete Modal --}}
    @if($showDeleteModal)
        <div class="modal modal-blur fade show" style="display: block;" tabindex="-1">
            <div class="modal-dialog modal-sm modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-body">
                        <div class="modal-title">Xác nhận xóa?</div>
                        <div>Bạn có chắc chắn muốn xóa danh mục <strong>{{ $selectedCategory?->name }}</strong>?</div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeModals">Hủy</button>
                        <button type="button" class="btn btn-danger" wire:click="delete">Xóa</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-backdrop fade show"></div>
    @endif
</div>
