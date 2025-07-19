<?php

namespace Packages\MaterialCatalog\Database\Seeders;

use Illuminate\Database\Seeder;
use Packages\MaterialCatalog\Models\MaterialSpecification;
use Packages\MaterialCatalog\Models\BuildingMaterial;

class MaterialSpecificationsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $materials = BuildingMaterial::all();

        foreach ($materials as $material) {
            $this->createSpecificationsForMaterial($material);
        }
    }

    /**
     * Create specifications for a specific material.
     */
    private function createSpecificationsForMaterial(BuildingMaterial $material): void
    {
        $specifications = [];

        // Specifications based on material type
        if (str_contains(strtolower($material->name), 'xi măng')) {
            $specifications = [
                [
                    'spec_name' => 'Cường độ nén',
                    'spec_value' => $material->model === 'PCB50' ? '50' : '40',
                    'spec_unit' => 'MPa',
                    'spec_type' => 'number',
                    'spec_category' => 'Cơ học',
                    'description' => 'Cường độ nén tối thiểu sau 28 ngày',
                    'sort_order' => 1,
                    'is_required' => true,
                    'show_in_listing' => true,
                ],
                [
                    'spec_name' => 'Thời gian đông kết đầu',
                    'spec_value' => '45',
                    'spec_unit' => 'phút',
                    'spec_type' => 'number',
                    'spec_category' => 'Thời gian',
                    'description' => 'Thời gian bắt đầu đông kết',
                    'sort_order' => 2,
                    'is_required' => true,
                ],
                [
                    'spec_name' => 'Thời gian đông kết cuối',
                    'spec_value' => '375',
                    'spec_unit' => 'phút',
                    'spec_type' => 'number',
                    'spec_category' => 'Thời gian',
                    'description' => 'Thời gian hoàn thành đông kết',
                    'sort_order' => 3,
                    'is_required' => true,
                ],
                [
                    'spec_name' => 'Độ mịn',
                    'spec_value' => $material->model === 'PCB50' ? '3000' : '2800',
                    'spec_unit' => 'cm²/g',
                    'spec_type' => 'number',
                    'spec_category' => 'Vật lý',
                    'description' => 'Độ mịn Blaine',
                    'sort_order' => 4,
                    'is_required' => false,
                    'show_in_listing' => true,
                ],
            ];
        } elseif (str_contains(strtolower($material->name), 'thép')) {
            $specifications = [
                [
                    'spec_name' => 'Đường kính',
                    'spec_value' => str_contains($material->name, 'D12') ? '12' : '10',
                    'spec_unit' => 'mm',
                    'spec_type' => 'number',
                    'spec_category' => 'Kích thước',
                    'description' => 'Đường kính danh nghĩa',
                    'sort_order' => 1,
                    'is_required' => true,
                    'show_in_listing' => true,
                ],
                [
                    'spec_name' => 'Giới hạn chảy',
                    'spec_value' => str_contains($material->model, 'CB300') ? '300' : '240',
                    'spec_unit' => 'MPa',
                    'spec_type' => 'number',
                    'spec_category' => 'Cơ học',
                    'description' => 'Giới hạn chảy tối thiểu',
                    'sort_order' => 2,
                    'is_required' => true,
                    'show_in_listing' => true,
                ],
                [
                    'spec_name' => 'Cường độ kéo',
                    'spec_value' => str_contains($material->model, 'CB300') ? '420-550' : '350-500',
                    'spec_unit' => 'MPa',
                    'spec_type' => 'text',
                    'spec_category' => 'Cơ học',
                    'description' => 'Cường độ kéo đứt',
                    'sort_order' => 3,
                    'is_required' => true,
                ],
                [
                    'spec_name' => 'Độ dãn dài',
                    'spec_value' => '≥ 14',
                    'spec_unit' => '%',
                    'spec_type' => 'text',
                    'spec_category' => 'Cơ học',
                    'description' => 'Độ dãn dài tối thiểu',
                    'sort_order' => 4,
                    'is_required' => false,
                ],
            ];
        } elseif (str_contains(strtolower($material->name), 'gạch')) {
            $specifications = [
                [
                    'spec_name' => 'Kích thước',
                    'spec_value' => '220x105x60',
                    'spec_unit' => 'mm',
                    'spec_type' => 'text',
                    'spec_category' => 'Kích thước',
                    'description' => 'Kích thước danh nghĩa',
                    'sort_order' => 1,
                    'is_required' => true,
                    'show_in_listing' => true,
                ],
                [
                    'spec_name' => 'Cường độ nén',
                    'spec_value' => '7.5',
                    'spec_unit' => 'MPa',
                    'spec_type' => 'number',
                    'spec_category' => 'Cơ học',
                    'description' => 'Cường độ nén tối thiểu',
                    'sort_order' => 2,
                    'is_required' => true,
                    'show_in_listing' => true,
                ],
                [
                    'spec_name' => 'Độ hấp thụ nước',
                    'spec_value' => '≤ 22',
                    'spec_unit' => '%',
                    'spec_type' => 'text',
                    'spec_category' => 'Vật lý',
                    'description' => 'Độ hấp thụ nước tối đa',
                    'sort_order' => 3,
                    'is_required' => true,
                ],
            ];
        } elseif (str_contains(strtolower($material->name), 'cát')) {
            $specifications = [
                [
                    'spec_name' => 'Mô đun độ mịn',
                    'spec_value' => '2.3-3.1',
                    'spec_unit' => '',
                    'spec_type' => 'text',
                    'spec_category' => 'Vật lý',
                    'description' => 'Mô đun độ mịn theo TCVN',
                    'sort_order' => 1,
                    'is_required' => true,
                    'show_in_listing' => true,
                ],
                [
                    'spec_name' => 'Hàm lượng sét',
                    'spec_value' => '≤ 3',
                    'spec_unit' => '%',
                    'spec_type' => 'text',
                    'spec_category' => 'Hóa học',
                    'description' => 'Hàm lượng sét tối đa',
                    'sort_order' => 2,
                    'is_required' => true,
                ],
                [
                    'spec_name' => 'Tạp chất hữu cơ',
                    'spec_value' => 'Đạt yêu cầu',
                    'spec_unit' => '',
                    'spec_type' => 'text',
                    'spec_category' => 'Hóa học',
                    'description' => 'Kiểm tra tạp chất hữu cơ',
                    'sort_order' => 3,
                    'is_required' => true,
                ],
            ];
        } elseif (str_contains(strtolower($material->name), 'sơn')) {
            $specifications = [
                [
                    'spec_name' => 'Độ che phủ',
                    'spec_value' => '12-14',
                    'spec_unit' => 'm²/lít',
                    'spec_type' => 'text',
                    'spec_category' => 'Ứng dụng',
                    'description' => 'Diện tích che phủ trên 1 lít sơn',
                    'sort_order' => 1,
                    'is_required' => true,
                    'show_in_listing' => true,
                ],
                [
                    'spec_name' => 'Thời gian khô bề mặt',
                    'spec_value' => '2-4',
                    'spec_unit' => 'giờ',
                    'spec_type' => 'text',
                    'spec_category' => 'Thời gian',
                    'description' => 'Thời gian khô bề mặt',
                    'sort_order' => 2,
                    'is_required' => true,
                ],
                [
                    'spec_name' => 'Thời gian sơn lớp tiếp theo',
                    'spec_value' => '4-6',
                    'spec_unit' => 'giờ',
                    'spec_type' => 'text',
                    'spec_category' => 'Thời gian',
                    'description' => 'Thời gian có thể sơn lớp tiếp theo',
                    'sort_order' => 3,
                    'is_required' => false,
                ],
                [
                    'spec_name' => 'VOC',
                    'spec_value' => '< 50',
                    'spec_unit' => 'g/l',
                    'spec_type' => 'text',
                    'spec_category' => 'Môi trường',
                    'description' => 'Hàm lượng hợp chất hữu cơ bay hơi',
                    'sort_order' => 4,
                    'is_required' => false,
                ],
            ];
        }

        // Create specifications
        foreach ($specifications as $specData) {
            MaterialSpecification::create(array_merge($specData, [
                'material_id' => $material->id,
            ]));
        }
    }
}
