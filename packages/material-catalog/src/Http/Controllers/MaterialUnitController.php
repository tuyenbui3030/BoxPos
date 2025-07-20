<?php

namespace Packages\MaterialCatalog\Http\Controllers;

use App\Http\Controllers\Controller;
use Packages\MaterialCatalog\Models\MaterialUnit;
use Packages\MaterialCatalog\Http\Requests\MaterialUnitRequest;
use Packages\MaterialCatalog\Services\MaterialUnitService;
use Packages\Log\Traits\Loggable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MaterialUnitController extends Controller
{
    use Loggable;

    protected MaterialUnitService $materialUnitService;

    public function __construct(MaterialUnitService $materialUnitService)
    {
        $this->materialUnitService = $materialUnitService;
    }

    /**
     * Display a listing of material units.
     */
    public function index(Request $request): JsonResponse
    {
        $this->logActivity('material_units_api_index_accessed', [
            'user_id' => auth()->id(),
            'store_id' => auth()->user()->current_store_id,
            'filters' => $request->only(['search', 'type', 'is_active']),
        ]);

        try {
            $query = MaterialUnit::query();

            // Apply filters
            if ($request->filled('search')) {
                $query->search($request->search);
            }

            if ($request->filled('type')) {
                $query->byType($request->type);
            }

            if ($request->has('is_active')) {
                $request->is_active ? $query->active() : $query->inactive();
            }

            // Apply sorting
            $sortBy = $request->get('sort_by', 'name');
            $sortDirection = $request->get('sort_direction', 'asc');
            
            if (in_array($sortBy, ['name', 'code', 'type', 'created_at'])) {
                $query->orderBy($sortBy, $sortDirection);
            } else {
                $query->orderByTypeAndName();
            }

            // Paginate or get all
            if ($request->boolean('paginate', true)) {
                $perPage = min($request->get('per_page', 15), 100);
                $units = $query->paginate($perPage);
            } else {
                $units = $query->get();
            }

            return response()->json([
                'success' => true,
                'data' => $units,
                'message' => 'Danh sách đơn vị tính được tải thành công.',
            ]);

        } catch (\Exception $e) {
            $this->logError($e, [
                'action' => 'material_units_api_index',
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi tải danh sách đơn vị tính.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Store a newly created material unit.
     */
    public function store(MaterialUnitRequest $request): JsonResponse
    {
        $this->logActivity('material_unit_api_store_started', [
            'user_id' => auth()->id(),
            'store_id' => auth()->user()->current_store_id,
        ]);

        try {
            $unit = $this->materialUnitService->createMaterialUnit($request->validated());

            $this->logActivity('material_unit_api_created', [
                'unit_id' => $unit->id,
                'unit_code' => $unit->code,
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'data' => $unit->load(['creator']),
                'message' => 'Đơn vị tính đã được tạo thành công.',
            ], 201);

        } catch (\Exception $e) {
            $this->logError($e, [
                'action' => 'material_unit_api_store',
                'data' => $request->validated(),
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi tạo đơn vị tính: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Display the specified material unit.
     */
    public function show(MaterialUnit $unit): JsonResponse
    {
        $this->logActivity('material_unit_api_show', [
            'unit_id' => $unit->id,
            'user_id' => auth()->id(),
        ]);

        try {
            return response()->json([
                'success' => true,
                'data' => $unit->load(['creator']),
                'message' => 'Chi tiết đơn vị tính được tải thành công.',
            ]);

        } catch (\Exception $e) {
            $this->logError($e, [
                'action' => 'material_unit_api_show',
                'unit_id' => $unit->id,
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi tải chi tiết đơn vị tính.',
            ], 500);
        }
    }

    /**
     * Update the specified material unit.
     */
    public function update(MaterialUnitRequest $request, MaterialUnit $unit): JsonResponse
    {
        $this->logActivity('material_unit_api_update_started', [
            'unit_id' => $unit->id,
            'user_id' => auth()->id(),
        ]);

        try {
            $unit = $this->materialUnitService->updateMaterialUnit($unit, $request->validated());

            $this->logActivity('material_unit_api_updated', [
                'unit_id' => $unit->id,
                'unit_code' => $unit->code,
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'data' => $unit->load(['creator']),
                'message' => 'Đơn vị tính đã được cập nhật thành công.',
            ]);

        } catch (\Exception $e) {
            $this->logError($e, [
                'action' => 'material_unit_api_update',
                'unit_id' => $unit->id,
                'data' => $request->validated(),
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi cập nhật đơn vị tính: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Remove the specified material unit.
     */
    public function destroy(MaterialUnit $unit): JsonResponse
    {
        $this->logActivity('material_unit_api_destroy_started', [
            'unit_id' => $unit->id,
            'user_id' => auth()->id(),
        ]);

        try {
            $this->materialUnitService->deleteMaterialUnit($unit);

            $this->logActivity('material_unit_api_deleted', [
                'unit_id' => $unit->id,
                'unit_code' => $unit->code,
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Đơn vị tính đã được xóa thành công.',
            ]);

        } catch (\Exception $e) {
            $this->logError($e, [
                'action' => 'material_unit_api_destroy',
                'unit_id' => $unit->id,
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi xóa đơn vị tính: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Toggle unit status.
     */
    public function toggleStatus(MaterialUnit $unit): JsonResponse
    {
        $this->logActivity('material_unit_api_toggle_status_started', [
            'unit_id' => $unit->id,
            'current_status' => $unit->is_active,
            'user_id' => auth()->id(),
        ]);

        try {
            $unit = $this->materialUnitService->toggleStatus($unit);

            $this->logActivity('material_unit_api_status_toggled', [
                'unit_id' => $unit->id,
                'new_status' => $unit->is_active,
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'data' => $unit,
                'message' => 'Trạng thái đơn vị tính đã được cập nhật.',
            ]);

        } catch (\Exception $e) {
            $this->logError($e, [
                'action' => 'material_unit_api_toggle_status',
                'unit_id' => $unit->id,
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi cập nhật trạng thái: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get units for dropdown.
     */
    public function dropdown(Request $request): JsonResponse
    {
        try {
            $query = MaterialUnit::active();

            if ($request->filled('type')) {
                $query->byType($request->type);
            }

            $units = $query->forDropdown()->get();

            return response()->json([
                'success' => true,
                'data' => $units,
                'message' => 'Danh sách đơn vị tính cho dropdown được tải thành công.',
            ]);

        } catch (\Exception $e) {
            $this->logError($e, [
                'action' => 'material_unit_api_dropdown',
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi tải danh sách đơn vị tính.',
            ], 500);
        }
    }
}
