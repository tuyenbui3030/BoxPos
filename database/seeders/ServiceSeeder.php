<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Packages\Store\Models\Store;
use Packages\Products\Models\ProductCategory;
use Packages\Products\Models\Service;
use Packages\User\Models\User;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🔧 Seeding Services...');

        $stores = Store::all();

        foreach ($stores as $store) {
            $this->createServicesForStore($store);
        }

        $this->command->info('✅ Services seeded successfully!');
    }

    private function createServicesForStore(Store $store): void
    {
        $createdBy = User::where('email', 'admin@' . $store->domain)->first();

        // Create service categories first
        $serviceCategories = $this->createServiceCategories($store, $createdBy);

        // Create services
        $this->createServices($store, $serviceCategories, $createdBy);
    }

    private function createServiceCategories(Store $store, ?User $createdBy): array
    {
        $categories = [
            [
                'name' => 'Dịch vụ Thi công',
                'slug' => 'dich-vu-thi-cong',
                'description' => 'Các dịch vụ thi công xây dựng chuyên nghiệp',
                'services' => [
                    'Thi công móng',
                    'Thi công tường',
                    'Thi công mái',
                    'Thi công sàn',
                    'Hoàn thiện nội thất',
                ]
            ],
            [
                'name' => 'Dịch vụ Thiết kế',
                'slug' => 'dich-vu-thiet-ke',
                'description' => 'Dịch vụ thiết kế kiến trúc và nội thất',
                'services' => [
                    'Thiết kế kiến trúc',
                    'Thiết kế nội thất',
                    'Thiết kế cảnh quan',
                    'Thiết kế 3D',
                    'Bản vẽ thi công',
                ]
            ],
            [
                'name' => 'Dịch vụ Vận chuyển',
                'slug' => 'dich-vu-van-chuyen',
                'description' => 'Dịch vụ vận chuyển và giao hàng',
                'services' => [
                    'Vận chuyển nội thành',
                    'Vận chuyển liên tỉnh',
                    'Cho thuê xe cẩu',
                    'Bốc xếp hàng hóa',
                    'Giao hàng tận nơi',
                ]
            ],
            [
                'name' => 'Dịch vụ Bảo trì',
                'slug' => 'dich-vu-bao-tri',
                'description' => 'Dịch vụ bảo trì và sửa chữa',
                'services' => [
                    'Bảo trì định kỳ',
                    'Sửa chữa khẩn cấp',
                    'Thay thế linh kiện',
                    'Kiểm tra an toàn',
                    'Bảo hành sản phẩm',
                ]
            ],
            [
                'name' => 'Dịch vụ Tư vấn',
                'slug' => 'dich-vu-tu-van',
                'description' => 'Dịch vụ tư vấn chuyên môn',
                'services' => [
                    'Tư vấn kỹ thuật',
                    'Tư vấn thiết kế',
                    'Tư vấn vật liệu',
                    'Khảo sát hiện trạng',
                    'Lập dự toán',
                ]
            ],
        ];

        $createdCategories = [];

        foreach ($categories as $categoryData) {
            $services = $categoryData['services'];
            unset($categoryData['services']);

            $category = ProductCategory::create([
                ...$categoryData,
                'store_id' => $store->id,
                'category_type' => 'service',
                'is_active' => true,
                'sort_order' => 0,
                'created_by' => $createdBy?->id,
            ]);

            $createdCategories[] = [
                'category' => $category,
                'services' => $services,
            ];
        }

        return $createdCategories;
    }

    private function createServices(Store $store, array $serviceCategories, ?User $createdBy): void
    {
        foreach ($serviceCategories as $categoryData) {
            $category = $categoryData['category'];
            $serviceNames = $categoryData['services'];

            foreach ($serviceNames as $index => $serviceName) {
                $this->createService($store, $category, $serviceName, $index + 1, $createdBy);
            }
        }
    }

    private function createService(Store $store, ProductCategory $category, string $serviceName, int $index, ?User $createdBy): void
    {
        $serviceCode = 'SV-' . strtoupper(substr($category->slug, 0, 3)) . '-' . str_pad($index, 3, '0', STR_PAD_LEFT);
        $slug = $this->generateSlug($serviceName);

        // Generate pricing based on service type
        $pricing = $this->generateServicePricing($serviceName, $category->name);

        Service::create([
            'store_id' => $store->id,
            'category_id' => $category->id,
            'service_code' => $serviceCode,
            'name' => $serviceName,
            'slug' => $slug,
            'description' => $this->generateServiceDescription($serviceName),
            'short_description' => $this->generateShortDescription($serviceName),
            'service_type' => $this->determineServiceType($serviceName),
            'duration_hours' => $this->estimateDuration($serviceName),
            'duration_type' => $this->determineDurationType($serviceName),
            'base_price' => $pricing['base_price'],
            'hourly_rate' => $pricing['hourly_rate'],
            'fixed_price' => $pricing['fixed_price'],
            'minimum_charge' => $pricing['minimum_charge'],
            'pricing_model' => $pricing['pricing_model'],
            'currency' => 'VND',
            'tax_rate' => 10,
            'tax_inclusive' => false,
            'requires_materials' => $this->requiresMaterials($serviceName),
            'requires_equipment' => $this->requiresEquipment($serviceName),
            'skill_level' => $this->determineSkillLevel($serviceName),
            'team_size' => $this->estimateTeamSize($serviceName),
            'location_type' => $this->determineLocationType($serviceName),
            'availability' => json_encode([
                'days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'],
                'hours' => ['start' => '07:00', 'end' => '17:00'],
                'holidays' => false,
            ]),
            'service_area' => json_encode(['Hà Nội', 'TP.HCM', 'Đà Nẵng']),
            'prerequisites' => json_encode($this->getPrerequisites($serviceName)),
            'deliverables' => json_encode($this->getDeliverables($serviceName)),
            'warranty_period' => $this->getWarrantyPeriod($serviceName),
            'warranty_terms' => $this->getWarrantyTerms($serviceName),
            'is_active' => true,
            'is_featured' => rand(0, 1) == 1,
            'requires_booking' => $this->requiresBooking($serviceName),
            'advance_booking_hours' => $this->getAdvanceBookingHours($serviceName),
            'cancellation_policy' => $this->getCancellationPolicy($serviceName),
            'tags' => json_encode($this->generateTags($serviceName, $category->name)),
            'metadata' => json_encode([
                'created_via' => 'seeder',
                'category_type' => $category->name,
                'complexity' => $this->determineComplexity($serviceName),
            ]),
            'created_by' => $createdBy?->id,
        ]);
    }

    private function generateSlug(string $serviceName): string
    {
        return strtolower(str_replace([' ', 'ă', 'â', 'đ', 'ê', 'ô', 'ơ', 'ư'], ['-', 'a', 'a', 'd', 'e', 'o', 'o', 'u'], $serviceName));
    }

    private function generateServiceDescription(string $serviceName): string
    {
        $descriptions = [
            'Thi công móng' => 'Dịch vụ thi công móng nhà chuyên nghiệp, đảm bảo chất lượng và tiến độ. Sử dụng vật liệu cao cấp và công nghệ hiện đại.',
            'Thiết kế kiến trúc' => 'Dịch vụ thiết kế kiến trúc sáng tạo, phù hợp với nhu cầu và ngân sách của khách hàng. Đội ngũ kiến trúc sư giàu kinh nghiệm.',
            'Vận chuyển nội thành' => 'Dịch vụ vận chuyển vật liệu xây dựng trong nội thành, nhanh chóng và an toàn. Đội xe chuyên dụng.',
            'Bảo trì định kỳ' => 'Dịch vụ bảo trì định kỳ cho công trình, đảm bảo tuổi thọ và an toàn sử dụng. Lịch bảo trì linh hoạt.',
            'Tư vấn kỹ thuật' => 'Dịch vụ tư vấn kỹ thuật chuyên sâu từ đội ngũ chuyên gia giàu kinh nghiệm. Giải pháp tối ưu cho mọi vấn đề.',
        ];

        return $descriptions[$serviceName] ?? "Dịch vụ {$serviceName} chuyên nghiệp với chất lượng cao và giá cả hợp lý.";
    }

    private function generateShortDescription(string $serviceName): string
    {
        return "Dịch vụ {$serviceName} chuyên nghiệp, chất lượng cao.";
    }

    private function determineServiceType(string $serviceName): string
    {
        if (str_contains($serviceName, 'Thi công')) return 'construction';
        if (str_contains($serviceName, 'Thiết kế')) return 'design';
        if (str_contains($serviceName, 'Vận chuyển')) return 'logistics';
        if (str_contains($serviceName, 'Bảo trì')) return 'maintenance';
        if (str_contains($serviceName, 'Tư vấn')) return 'consultation';
        return 'general';
    }

    private function generateServicePricing(string $serviceName, string $categoryName): array
    {
        $baseRates = [
            'Thi công' => ['base' => 500000, 'hourly' => 150000],
            'Thiết kế' => ['base' => 300000, 'hourly' => 200000],
            'Vận chuyển' => ['base' => 200000, 'hourly' => 100000],
            'Bảo trì' => ['base' => 400000, 'hourly' => 120000],
            'Tư vấn' => ['base' => 600000, 'hourly' => 250000],
        ];

        $categoryKey = '';
        foreach ($baseRates as $key => $rates) {
            if (str_contains($categoryName, $key)) {
                $categoryKey = $key;
                break;
            }
        }

        $rates = $baseRates[$categoryKey] ?? ['base' => 400000, 'hourly' => 150000];
        
        return [
            'base_price' => $rates['base'] + rand(-100000, 200000),
            'hourly_rate' => $rates['hourly'] + rand(-50000, 100000),
            'fixed_price' => ($rates['base'] * 2) + rand(-200000, 500000),
            'minimum_charge' => $rates['base'] * 0.5,
            'pricing_model' => rand(0, 1) == 1 ? 'hourly' : 'fixed',
        ];
    }

    private function estimateDuration(string $serviceName): int
    {
        $durations = [
            'Thi công móng' => 48,
            'Thiết kế kiến trúc' => 24,
            'Vận chuyển nội thành' => 4,
            'Bảo trì định kỳ' => 8,
            'Tư vấn kỹ thuật' => 2,
        ];

        return $durations[$serviceName] ?? rand(4, 24);
    }

    private function determineDurationType(string $serviceName): string
    {
        if (str_contains($serviceName, 'Thi công')) return 'days';
        if (str_contains($serviceName, 'Thiết kế')) return 'days';
        if (str_contains($serviceName, 'Vận chuyển')) return 'hours';
        return 'hours';
    }

    private function requiresMaterials(string $serviceName): bool
    {
        return str_contains($serviceName, 'Thi công') || str_contains($serviceName, 'Bảo trì');
    }

    private function requiresEquipment(string $serviceName): bool
    {
        return str_contains($serviceName, 'Thi công') || str_contains($serviceName, 'Vận chuyển');
    }

    private function determineSkillLevel(string $serviceName): string
    {
        if (str_contains($serviceName, 'Thiết kế') || str_contains($serviceName, 'Tư vấn')) return 'expert';
        if (str_contains($serviceName, 'Thi công')) return 'professional';
        return 'intermediate';
    }

    private function estimateTeamSize(string $serviceName): int
    {
        if (str_contains($serviceName, 'Thi công')) return rand(3, 8);
        if (str_contains($serviceName, 'Vận chuyển')) return rand(2, 4);
        return rand(1, 3);
    }

    private function determineLocationType(string $serviceName): string
    {
        if (str_contains($serviceName, 'Thiết kế')) return 'office';
        return 'on_site';
    }

    private function getPrerequisites(string $serviceName): array
    {
        $prerequisites = [
            'Thi công móng' => ['Bản vẽ thiết kế', 'Giấy phép xây dựng', 'Khảo sát địa chất'],
            'Thiết kế kiến trúc' => ['Thông tin yêu cầu', 'Bản đồ địa hình', 'Ngân sách dự kiến'],
            'Vận chuyển nội thành' => ['Địa chỉ giao hàng', 'Thông tin liên hệ', 'Đường đi phù hợp'],
        ];

        return $prerequisites[$serviceName] ?? ['Thông tin chi tiết yêu cầu'];
    }

    private function getDeliverables(string $serviceName): array
    {
        $deliverables = [
            'Thi công móng' => ['Móng hoàn thiện', 'Biên bản nghiệm thu', 'Bảo hành 24 tháng'],
            'Thiết kế kiến trúc' => ['Bản vẽ thiết kế', 'Thuyết minh dự án', 'Dự toán chi tiết'],
            'Vận chuyển nội thành' => ['Giao hàng đúng hạn', 'Biên bản giao nhận', 'Bảo hiểm hàng hóa'],
        ];

        return $deliverables[$serviceName] ?? ['Hoàn thành dịch vụ theo yêu cầu'];
    }

    private function getWarrantyPeriod(string $serviceName): ?int
    {
        if (str_contains($serviceName, 'Thi công')) return 24; // 24 months
        if (str_contains($serviceName, 'Thiết kế')) return 12; // 12 months
        if (str_contains($serviceName, 'Bảo trì')) return 6; // 6 months
        return null;
    }

    private function getWarrantyTerms(string $serviceName): ?string
    {
        if (str_contains($serviceName, 'Thi công')) {
            return 'Bảo hành 24 tháng cho lỗi kỹ thuật, không bao gồm hư hỏng do thiên tai';
        }
        return null;
    }

    private function requiresBooking(string $serviceName): bool
    {
        return !str_contains($serviceName, 'Tư vấn');
    }

    private function getAdvanceBookingHours(string $serviceName): int
    {
        if (str_contains($serviceName, 'Thi công')) return 72; // 3 days
        if (str_contains($serviceName, 'Thiết kế')) return 48; // 2 days
        return 24; // 1 day
    }

    private function getCancellationPolicy(string $serviceName): string
    {
        return 'Có thể hủy miễn phí trước 24 giờ. Hủy trong vòng 24 giờ sẽ tính phí 50%.';
    }

    private function generateTags(string $serviceName, string $categoryName): array
    {
        $baseTags = ['chuyên nghiệp', 'chất lượng cao', 'giá tốt'];
        
        if (str_contains($serviceName, 'Thi công')) {
            $baseTags = array_merge($baseTags, ['thi công', 'xây dựng', 'chuyên nghiệp']);
        }
        
        return $baseTags;
    }

    private function determineComplexity(string $serviceName): string
    {
        if (str_contains($serviceName, 'Thiết kế') || str_contains($serviceName, 'Tư vấn')) return 'high';
        if (str_contains($serviceName, 'Thi công')) return 'medium';
        return 'low';
    }
}
