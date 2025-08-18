<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Application;

class ApplicationSeeder extends Seeder
{
    public function run(): void
    {
        $applications = [
            [
                'name' => 'BuildingPos',
                'slug' => 'building-pos',
                'description' => 'Hệ thống quản lý vật liệu xây dựng - Quản lý kho, bán hàng, nhà cung cấp cho cửa hàng vật liệu xây dựng',
                'icon' => 'apps/building-pos.png',
                'color_scheme' => [
                    'primary' => '#F59E0B',
                    'secondary' => '#92400E',
                    'accent' => '#FCD34D',
                ],
                'available_packages' => [
                    'material-catalog',
                    'material-inventory',
                    'material-suppliers',
                    'material-pricing',
                    'material-purchasing',
                    'warehouse',
                    'sales-orders',
                    'customer',
                    'reports',
                ],
                'pricing_tiers' => [
                    'trial' => [
                        'name' => 'Dùng thử 14 ngày',
                        'monthly_price' => 0,
                        'max_stores' => 1,
                        'features' => ['basic_inventory', 'basic_sales', 'basic_reports'],
                        'description' => 'Dùng thử miễn phí 14 ngày với đầy đủ tính năng',
                    ],
                    'basic' => [
                        'name' => 'Cơ bản',
                        'monthly_price' => 299000,
                        'max_stores' => 1,
                        'features' => ['inventory_management', 'sales_management', 'customer_management', 'basic_reports'],
                        'description' => 'Phù hợp cho cửa hàng nhỏ, 1 địa điểm',
                    ],
                    'professional' => [
                        'name' => 'Chuyên nghiệp',
                        'monthly_price' => 599000,
                        'max_stores' => 3,
                        'features' => ['*'], // All features
                        'description' => 'Phù hợp cho chuỗi cửa hàng nhỏ, tối đa 3 địa điểm',
                    ],
                    'enterprise' => [
                        'name' => 'Doanh nghiệp',
                        'monthly_price' => 1299000,
                        'max_stores' => -1, // Unlimited
                        'features' => ['*'], // All features
                        'description' => 'Không giới hạn địa điểm, hỗ trợ ưu tiên',
                    ],
                ],
                'features' => [
                    'inventory_management' => 'Quản lý kho hàng',
                    'sales_management' => 'Quản lý bán hàng',
                    'customer_management' => 'Quản lý khách hàng',
                    'supplier_management' => 'Quản lý nhà cung cấp',
                    'pricing_management' => 'Quản lý giá',
                    'warehouse_management' => 'Quản lý kho',
                    'advanced_reports' => 'Báo cáo nâng cao',
                    'multi_store' => 'Đa cửa hàng',
                    'api_access' => 'Truy cập API',
                    'priority_support' => 'Hỗ trợ ưu tiên',
                ],
                'status' => 'active',
                'sort_order' => 1,
            ],
            [
                'name' => 'ClinicPos',
                'slug' => 'clinic-pos',
                'description' => 'Hệ thống quản lý phòng khám - Quản lý bệnh nhân, lịch hẹn, kê đơn thuốc, thanh toán',
                'icon' => 'apps/clinic-pos.png',
                'color_scheme' => [
                    'primary' => '#10B981',
                    'secondary' => '#047857',
                    'accent' => '#6EE7B7',
                ],
                'available_packages' => [
                    'patient-management',
                    'appointment-scheduling',
                    'medical-records',
                    'prescription-management',
                    'medical-inventory',
                    'insurance-billing',
                    'medical-reports',
                    'payments',
                ],
                'pricing_tiers' => [
                    'trial' => [
                        'name' => 'Dùng thử 14 ngày',
                        'monthly_price' => 0,
                        'max_stores' => 1,
                        'features' => ['basic_patient_management', 'basic_appointments', 'basic_records'],
                        'description' => 'Dùng thử miễn phí 14 ngày',
                    ],
                    'basic' => [
                        'name' => 'Phòng khám nhỏ',
                        'monthly_price' => 499000,
                        'max_stores' => 1,
                        'features' => ['patient_management', 'appointment_scheduling', 'medical_records', 'basic_reports'],
                        'description' => 'Phù hợp cho phòng khám 1-2 bác sĩ',
                    ],
                    'professional' => [
                        'name' => 'Phòng khám lớn',
                        'monthly_price' => 999000,
                        'max_stores' => 2,
                        'features' => ['*'],
                        'description' => 'Phù hợp cho phòng khám đa khoa, nhiều bác sĩ',
                    ],
                    'enterprise' => [
                        'name' => 'Chuỗi phòng khám',
                        'monthly_price' => 1999000,
                        'max_stores' => -1,
                        'features' => ['*'],
                        'description' => 'Chuỗi phòng khám, bệnh viện tư',
                    ],
                ],
                'features' => [
                    'patient_management' => 'Quản lý bệnh nhân',
                    'appointment_scheduling' => 'Đặt lịch hẹn',
                    'medical_records' => 'Hồ sơ bệnh án',
                    'prescription_management' => 'Kê đơn thuốc',
                    'medical_inventory' => 'Quản lý thuốc',
                    'insurance_billing' => 'Thanh toán bảo hiểm',
                    'telemedicine' => 'Khám từ xa',
                    'lab_integration' => 'Kết nối xét nghiệm',
                    'multi_clinic' => 'Đa phòng khám',
                    'priority_support' => 'Hỗ trợ ưu tiên',
                ],
                'status' => 'beta',
                'sort_order' => 2,
            ],
            [
                'name' => 'RestaurantPos',
                'slug' => 'restaurant-pos',
                'description' => 'Hệ thống quản lý nhà hàng - Quản lý menu, đặt bàn, order, bếp, thanh toán',
                'icon' => 'apps/restaurant-pos.png',
                'color_scheme' => [
                    'primary' => '#EF4444',
                    'secondary' => '#DC2626',
                    'accent' => '#FCA5A5',
                ],
                'available_packages' => [
                    'menu-management',
                    'table-management',
                    'order-management',
                    'kitchen-display',
                    'restaurant-inventory',
                    'staff-management',
                    'restaurant-reports',
                    'payments',
                ],
                'pricing_tiers' => [
                    'trial' => [
                        'name' => 'Dùng thử 14 ngày',
                        'monthly_price' => 0,
                        'max_stores' => 1,
                        'features' => ['basic_menu', 'basic_orders', 'basic_payments'],
                        'description' => 'Dùng thử miễn phí 14 ngày',
                    ],
                    'basic' => [
                        'name' => 'Quán nhỏ',
                        'monthly_price' => 399000,
                        'max_stores' => 1,
                        'features' => ['menu_management', 'order_management', 'basic_reports'],
                        'description' => 'Phù hợp cho quán ăn, cafe nhỏ',
                    ],
                    'professional' => [
                        'name' => 'Nhà hàng',
                        'monthly_price' => 799000,
                        'max_stores' => 2,
                        'features' => ['*'],
                        'description' => 'Nhà hàng trung bình, nhiều bàn',
                    ],
                    'enterprise' => [
                        'name' => 'Chuỗi nhà hàng',
                        'monthly_price' => 1599000,
                        'max_stores' => -1,
                        'features' => ['*'],
                        'description' => 'Chuỗi nhà hàng, franchise',
                    ],
                ],
                'features' => [
                    'menu_management' => 'Quản lý menu',
                    'table_management' => 'Quản lý bàn',
                    'order_management' => 'Quản lý order',
                    'kitchen_display' => 'Màn hình bếp',
                    'inventory_management' => 'Quản lý nguyên liệu',
                    'staff_management' => 'Quản lý nhân viên',
                    'delivery_integration' => 'Tích hợp giao hàng',
                    'loyalty_program' => 'Chương trình khách hàng thân thiết',
                    'multi_location' => 'Đa địa điểm',
                    'priority_support' => 'Hỗ trợ ưu tiên',
                ],
                'status' => 'coming_soon',
                'sort_order' => 3,
            ],
            [
                'name' => 'BeautyPos',
                'slug' => 'beauty-pos',
                'description' => 'Hệ thống quản lý salon làm đẹp - Đặt lịch, quản lý dịch vụ, khách hàng, nhân viên',
                'icon' => 'apps/beauty-pos.png',
                'color_scheme' => [
                    'primary' => '#EC4899',
                    'secondary' => '#BE185D',
                    'accent' => '#F9A8D4',
                ],
                'available_packages' => [
                    'appointment-booking',
                    'service-management',
                    'customer-profiles',
                    'staff-scheduling',
                    'beauty-inventory',
                    'commission-tracking',
                    'beauty-reports',
                    'payments',
                ],
                'pricing_tiers' => [
                    'trial' => [
                        'name' => 'Dùng thử 14 ngày',
                        'monthly_price' => 0,
                        'max_stores' => 1,
                        'features' => ['basic_booking', 'basic_services', 'basic_customers'],
                        'description' => 'Dùng thử miễn phí 14 ngày',
                    ],
                    'basic' => [
                        'name' => 'Salon nhỏ',
                        'monthly_price' => 349000,
                        'max_stores' => 1,
                        'features' => ['appointment_booking', 'service_management', 'customer_management'],
                        'description' => 'Salon 1-3 nhân viên',
                    ],
                    'professional' => [
                        'name' => 'Salon lớn',
                        'monthly_price' => 699000,
                        'max_stores' => 2,
                        'features' => ['*'],
                        'description' => 'Salon nhiều nhân viên, dịch vụ đa dạng',
                    ],
                    'enterprise' => [
                        'name' => 'Chuỗi salon',
                        'monthly_price' => 1399000,
                        'max_stores' => -1,
                        'features' => ['*'],
                        'description' => 'Chuỗi salon, spa cao cấp',
                    ],
                ],
                'features' => [
                    'appointment_booking' => 'Đặt lịch hẹn',
                    'service_management' => 'Quản lý dịch vụ',
                    'customer_profiles' => 'Hồ sơ khách hàng',
                    'staff_scheduling' => 'Lịch làm việc nhân viên',
                    'inventory_management' => 'Quản lý sản phẩm',
                    'commission_tracking' => 'Theo dõi hoa hồng',
                    'loyalty_program' => 'Chương trình thành viên',
                    'online_booking' => 'Đặt lịch online',
                    'multi_location' => 'Đa địa điểm',
                    'priority_support' => 'Hỗ trợ ưu tiên',
                ],
                'status' => 'coming_soon',
                'sort_order' => 4,
            ],
        ];

        foreach ($applications as $appData) {
            Application::create($appData);
        }

        $this->command->info('✅ Created ' . count($applications) . ' applications');
        $this->command->info('📱 Available apps:');
        foreach ($applications as $app) {
            $status = match($app['status']) {
                'active' => '🟢',
                'beta' => '🟡',
                'coming_soon' => '🔵',
                default => '⚪'
            };
            $this->command->info("   {$status} {$app['name']} ({$app['slug']})");
        }
    }
}