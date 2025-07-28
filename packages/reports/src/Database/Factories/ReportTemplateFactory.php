<?php

namespace Packages\Reports\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Packages\Reports\Models\ReportTemplate;
use Packages\Store\Models\Store;
use Packages\User\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Packages\Reports\Models\ReportTemplate>
 */
class ReportTemplateFactory extends Factory
{
    protected $model = ReportTemplate::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $reportType = $this->faker->randomElement(['sales', 'inventory', 'financial', 'customer', 'employee', 'product']);
        
        return [
            'store_id' => Store::factory(),
            'code' => $this->faker->unique()->regexify('RPT[0-9]{6}'),
            'name' => $this->getVietnameseTemplateName($reportType),
            'description' => $this->faker->optional()->paragraph(),
            'type' => $reportType,
            'category' => $this->faker->randomElement(['operational', 'financial', 'analytical', 'compliance']),
            'frequency' => $this->faker->optional()->randomElement(['daily', 'weekly', 'monthly', 'quarterly']),
            'data_sources' => [$this->getDataSource($reportType)],
            'filters' => $this->getVietnameseFilters($reportType),
            'columns' => $this->getVietnameseColumns($reportType),
            'grouping' => $this->getGroupingConfig($reportType),
            'sorting' => $this->getSortingConfig(),
            'calculations' => $this->getAggregations($reportType),
            'output_format' => $this->faker->randomElement(['pdf', 'excel', 'csv', 'html']),
            'chart_config' => [
                'type' => $this->faker->optional()->randomElement(['bar', 'line', 'pie', 'column', 'area']),
                'layout' => $this->faker->randomElement(['table', 'chart', 'dashboard', 'summary'])
            ],
            'layout_config' => $this->getFormattingConfig(),
            'permissions' => $this->getVietnamesePermissions(),
            'is_public' => $this->faker->boolean(30),
            'is_system' => false,
            'is_active' => true,
            'auto_refresh' => $this->faker->boolean(20),
            'refresh_interval' => $this->faker->optional()->numberBetween(5, 60),
            'cache_enabled' => $this->faker->boolean(80),
            'created_by' => User::factory(),
        ];
    }

    /**
     * Create template for specific store
     */
    public function forStore($store): static
    {
        return $this->state(fn (array $attributes) => [
            'store_id' => is_object($store) ? $store->id : $store,
        ]);
    }

    /**
     * Create sales report template
     */
    public function salesReport(): static
    {
        return $this->state(fn (array $attributes) => [
            'report_type' => 'sales',
            'template_name' => $this->getVietnameseSalesTemplateName(),
            'data_source' => 'sales_orders,invoices,payments',
            'columns' => $this->getSalesColumns(),
            'chart_type' => 'bar',
        ]);
    }

    /**
     * Create inventory report template
     */
    public function inventoryReport(): static
    {
        return $this->state(fn (array $attributes) => [
            'report_type' => 'inventory',
            'template_name' => $this->getVietnameseInventoryTemplateName(),
            'data_source' => 'material_inventory,inventory_movements',
            'columns' => $this->getInventoryColumns(),
            'chart_type' => 'column',
        ]);
    }

    /**
     * Create financial report template
     */
    public function financialReport(): static
    {
        return $this->state(fn (array $attributes) => [
            'report_type' => 'financial',
            'template_name' => $this->getVietnameseFinancialTemplateName(),
            'data_source' => 'cash_transactions,payments,invoices',
            'columns' => $this->getFinancialColumns(),
            'chart_type' => 'line',
        ]);
    }

    /**
     * Create active template
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * Create scheduled template
     */
    public function scheduled(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_scheduled' => true,
            'schedule_frequency' => $this->faker->randomElement(['daily', 'weekly', 'monthly']),
            'schedule_time' => $this->faker->time('H:i'),
            'recipients' => ['admin@boxpos.vn', 'manager@boxpos.vn'],
        ]);
    }

    /**
     * Create public template
     */
    public function public(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_public' => true,
        ]);
    }

    /**
     * Get Vietnamese template names based on type
     */
    private function getVietnameseTemplateName(string $type): string
    {
        $names = [
            'sales' => [
                'Báo cáo doanh thu bán hàng',
                'Thống kê đơn hàng',
                'Báo cáo bán hàng theo ngày',
                'Phân tích doanh số',
                'Báo cáo khách hàng'
            ],
            'inventory' => [
                'Báo cáo tồn kho',
                'Thống kê xuất nhập kho',
                'Báo cáo kiểm kê',
                'Phân tích hàng tồn',
                'Báo cáo vật tư'
            ],
            'financial' => [
                'Báo cáo tài chính',
                'Thống kê thu chi',
                'Báo cáo dòng tiền',
                'Phân tích lợi nhuận',
                'Báo cáo công nợ'
            ],
            'customer' => [
                'Báo cáo khách hàng',
                'Thống kê mua hàng',
                'Phân tích khách hàng',
                'Báo cáo loyalty',
                'Thống kê chăm sóc KH'
            ],
            'employee' => [
                'Báo cáo nhân viên',
                'Thống kê chấm công',
                'Báo cáo lương',
                'Phân tích hiệu suất',
                'Báo cáo đào tạo'
            ],
            'product' => [
                'Báo cáo sản phẩm',
                'Thống kê bán chạy',
                'Phân tích sản phẩm',
                'Báo cáo danh mục',
                'Thống kê giá bán'
            ]
        ];
        
        return $this->faker->randomElement($names[$type] ?? $names['sales']);
    }

    /**
     * Get data source based on report type
     */
    private function getDataSource(string $type): string
    {
        $sources = [
            'sales' => 'sales_orders,invoices,payments,customers',
            'inventory' => 'material_inventory,inventory_movements,building_materials',
            'financial' => 'cash_transactions,payments,invoices,cash_accounts',
            'customer' => 'customers,sales_orders,loyalty_transactions',
            'employee' => 'employees,employee_schedules,employee_payroll',
            'product' => 'building_materials,products,sales_order_items'
        ];
        
        return $sources[$type] ?? 'sales_orders';
    }

    /**
     * Get query configuration
     */
    private function getQueryConfig(string $type): array
    {
        return [
            'joins' => $this->getJoinConfig($type),
            'conditions' => $this->getConditionConfig($type),
            'date_range' => 'last_30_days',
            'limit' => 1000,
            'distinct' => false
        ];
    }

    /**
     * Get Vietnamese columns based on report type
     */
    private function getVietnameseColumns(string $type): array
    {
        $columns = [
            'sales' => [
                ['name' => 'order_date', 'label' => 'Ngày đặt hàng', 'type' => 'date'],
                ['name' => 'order_number', 'label' => 'Số đơn hàng', 'type' => 'string'],
                ['name' => 'customer_name', 'label' => 'Tên khách hàng', 'type' => 'string'],
                ['name' => 'total_amount', 'label' => 'Tổng tiền', 'type' => 'currency'],
                ['name' => 'status', 'label' => 'Trạng thái', 'type' => 'string']
            ],
            'inventory' => [
                ['name' => 'material_name', 'label' => 'Tên vật liệu', 'type' => 'string'],
                ['name' => 'current_stock', 'label' => 'Tồn kho hiện tại', 'type' => 'number'],
                ['name' => 'min_stock_level', 'label' => 'Tồn kho tối thiểu', 'type' => 'number'],
                ['name' => 'total_value', 'label' => 'Giá trị tồn kho', 'type' => 'currency'],
                ['name' => 'location', 'label' => 'Vị trí', 'type' => 'string']
            ],
            'financial' => [
                ['name' => 'transaction_date', 'label' => 'Ngày giao dịch', 'type' => 'date'],
                ['name' => 'description', 'label' => 'Mô tả', 'type' => 'string'],
                ['name' => 'amount', 'label' => 'Số tiền', 'type' => 'currency'],
                ['name' => 'type', 'label' => 'Loại giao dịch', 'type' => 'string'],
                ['name' => 'balance_after', 'label' => 'Số dư sau GD', 'type' => 'currency']
            ]
        ];
        
        return $columns[$type] ?? $columns['sales'];
    }

    /**
     * Get Vietnamese filters
     */
    private function getVietnameseFilters(string $type): array
    {
        return [
            ['name' => 'date_range', 'label' => 'Khoảng thời gian', 'type' => 'daterange'],
            ['name' => 'store_id', 'label' => 'Cửa hàng', 'type' => 'select'],
            ['name' => 'status', 'label' => 'Trạng thái', 'type' => 'multiselect'],
            ['name' => 'amount_min', 'label' => 'Số tiền tối thiểu', 'type' => 'number'],
            ['name' => 'amount_max', 'label' => 'Số tiền tối đa', 'type' => 'number']
        ];
    }

    /**
     * Get sorting configuration
     */
    private function getSortingConfig(): array
    {
        return [
            'default_column' => 'created_at',
            'default_direction' => 'desc',
            'allowed_columns' => ['created_at', 'total_amount', 'customer_name', 'status']
        ];
    }

    /**
     * Get grouping configuration
     */
    private function getGroupingConfig(string $type): array
    {
        $groupings = [
            'sales' => ['date', 'customer', 'product', 'status'],
            'inventory' => ['category', 'location', 'supplier'],
            'financial' => ['type', 'category', 'account'],
            'customer' => ['group', 'location', 'type'],
            'employee' => ['department', 'position', 'status'],
            'product' => ['category', 'supplier', 'status']
        ];
        
        return [
            'available_groups' => $groupings[$type] ?? ['date'],
            'default_group' => $groupings[$type][0] ?? 'date'
        ];
    }

    /**
     * Get aggregations
     */
    private function getAggregations(string $type): array
    {
        return [
            'sum' => ['total_amount', 'quantity', 'value'],
            'avg' => ['total_amount', 'quantity'],
            'count' => ['id', 'customer_id'],
            'min' => ['total_amount', 'date'],
            'max' => ['total_amount', 'date']
        ];
    }

    /**
     * Get formatting configuration
     */
    private function getFormattingConfig(): array
    {
        return [
            'currency_symbol' => 'đ',
            'currency_position' => 'after',
            'decimal_places' => 0,
            'thousand_separator' => '.',
            'date_format' => 'd/m/Y',
            'time_format' => 'H:i',
            'number_format' => '#,##0'
        ];
    }

    /**
     * Get Vietnamese permissions
     */
    private function getVietnamesePermissions(): array
    {
        return [
            'Xem báo cáo',
            'Xuất báo cáo',
            'Chỉnh sửa báo cáo',
            'Xóa báo cáo',
            'Chia sẻ báo cáo',
            'Lên lịch báo cáo'
        ];
    }

    /**
     * Get Vietnamese tags based on type
     */
    private function getVietnameseTags(string $type): array
    {
        $tags = [
            'sales' => ['bán hàng', 'doanh thu', 'khách hàng', 'đơn hàng'],
            'inventory' => ['kho hàng', 'tồn kho', 'vật liệu', 'xuất nhập'],
            'financial' => ['tài chính', 'thu chi', 'dòng tiền', 'lợi nhuận'],
            'customer' => ['khách hàng', 'chăm sóc', 'loyalty', 'phân tích'],
            'employee' => ['nhân viên', 'lương', 'chấm công', 'hiệu suất'],
            'product' => ['sản phẩm', 'danh mục', 'giá bán', 'bán chạy']
        ];
        
        return $tags[$type] ?? ['báo cáo', 'thống kê'];
    }

    /**
     * Get report metadata
     */
    private function getReportMetadata(): array
    {
        return [
            'version' => '1.0',
            'author' => $this->faker->name(),
            'created_date' => $this->faker->dateTimeBetween('-1 year', 'now')->format('Y-m-d'),
            'last_modified' => $this->faker->dateTimeBetween('-1 month', 'now')->format('Y-m-d'),
            'usage_count' => $this->faker->numberBetween(0, 100),
            'avg_execution_time' => $this->faker->randomFloat(2, 0.5, 10.0),
            'data_freshness' => $this->faker->randomElement(['real_time', 'hourly', 'daily']),
            'complexity' => $this->faker->randomElement(['simple', 'medium', 'complex'])
        ];
    }

    /**
     * Get join configuration
     */
    private function getJoinConfig(string $type): array
    {
        $joins = [
            'sales' => [
                ['table' => 'customers', 'on' => 'sales_orders.customer_id = customers.id'],
                ['table' => 'invoices', 'on' => 'sales_orders.id = invoices.sales_order_id']
            ],
            'inventory' => [
                ['table' => 'building_materials', 'on' => 'material_inventory.material_id = building_materials.id'],
                ['table' => 'material_categories', 'on' => 'building_materials.category_id = material_categories.id']
            ]
        ];
        
        return $joins[$type] ?? [];
    }

    /**
     * Get condition configuration
     */
    private function getConditionConfig(string $type): array
    {
        return [
            'default_conditions' => [
                ['column' => 'store_id', 'operator' => '=', 'value' => '{{current_store_id}}'],
                ['column' => 'created_at', 'operator' => '>=', 'value' => '{{date_from}}'],
                ['column' => 'created_at', 'operator' => '<=', 'value' => '{{date_to}}']
            ]
        ];
    }

    /**
     * Get Vietnamese sales template names
     */
    private function getVietnameseSalesTemplateName(): string
    {
        $names = [
            'Báo cáo doanh thu hàng ngày',
            'Thống kê bán hàng theo tháng',
            'Phân tích khách hàng mua nhiều',
            'Báo cáo sản phẩm bán chạy',
            'Thống kê đơn hàng theo trạng thái'
        ];
        
        return $this->faker->randomElement($names);
    }

    /**
     * Get Vietnamese inventory template names
     */
    private function getVietnameseInventoryTemplateName(): string
    {
        $names = [
            'Báo cáo tồn kho theo danh mục',
            'Thống kê xuất nhập kho hàng ngày',
            'Báo cáo vật liệu sắp hết',
            'Phân tích giá trị tồn kho',
            'Báo cáo kiểm kê định kỳ'
        ];
        
        return $this->faker->randomElement($names);
    }

    /**
     * Get Vietnamese financial template names
     */
    private function getVietnameseFinancialTemplateName(): string
    {
        $names = [
            'Báo cáo thu chi hàng ngày',
            'Thống kê dòng tiền theo tháng',
            'Báo cáo công nợ khách hàng',
            'Phân tích lợi nhuận theo sản phẩm',
            'Báo cáo tài chính tổng hợp'
        ];
        
        return $this->faker->randomElement($names);
    }

    /**
     * Get sales columns
     */
    private function getSalesColumns(): array
    {
        return [
            ['name' => 'order_date', 'label' => 'Ngày đặt hàng', 'type' => 'date'],
            ['name' => 'order_number', 'label' => 'Số đơn hàng', 'type' => 'string'],
            ['name' => 'customer_name', 'label' => 'Tên khách hàng', 'type' => 'string'],
            ['name' => 'total_amount', 'label' => 'Tổng tiền', 'type' => 'currency'],
            ['name' => 'payment_status', 'label' => 'Trạng thái thanh toán', 'type' => 'string'],
            ['name' => 'delivery_status', 'label' => 'Trạng thái giao hàng', 'type' => 'string']
        ];
    }

    /**
     * Get inventory columns
     */
    private function getInventoryColumns(): array
    {
        return [
            ['name' => 'material_code', 'label' => 'Mã vật liệu', 'type' => 'string'],
            ['name' => 'material_name', 'label' => 'Tên vật liệu', 'type' => 'string'],
            ['name' => 'category_name', 'label' => 'Danh mục', 'type' => 'string'],
            ['name' => 'current_stock', 'label' => 'Tồn kho hiện tại', 'type' => 'number'],
            ['name' => 'min_stock_level', 'label' => 'Tồn kho tối thiểu', 'type' => 'number'],
            ['name' => 'total_value', 'label' => 'Giá trị tồn kho', 'type' => 'currency']
        ];
    }

    /**
     * Get financial columns
     */
    private function getFinancialColumns(): array
    {
        return [
            ['name' => 'transaction_date', 'label' => 'Ngày giao dịch', 'type' => 'date'],
            ['name' => 'transaction_code', 'label' => 'Mã giao dịch', 'type' => 'string'],
            ['name' => 'description', 'label' => 'Mô tả', 'type' => 'string'],
            ['name' => 'type', 'label' => 'Loại giao dịch', 'type' => 'string'],
            ['name' => 'amount', 'label' => 'Số tiền', 'type' => 'currency'],
            ['name' => 'balance_after', 'label' => 'Số dư sau GD', 'type' => 'currency']
        ];
    }
}