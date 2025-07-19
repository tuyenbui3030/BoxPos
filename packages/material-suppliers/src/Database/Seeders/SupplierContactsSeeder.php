<?php

namespace Packages\MaterialSuppliers\Database\Seeders;

use Illuminate\Database\Seeder;
use Packages\MaterialSuppliers\Models\MaterialSupplier;
use Packages\MaterialSuppliers\Models\SupplierContact;

class SupplierContactsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $suppliers = MaterialSupplier::all();

        foreach ($suppliers as $supplier) {
            $this->createContactsForSupplier($supplier);
        }
    }

    /**
     * Create contacts for a specific supplier.
     */
    private function createContactsForSupplier(MaterialSupplier $supplier): void
    {
        $contacts = [];

        // Generate contacts based on supplier
        if (str_contains($supplier->company_name, 'Hà Tiên')) {
            $contacts = [
                [
                    'name' => 'Nguyễn Văn A',
                    'position' => 'Giám đốc Kinh doanh',
                    'department' => 'Kinh doanh',
                    'phone' => '0901234567',
                    'mobile' => '0901234567',
                    'email' => 'sales@hatiencement.com',
                    'is_primary' => true,
                    'responsibilities' => ['Bán hàng', 'Hỗ trợ kỹ thuật'],
                ],
                [
                    'name' => 'Trần Thị B',
                    'position' => 'Chuyên viên Kỹ thuật',
                    'department' => 'Kỹ thuật',
                    'phone' => '0901234568',
                    'mobile' => '0901234568',
                    'email' => 'technical@hatiencement.com',
                    'is_primary' => false,
                    'responsibilities' => ['Hỗ trợ kỹ thuật', 'Kiểm tra chất lượng'],
                ],
            ];
        } elseif (str_contains($supplier->company_name, 'Hòa Phát')) {
            $contacts = [
                [
                    'name' => 'Trần Thị B',
                    'position' => 'Trưởng phòng Kinh doanh',
                    'department' => 'Kinh doanh',
                    'phone' => '0912345678',
                    'mobile' => '0912345678',
                    'email' => 'contact@hoaphat.com.vn',
                    'is_primary' => true,
                    'responsibilities' => ['Bán hàng', 'Quản lý đơn hàng'],
                ],
                [
                    'name' => 'Lê Văn C',
                    'position' => 'Kỹ sư Sản xuất',
                    'department' => 'Sản xuất',
                    'phone' => '0912345679',
                    'mobile' => '0912345679',
                    'email' => 'production@hoaphat.com.vn',
                    'is_primary' => false,
                    'responsibilities' => ['Kiểm tra chất lượng', 'Lập kế hoạch sản xuất'],
                ],
            ];
        } elseif (str_contains($supplier->company_name, 'Đồng Tâm')) {
            $contacts = [
                [
                    'name' => 'Lê Văn C',
                    'position' => 'Giám đốc Kinh doanh',
                    'department' => 'Kinh doanh',
                    'phone' => '0923456789',
                    'mobile' => '0923456789',
                    'email' => 'info@dongtambrick.com',
                    'is_primary' => true,
                    'responsibilities' => ['Bán hàng', 'Chăm sóc khách hàng'],
                ],
            ];
        } elseif (str_contains($supplier->company_name, 'Cát Đá')) {
            $contacts = [
                [
                    'name' => 'Phạm Thị D',
                    'position' => 'Quản lý Kinh doanh',
                    'department' => 'Kinh doanh',
                    'phone' => '0934567890',
                    'mobile' => '0934567890',
                    'email' => 'sales@catdamiennam.com',
                    'is_primary' => true,
                    'responsibilities' => ['Bán hàng', 'Vận chuyển'],
                ],
                [
                    'name' => 'Nguyễn Văn E',
                    'position' => 'Trưởng kho',
                    'department' => 'Kho vận',
                    'phone' => '0934567891',
                    'mobile' => '0934567891',
                    'email' => 'warehouse@catdamiennam.com',
                    'is_primary' => false,
                    'responsibilities' => ['Quản lý kho', 'Xuất hàng'],
                ],
            ];
        } elseif (str_contains($supplier->company_name, 'Jotun')) {
            $contacts = [
                [
                    'name' => 'Hoàng Văn E',
                    'position' => 'Giám đốc Bán hàng',
                    'department' => 'Kinh doanh',
                    'phone' => '0945678901',
                    'mobile' => '0945678901',
                    'email' => 'vietnam@jotun.com',
                    'is_primary' => true,
                    'responsibilities' => ['Bán hàng', 'Phát triển thị trường'],
                ],
                [
                    'name' => 'Võ Thị F',
                    'position' => 'Chuyên viên Màu sắc',
                    'department' => 'Kỹ thuật',
                    'phone' => '0945678902',
                    'mobile' => '0945678902',
                    'email' => 'color@jotun.com',
                    'is_primary' => false,
                    'responsibilities' => ['Tư vấn màu sắc', 'Hỗ trợ kỹ thuật'],
                ],
            ];
        }

        // Create contacts
        foreach ($contacts as $contactData) {
            SupplierContact::create(array_merge($contactData, [
                'supplier_id' => $supplier->id,
                'is_active' => true,
                'notes' => "Liên hệ chính của {$supplier->company_name}",
            ]));
        }
    }
}
