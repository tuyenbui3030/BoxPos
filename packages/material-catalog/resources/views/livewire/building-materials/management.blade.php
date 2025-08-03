<div>
    <!-- Overview Section -->
    <div class="row row-deck row-cards mb-3">
        <div class="col-sm-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="subheader">Tổng vật liệu</div>
                    </div>
                    <div class="h1 mb-3">{{ $this->getTotalMaterials() }}</div>
                    <div class="d-flex mb-2">
                        <div>Vật liệu trong hệ thống</div>
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
                    <div class="h1 mb-3 text-green">{{ $this->getActiveMaterials() }}</div>
                    <div class="d-flex mb-2">
                        <div>Vật liệu đang sử dụng</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="subheader">Nổi bật</div>
                    </div>
                    <div class="h1 mb-3 text-blue">{{ $this->getFeaturedMaterials() }}</div>
                    <div class="d-flex mb-2">
                        <div>Vật liệu nổi bật</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="subheader">Nguy hiểm</div>
                    </div>
                    <div class="h1 mb-3 text-red">{{ $this->getHazardousMaterials() }}</div>
                    <div class="d-flex mb-2">
                        <div>Vật liệu nguy hiểm</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Layout -->
    <div class="row">
        <!-- Filter Sidebar -->
        @if ($showFilters)
            <div class="col-lg-3">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Bộ lọc</h3>
                    <div class="card-actions">
                        <button wire:click="clearFilters" class="btn btn-outline-primary btn-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-sm" width="24"
                                height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                fill="none" stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                <path d="M20 11a8.1 8.1 0 0 0 -15.5 -2m-.5 -4v4h4" />
                                <path d="M4 13a8.1 8.1 0 0 0 15.5 2m.5 4v-4h-4" />
                            </svg>
                            Xóa bộ lọc
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
                                    placeholder="Tìm theo mã, tên, thương hiệu...">
                            </div>
                        </div>

                        <!-- Category Filter -->
                        <div class="mb-3">
                            <label class="form-label">Danh mục</label>
                            <select wire:model.live="filters.category_id" class="form-select">
                                <option value="">Tất cả danh mục</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">
                                        {{ str_repeat('— ', $category->level) }}{{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Brand Filter -->
                        <div class="mb-3">
                            <label class="form-label">Thương hiệu</label>
                            <select wire:model.live="filters.brand" class="form-select">
                                <option value="">Tất cả thương hiệu</option>
                                @foreach($brands as $brand)
                                    <option value="{{ $brand }}">{{ $brand }}</option>
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

                        <!-- Featured Filter -->
                        <div class="mb-3">
                            <label class="form-label">Nổi bật</label>
                            <div class="form-selectgroup">
                                <label class="form-selectgroup-item">
                                    <input type="radio" name="featured_filter" value="" class="form-selectgroup-input"
                                           wire:model.live="filters.is_featured" {{ $filters['is_featured'] === '' ? 'checked' : '' }} />
                                    <span class="form-selectgroup-label">Tất cả</span>
                                </label>
                                <label class="form-selectgroup-item">
                                    <input type="radio" name="featured_filter" value="1" class="form-selectgroup-input"
                                           wire:model.live="filters.is_featured" {{ $filters['is_featured'] === '1' ? 'checked' : '' }} />
                                    <span class="form-selectgroup-label">Nổi bật</span>
                                </label>
                                <label class="form-selectgroup-item">
                                    <input type="radio" name="featured_filter" value="0" class="form-selectgroup-input"
                                           wire:model.live="filters.is_featured" {{ $filters['is_featured'] === '0' ? 'checked' : '' }} />
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

            {{-- Materials Table Card --}}
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        Danh sách vật liệu
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
                                    class="form-control" placeholder="Tìm kiếm vật liệu...">
                            </div>
                            <div class="btn-list">
                                <button wire:click="openCreateModal" class="btn btn-primary">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                        <path d="M12 5l0 14" />
                                        <path d="M5 12l14 0" />
                                    </svg>
                                    Thêm vật liệu
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
                                <th>Vật liệu</th>
                                <th>Mã</th>
                                <th>Danh mục</th>
                                <th>Thương hiệu</th>
                                <th>Đơn vị</th>
                                <th>Trạng thái</th>
                                <th class="w-1">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($materials as $index => $material)
                                <tr>
                                    <td>
                                        <span class="text-muted">{{ ($materials->currentPage() - 1) * $materials->perPage() + $index + 1 }}</span>
                                    </td>
                                    <td>
                                        <div class="d-flex py-1 align-items-center">
                                            <span class="avatar me-2" style="background-image: url({{ $material->primary_image_url }})"></span>
                                            <div class="flex-fill">
                                                <div class="font-weight-medium">
                                                    {{ $material->name }}
                                                    @if($material->is_featured)
                                                        <span class="badge bg-yellow-lt ms-1">Nổi bật</span>
                                                    @endif
                                                    @if($material->is_hazardous)
                                                        <span class="badge bg-red-lt ms-1">Nguy hiểm</span>
                                                    @endif
                                                </div>
                                                @if($material->short_description)
                                                    <div class="text-muted">{{ Str::limit($material->short_description, 60) }}</div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="text-muted">{{ $material->material_code }}</span>
                                    </td>
                                    <td>
                                        @if($material->category)
                                            <span class="badge bg-blue-lt">{{ $material->category->name }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($material->brand)
                                            <span class="badge bg-secondary-lt">{{ $material->brand }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($material->primaryUnit)
                                            <span class="badge bg-green-lt">{{ $material->primaryUnit->symbol }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <label class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" 
                                                   {{ $material->is_active ? 'checked' : '' }}
                                                   wire:click="toggleStatus({{ $material->id }})">
                                        </label>
                                    </td>
                                    <td>
                                        <div class="btn-list flex-nowrap">
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                    Thao tác
                                                </button>
                                                <div class="dropdown-menu">
                                                    <a class="dropdown-item" href="#" wire:click.prevent="openDetailModal({{ $material->id }})">
                                                        <i class="icon ti ti-eye me-2"></i>
                                                        Xem chi tiết
                                                    </a>
                                                    <a class="dropdown-item" href="#" wire:click.prevent="openEditModal({{ $material->id }})">
                                                        <i class="icon ti ti-edit me-2"></i>
                                                        Sửa
                                                    </a>
                                                    <div class="dropdown-divider"></div>
                                                    <a class="dropdown-item text-danger" href="#" wire:click.prevent="openDeleteModal({{ $material->id }})">
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
                                            <p class="empty-title">Chưa có vật liệu nào</p>
                                            <p class="empty-subtitle text-muted">
                                                Hãy thêm vật liệu đầu tiên để bắt đầu quản lý kho
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
                                                    Thêm vật liệu đầu tiên
                                                </button>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($materials->hasPages())
                    <div class="card-footer d-flex align-items-center">
                        <p class="m-0 text-muted">
                            Showing <span class="badge bg-secondary fw-normal text-white">{{ $materials->firstItem() }}</span>
                            to <span class="badge bg-secondary fw-normal text-white">{{ $materials->lastItem() }}</span>
                            of <span class="badge bg-secondary fw-normal text-white">{{ $materials->total() }}</span>
                            results
                        </p>
                        @if ($materials->hasPages())
                            <ul class="pagination m-0 ms-auto">
                                {{ $materials->links('custom.pagination') }}
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
            <div class="modal-dialog modal-xl modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            {{ $showCreateModal ? 'Thêm vật liệu' : 'Sửa vật liệu' }}
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeModals"></button>
                    </div>
                    <form wire:submit="save">
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label required">Danh mục</label>
                                        <select class="form-select @error('form.category_id') is-invalid @enderror" wire:model="form.category_id">
                                            <option value="">Chọn danh mục</option>
                                            @foreach($categories as $category)
                                                <option value="{{ $category->id }}">
                                                    {{ str_repeat('— ', $category->level) }}{{ $category->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('form.category_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label required">Đơn vị tính chính</label>
                                        <select class="form-select @error('form.primary_unit_id') is-invalid @enderror" wire:model="form.primary_unit_id">
                                            <option value="">Chọn đơn vị tính</option>
                                            @foreach($units as $unit)
                                                <option value="{{ $unit->id }}">{{ $unit->name }} ({{ $unit->symbol }})</option>
                                            @endforeach
                                        </select>
                                        @error('form.primary_unit_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label required">Mã vật liệu</label>
                                        <input type="text" class="form-control @error('form.material_code') is-invalid @enderror" 
                                               wire:model="form.material_code" placeholder="Mã tự động">
                                        @error('form.material_code')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label required">Tên vật liệu</label>
                                        <input type="text" class="form-control @error('form.name') is-invalid @enderror" 
                                               wire:model="form.name" placeholder="VD: Xi măng Portland PCB40...">
                                        @error('form.name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Thương hiệu</label>
                                        <input type="text" class="form-control @error('form.brand') is-invalid @enderror" 
                                               wire:model="form.brand" placeholder="VD: Hà Tiên, Hòa Phát...">
                                        @error('form.brand')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Model</label>
                                        <input type="text" class="form-control @error('form.model') is-invalid @enderror" 
                                               wire:model="form.model" placeholder="VD: PCB40, CB300-V...">
                                        @error('form.model')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Xuất xứ</label>
                                        <input type="text" class="form-control @error('form.origin_country') is-invalid @enderror" 
                                               wire:model="form.origin_country" placeholder="VD: Vietnam, China...">
                                        @error('form.origin_country')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Mô tả ngắn</label>
                                <input type="text" class="form-control @error('form.short_description') is-invalid @enderror" 
                                       wire:model="form.short_description" placeholder="Mô tả ngắn gọn về vật liệu...">
                                @error('form.short_description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Mô tả chi tiết</label>
                                <textarea class="form-control @error('form.description') is-invalid @enderror" 
                                          wire:model="form.description" rows="4" placeholder="Mô tả chi tiết về vật liệu..."></textarea>
                                @error('form.description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Khối lượng/đơn vị (kg)</label>
                                        <input type="number" step="0.001" class="form-control @error('form.weight_per_unit') is-invalid @enderror" 
                                               wire:model="form.weight_per_unit" placeholder="0.000">
                                        @error('form.weight_per_unit')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Hạn sử dụng (ngày)</label>
                                        <input type="number" class="form-control @error('form.shelf_life_days') is-invalid @enderror" 
                                               wire:model="form.shelf_life_days" placeholder="365">
                                        @error('form.shelf_life_days')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Hình ảnh</label>
                                <input type="file" class="form-control @error('images.*') is-invalid @enderror" 
                                       wire:model="images" multiple accept="image/*">
                                @error('images.*')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-hint">Chọn nhiều hình ảnh (tối đa 2MB/ảnh). Hình ảnh sẽ được upload lên Cloudflare R2.</small>
                                
                                {{-- Preview uploaded images --}}
                                @if($images)
                                    <div class="row mt-3">
                                        @foreach($images as $index => $image)
                                            <div class="col-md-3 mb-2">
                                                <div class="card">
                                                    <img src="{{ $image->temporaryUrl() }}" class="card-img-top" style="height: 150px; object-fit: cover;" alt="Preview">
                                                    <div class="card-body p-2">
                                                        <small class="text-muted">{{ $image->getClientOriginalName() }}</small>
                                                        <button type="button" class="btn btn-sm btn-outline-danger float-end" 
                                                                wire:click="removeImage({{ $index }})">
                                                            <i class="ti ti-x"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif

                                {{-- Show existing images when editing --}}
                                @if($showEditModal && $selectedMaterial && $selectedMaterial->images)
                                    <div class="mt-3">
                                        <label class="form-label">Hình ảnh hiện tại</label>
                                        <div class="row">
                                            @foreach($selectedMaterial->image_urls as $index => $imageUrl)
                                                <div class="col-md-3 mb-2">
                                                    <div class="card">
                                                        <img src="{{ $imageUrl }}" class="card-img-top" style="height: 150px; object-fit: cover;" alt="Current image">
                                                        <div class="card-body p-2">
                                                            <small class="text-muted">Hình {{ $index + 1 }}</small>
                                                            <button type="button" class="btn btn-sm btn-outline-danger float-end" 
                                                                    wire:click="removeExistingImage({{ $index }})">
                                                                <i class="ti ti-x"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <label class="form-label">Tùy chọn</label>
                                        <div class="row">
                                            <div class="col-md-3">
                                                <label class="form-check">
                                                    <input class="form-check-input" type="checkbox" wire:model="form.is_active">
                                                    <span class="form-check-label">Hoạt động</span>
                                                </label>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-check">
                                                    <input class="form-check-input" type="checkbox" wire:model="form.is_featured">
                                                    <span class="form-check-label">Nổi bật</span>
                                                </label>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-check">
                                                    <input class="form-check-input" type="checkbox" wire:model="form.is_hazardous">
                                                    <span class="form-check-label">Nguy hiểm</span>
                                                </label>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-check">
                                                    <input class="form-check-input" type="checkbox" wire:model="form.requires_quality_check">
                                                    <span class="form-check-label">Kiểm tra chất lượng</span>
                                                </label>
                                            </div>
                                        </div>
                                        <div class="row mt-2">
                                            <div class="col-md-6">
                                                <label class="form-check">
                                                    <input class="form-check-input" type="checkbox" wire:model="form.track_serial_numbers">
                                                    <span class="form-check-label">Theo dõi số serial</span>
                                                </label>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-check">
                                                    <input class="form-check-input" type="checkbox" wire:model="form.track_batch_numbers">
                                                    <span class="form-check-label">Theo dõi số lô</span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Ghi chú nội bộ</label>
                                <textarea class="form-control @error('form.internal_notes') is-invalid @enderror" 
                                          wire:model="form.internal_notes" rows="2" placeholder="Ghi chú nội bộ..."></textarea>
                                @error('form.internal_notes')
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

    {{-- Detail Modal --}}
    @if($showDetailModal && $selectedMaterial)
        @include('material-catalog::livewire.building-materials.detail-modal')
    @endif

    {{-- Delete Modal --}}
    @if($showDeleteModal)
        <div class="modal modal-blur fade show" style="display: block;" tabindex="-1">
            <div class="modal-dialog modal-sm modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-body">
                        <div class="modal-title">Xác nhận xóa?</div>
                        <div>Bạn có chắc chắn muốn xóa vật liệu <strong>{{ $selectedMaterial?->name }}</strong>?</div>
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
