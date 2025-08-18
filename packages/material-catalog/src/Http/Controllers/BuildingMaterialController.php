<?php

namespace Packages\MaterialCatalog\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Packages\MaterialCatalog\Models\BuildingMaterial;
use Packages\MaterialCatalog\Services\BuildingMaterialService;

class BuildingMaterialController
{
    public function __construct(
        private BuildingMaterialService $materialService
    ) {}

    /**
     * Display a listing of materials (API endpoint)
     */
    public function index(Request $request): JsonResponse
    {
        $materials = $this->materialService->getAll($request->all());
        
        return response()->json([
            'success' => true,
            'data' => $materials
        ]);
    }

    /**
     * Display the specified material (API endpoint)
     */
    public function show(BuildingMaterial $material): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $material->load(['category', 'primaryUnit', 'specifications'])
        ]);
    }

    /**
     * Remove the specified material (API endpoint)
     */
    public function destroy(BuildingMaterial $material): JsonResponse
    {
        try {
            $this->materialService->delete($material);
            
            return response()->json([
                'success' => true,
                'message' => 'Vật liệu đã được xóa thành công'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle material status (API endpoint)
     */
    public function toggleStatus(BuildingMaterial $material): JsonResponse
    {
        try {
            $material->update(['is_active' => !$material->is_active]);
            
            return response()->json([
                'success' => true,
                'message' => 'Trạng thái đã được cập nhật',
                'data' => ['is_active' => $material->is_active]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk operations (API endpoint)
     */
    public function bulkAction(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'action' => 'required|in:delete,activate,deactivate,feature,unfeature',
            'ids' => 'required|array',
            'ids.*' => 'exists:building_materials,id'
        ]);

        try {
            $count = $this->materialService->bulkAction($validated['action'], $validated['ids']);
            
            return response()->json([
                'success' => true,
                'message' => "Đã thực hiện thao tác trên {$count} vật liệu"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }
}