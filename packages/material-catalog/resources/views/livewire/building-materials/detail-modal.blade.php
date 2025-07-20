<div class="modal modal-blur fade show" style="display: block;" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Chi tiết vật liệu</h5>
                <button type="button" class="btn-close" wire:click="closeModals"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-8">
                        {{-- Basic Information --}}
                        <div class="card mb-3">
                            <div class="card-header">
                                <h3 class="card-title">Thông tin cơ bản</h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Mã vật liệu</label>
                                            <div class="form-control-plaintext">{{ $selectedMaterial->material_code }}</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Tên vật liệu</label>
                                            <div class="form-control-plaintext">{{ $selectedMaterial->name }}</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Danh mục</label>
                                            <div class="form-control-plaintext">
                                                @if($selectedMaterial->category)
                                                    <span class="badge bg-blue-lt">{{ $selectedMaterial->category->name }}</span>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Đơn vị tính</label>
                                            <div class="form-control-plaintext">
                                                @if($selectedMaterial->primaryUnit)
                                                    <span class="badge bg-green-lt">{{ $selectedMaterial->primaryUnit->name }} ({{ $selectedMaterial->primaryUnit->symbol }})</span>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label">Thương hiệu</label>
                                            <div class="form-control-plaintext">{{ $selectedMaterial->brand ?: '—' }}</div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label">Model</label>
                                            <div class="form-control-plaintext">{{ $selectedMaterial->model ?: '—' }}</div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label">Xuất xứ</label>
                                            <div class="form-control-plaintext">{{ $selectedMaterial->origin_country ?: '—' }}</div>
                                        </div>
                                    </div>
                                </div>
                                @if($selectedMaterial->description)
                                    <div class="mb-3">
                                        <label class="form-label">Mô tả</label>
                                        <div class="form-control-plaintext">{{ $selectedMaterial->description }}</div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Technical Specifications --}}
                        @if($selectedMaterial->specifications && $selectedMaterial->specifications->count() > 0)
                            <div class="card mb-3">
                                <div class="card-header">
                                    <h3 class="card-title">Thông số kỹ thuật</h3>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Thông số</th>
                                                    <th>Giá trị</th>
                                                    <th>Đơn vị</th>
                                                    <th>Loại</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($selectedMaterial->specifications as $spec)
                                                    <tr>
                                                        <td>
                                                            <strong>{{ $spec->spec_name }}</strong>
                                                            @if($spec->description)
                                                                <br><small class="text-muted">{{ $spec->description }}</small>
                                                            @endif
                                                        </td>
                                                        <td>{{ $spec->spec_value }}</td>
                                                        <td>{{ $spec->spec_unit ?: '—' }}</td>
                                                        <td>
                                                            <span class="badge bg-secondary-lt">{{ $spec->spec_category }}</span>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- Additional Information --}}
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Thông tin bổ sung</h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    @if($selectedMaterial->weight_per_unit)
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Khối lượng/đơn vị</label>
                                                <div class="form-control-plaintext">{{ number_format($selectedMaterial->weight_per_unit, 3) }} kg</div>
                                            </div>
                                        </div>
                                    @endif
                                    @if($selectedMaterial->shelf_life_days)
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Hạn sử dụng</label>
                                                <div class="form-control-plaintext">{{ $selectedMaterial->shelf_life_days }} ngày</div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <label class="form-label">Đặc tính</label>
                                            <div class="form-control-plaintext">
                                                @if($selectedMaterial->is_hazardous)
                                                    <span class="badge bg-red-lt me-1">Nguy hiểm</span>
                                                @endif
                                                @if($selectedMaterial->requires_quality_check)
                                                    <span class="badge bg-orange-lt me-1">Kiểm tra chất lượng</span>
                                                @endif
                                                @if($selectedMaterial->track_serial_numbers)
                                                    <span class="badge bg-purple-lt me-1">Theo dõi số serial</span>
                                                @endif
                                                @if($selectedMaterial->track_batch_numbers)
                                                    <span class="badge bg-cyan-lt me-1">Theo dõi số lô</span>
                                                @endif
                                                @if(!$selectedMaterial->is_hazardous && !$selectedMaterial->requires_quality_check && !$selectedMaterial->track_serial_numbers && !$selectedMaterial->track_batch_numbers)
                                                    <span class="text-muted">Không có đặc tính đặc biệt</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @if($selectedMaterial->internal_notes)
                                    <div class="mb-3">
                                        <label class="form-label">Ghi chú nội bộ</label>
                                        <div class="form-control-plaintext">{{ $selectedMaterial->internal_notes }}</div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        {{-- Status & Actions --}}
                        <div class="card mb-3">
                            <div class="card-header">
                                <h3 class="card-title">Trạng thái</h3>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label">Hoạt động</label>
                                    <div class="form-control-plaintext">
                                        @if($selectedMaterial->is_active)
                                            <span class="badge bg-success">Hoạt động</span>
                                        @else
                                            <span class="badge bg-secondary">Không hoạt động</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Nổi bật</label>
                                    <div class="form-control-plaintext">
                                        @if($selectedMaterial->is_featured)
                                            <span class="badge bg-yellow">Nổi bật</span>
                                        @else
                                            <span class="text-muted">Không</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Ngày tạo</label>
                                    <div class="form-control-plaintext">{{ $selectedMaterial->created_at->format('d/m/Y H:i') }}</div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Cập nhật cuối</label>
                                    <div class="form-control-plaintext">{{ $selectedMaterial->updated_at->format('d/m/Y H:i') }}</div>
                                </div>
                            </div>
                        </div>

                        {{-- Images --}}
                        @if($selectedMaterial->images && count($selectedMaterial->images) > 0)
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Hình ảnh</h3>
                                </div>
                                <div class="card-body">
                                    <div class="row g-2">
                                        @foreach($selectedMaterial->images as $image)
                                            <div class="col-6">
                                                <img src="{{ asset('storage/' . $image) }}" class="img-fluid rounded" alt="Material Image">
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" wire:click="closeModals">Đóng</button>
                <button type="button" class="btn btn-primary" wire:click="openEditModal({{ $selectedMaterial->id }})">
                    Chỉnh sửa
                </button>
            </div>
        </div>
    </div>
</div>
<div class="modal-backdrop fade show"></div>
