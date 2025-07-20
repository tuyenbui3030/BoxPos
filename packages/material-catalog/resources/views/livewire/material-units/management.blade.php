<div x-data="materialUnitsManagement()">
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

    <!-- Page Header -->
    <div class="page-header d-print-none mb-4">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <div class="page-pretitle">
                        Quản lý danh mục
                    </div>
                    <h2 class="page-title">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon me-2" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                            <path d="M12 3l8 4.5l0 9l-8 4.5l-8 -4.5l0 -9l8 -4.5"/>
                            <path d="M12 12l8 -4.5"/>
                            <path d="M12 12l0 9"/>
                            <path d="M12 12l-8 -4.5"/>
                        </svg>
                        Đơn vị tính
                    </h2>
                    <div class="text-muted mt-1">Quản lý các đơn vị đo lường trong hệ thống</div>
                </div>

            </div>
        </div>
    </div>

    <!-- Overview Section -->
    <div class="row row-deck row-cards mb-3">
        <div class="col-sm-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="subheader">Tổng đơn vị</div>
                        <div class="ms-auto lh-1">
                            <div class="dropdown">
                                <a class="dropdown-toggle text-muted" href="#" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Tất cả</a>
                            </div>
                        </div>
                    </div>
                    <div class="h1 mb-3">{{ $this->getTotalUnits() }}</div>
                    <div class="d-flex mb-2">
                        <div>Đơn vị tính trong hệ thống</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="subheader">Đang hoạt động</div>
                    </div>
                    <div class="h1 mb-3 text-green">{{ $this->getActiveUnits() }}</div>
                    <div class="d-flex mb-2">
                        <div>Đơn vị đang sử dụng</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="subheader">Loại đơn vị</div>
                    </div>
                    <div class="h1 mb-3 text-blue">{{ $this->getUnitTypesCount() }}</div>
                    <div class="d-flex mb-2">
                        <div>Số loại khác nhau</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="subheader">Mặc định</div>
                    </div>
                    <div class="h1 mb-3 text-yellow">{{ $this->getDefaultUnits() }}</div>
                    <div class="d-flex mb-2">
                        <div>Đơn vị mặc định</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

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
                                <svg class="animate-spin icon icon-sm me-1" fill="none" viewBox="0 0 24 24">
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
                                    placeholder="Tìm theo mã, tên, ký hiệu...">
                            </div>
                        </div>

                        <!-- Type Filter -->
                        <div class="mb-3">
                            <label class="form-label">Loại đơn vị</label>
                            <select wire:model.live="filters.type" class="form-select" id="select-type-filter">
                                <option value="">Tất cả loại</option>
                                @foreach($unitTypes as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
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

                        <!-- Default Filter -->
                        <div class="mb-3">
                            <label class="form-label">Đơn vị mặc định</label>
                            <div class="form-selectgroup">
                                <label class="form-selectgroup-item">
                                    <input type="radio" name="default_filter" value="" class="form-selectgroup-input"
                                           wire:model.live="filters.is_default" {{ $filters['is_default'] === '' ? 'checked' : '' }} />
                                    <span class="form-selectgroup-label">Tất cả</span>
                                </label>
                                <label class="form-selectgroup-item">
                                    <input type="radio" name="default_filter" value="1" class="form-selectgroup-input"
                                           wire:model.live="filters.is_default" {{ $filters['is_default'] === '1' ? 'checked' : '' }} />
                                    <span class="form-selectgroup-label">Mặc định</span>
                                </label>
                                <label class="form-selectgroup-item">
                                    <input type="radio" name="default_filter" value="0" class="form-selectgroup-input"
                                           wire:model.live="filters.is_default" {{ $filters['is_default'] === '0' ? 'checked' : '' }} />
                                    <span class="form-selectgroup-label">Thường</span>
                                </label>
                            </div>
                        </div>


                    </div>
                </div>
            </div>
        @endif

        <!-- Main Content -->
        <div class="{{ $showFilters ? 'col-lg-9' : 'col-12' }}">

            <!-- Units Table -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        Danh sách đơn vị tính
                    </h3>
                    <div class="card-actions">
                        <div class="d-flex">
                            @if (!$showFilters)
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
                                        class="form-control" placeholder="Tìm kiếm đơn vị tính...">
                                </div>
                            @endif
                            <div class="btn-list">
                                <button wire:click="toggleFilters" class="btn btn-outline-primary">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                        <path d="M5.5 5h13a1 1 0 0 1 .5 1.5L14 12L14 19L10 16L10 12L5.5 6.5a1 1 0 0 1 .5 -1.5" />
                                    </svg>
                                    {{ $showFilters ? 'Ẩn bộ lọc' : 'Hiện bộ lọc' }}
                                </button>
                                <button wire:click="openCreateModal" class="btn btn-primary">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                        <path d="M12 5l0 14" />
                                        <path d="M5 12l14 0" />
                                    </svg>
                                    Thêm đơn vị
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
                                <th>Đơn vị tính</th>
                                <th>Ký hiệu</th>
                                <th>Loại</th>
                                <th>Hệ số chuyển đổi</th>
                                <th>Trạng thái</th>
                                <th class="w-1"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($units as $index => $unit)
                                <tr>
                                    <td>
                                        <span class="text-muted">{{ ($units->currentPage() - 1) * $units->perPage() + $index + 1 }}</span>
                                    </td>
                                    <td>
                                        <div class="d-flex py-1 align-items-center">
                                            <span class="avatar me-2" style="background-color: {{ $unit->type === 'weight' ? '#206bc4' : ($unit->type === 'volume' ? '#ae3ec9' : ($unit->type === 'length' ? '#d63384' : '#fd7e14')) }}">
                                                {{ strtoupper(substr($unit->symbol, 0, 2)) }}
                                            </span>
                                            <div class="flex-fill">
                                                <div class="font-weight-medium">{{ $unit->name }}</div>
                                                <div class="text-muted">
                                                    <small>{{ $unit->code }}</small>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-blue-lt">{{ $unit->symbol }}</span>
                                        @if($unit->is_default)
                                            <span class="badge bg-success-lt ms-1">Mặc định</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary-lt">{{ $unitTypes[$unit->type] ?? $unit->type }}</span>
                                    </td>
                                    <td>
                                        <span class="text-muted">{{ number_format($unit->conversion_factor, 3) }}</span>
                                    </td>
                                    <td>
                                        <label class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox"
                                                   {{ $unit->is_active ? 'checked' : '' }}
                                                   wire:click="toggleStatus({{ $unit->id }})">
                                        </label>
                                    </td>
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-ghost-secondary btn-sm dropdown-toggle"
                                                type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                Thao tác
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-end">
                                                <a wire:click="openEditModal({{ $unit->id }})"
                                                    class="dropdown-item" href="#">
                                                    <svg xmlns="http://www.w3.org/2000/svg"
                                                        class="icon dropdown-item-icon" width="24" height="24"
                                                        viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                                        fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                        <path d="M7 7h-1a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-1" />
                                                        <path d="M20.385 6.585a2.1 2.1 0 0 0 -2.97 -2.97l-8.415 8.385v3h3l8.385 -8.415z" />
                                                        <path d="M16 5l3 3" />
                                                    </svg>
                                                    Chỉnh sửa
                                                </a>
                                                <div class="dropdown-divider"></div>
                                                <a wire:click="openDeleteModal({{ $unit->id }})"
                                                    class="dropdown-item text-danger" href="#">
                                                    <svg xmlns="http://www.w3.org/2000/svg"
                                                        class="icon dropdown-item-icon" width="24" height="24"
                                                        viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                                        fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                        <path d="M4 7l16 0" />
                                                        <path d="M10 11l0 6" />
                                                        <path d="M14 11l0 6" />
                                                        <path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" />
                                                        <path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" />
                                                    </svg>
                                                    Xóa
                                                </a>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7">
                                        <div class="empty">
                                            <div class="empty-img">
                                                <img src="{{ asset('static/illustrations/undraw_void_3ggu.svg') }}" height="128"
                                                    class="empty-img" alt="">
                                            </div>
                                            <p class="empty-title">Chưa có đơn vị tính nào</p>
                                            <p class="empty-subtitle text-muted">
                                                Hãy thêm đơn vị tính đầu tiên để bắt đầu quản lý vật liệu
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
                                                    Thêm đơn vị tính đầu tiên
                                                </button>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($units->hasPages())
                    <div class="card-footer d-flex align-items-center">
                        <p class="m-0 text-muted">
                            Showing <span class="badge bg-secondary fw-normal text-white">{{ $units->firstItem() }}</span>
                            to <span class="badge bg-secondary fw-normal text-white">{{ $units->lastItem() }}</span>
                            of <span class="badge bg-secondary fw-normal text-white">{{ $units->total() }}</span>
                            results
                        </p>
                        @if ($units->hasPages())
                            <ul class="pagination m-0 ms-auto">
                                {{ $units->links('custom.pagination') }}
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
                            {{ $showCreateModal ? 'Thêm đơn vị tính' : 'Sửa đơn vị tính' }}
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeModals"></button>
                    </div>
                    <form wire:submit="save">
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label required">Mã đơn vị</label>
                                        <input type="text" class="form-control @error('form.code') is-invalid @enderror" 
                                               wire:model="form.code" placeholder="VD: KG, M, L...">
                                        @error('form.code')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label required">Tên đơn vị</label>
                                        <input type="text" class="form-control @error('form.name') is-invalid @enderror" 
                                               wire:model="form.name" placeholder="VD: Kilogram, Mét, Lít...">
                                        @error('form.name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label required">Ký hiệu</label>
                                        <input type="text" class="form-control @error('form.symbol') is-invalid @enderror" 
                                               wire:model="form.symbol" placeholder="VD: kg, m, l...">
                                        @error('form.symbol')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label required">Loại đơn vị</label>
                                        <select class="form-select @error('form.type') is-invalid @enderror" wire:model="form.type">
                                            <option value="">Chọn loại đơn vị</option>
                                            @foreach($unitTypes as $key => $label)
                                                <option value="{{ $key }}">{{ $label }}</option>
                                            @endforeach
                                        </select>
                                        @error('form.type')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label required">Hệ số chuyển đổi</label>
                                        <input type="number" step="0.001" class="form-control @error('form.conversion_factor') is-invalid @enderror" 
                                               wire:model="form.conversion_factor" placeholder="1.000">
                                        @error('form.conversion_factor')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="form-hint">Hệ số chuyển đổi so với đơn vị cơ bản</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Tùy chọn</label>
                                        <div>
                                            <label class="form-check">
                                                <input class="form-check-input" type="checkbox" wire:model="form.is_active">
                                                <span class="form-check-label">Hoạt động</span>
                                            </label>
                                            <label class="form-check">
                                                <input class="form-check-input" type="checkbox" wire:model="form.is_default">
                                                <span class="form-check-label">Đặt làm mặc định</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Mô tả</label>
                                <textarea class="form-control @error('form.description') is-invalid @enderror" 
                                          wire:model="form.description" rows="3" placeholder="Mô tả chi tiết về đơn vị tính..."></textarea>
                                @error('form.description')
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
                        <div>Bạn có chắc chắn muốn xóa đơn vị tính <strong>{{ $selectedUnit?->name }}</strong>?</div>
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