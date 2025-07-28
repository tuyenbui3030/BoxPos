<?php

namespace Packages\Reports\Database\Seeders;

use Database\Seeders\BasePackageSeeder;
use Packages\Reports\Models\ReportTemplate;
use Packages\Reports\Models\ReportInstance;
use Packages\Reports\Models\ReportSchedule;
use Packages\Reports\Models\ReportDashboard;
use Packages\Store\Models\Store;
use Packages\User\Models\User;
use Carbon\Carbon;

class ReportSeeder extends BasePackageSeeder
{
    public function run(): void
    {
        $this->ensureSeedingAllowed();
        
        $this->executeWithTransaction(function () {
            $this->logSeedingProgress('reports_seeding_started');

            $this->seedForAllStores(function (Store $store) {
                // Create report templates
                $templates = $this->createReportTemplates($store);
                
                // Create report instances for each template
                foreach ($templates as $template) {
                    $instanceCount = $this->getRecordCount(15, 3);
                    $this->createReportInstances($template, $instanceCount);
                }
                
                // Create report schedules
                $scheduleCount = min(3, $templates->count());
                foreach ($templates->take($scheduleCount) as $template) {
                    $this->createReportSchedule($template);
                }
                
                // Create report dashboard
                $this->createReportDashboard($store);
            });

            $this->logSeedingProgress('reports_seeding_completed');
        });
    }

    private function createReportTemplates(Store $store): \Illuminate\Support\Collection
    {
        $this->logSeedingProgress('creating_report_templates', [
            'store_id' => $store->id,
            'store_name' => $store->name
        ]);

        $createdBy = User::whereHas('stores', function($query) use ($store) {
            $query->where('store_id', $store->id);
        })->first();
        
        $templates = [
            [
                'code' => 'DAILY_SALES',
                'name' => 'Báo cáo Doanh thu Hàng ngày',
                'description' => 'Báo cáo tổng hợp doanh thu theo ngày',
                'category' => 'sales',
                'type' => 'summary',
                'data_sources' => json_encode(['sales_orders', 'invoices']),
                'columns' => json_encode([
                    ['name' => 'date', 'label' => 'Ngày', 'type' => 'date'],
                    ['name' => 'total_orders', 'label' => 'Số đơn hàng', 'type' => 'number'],
                    ['name' => 'total_revenue', 'label' => 'Doanh thu', 'type' => 'currency'],
                    ['name' => 'avg_order_value', 'label' => 'Giá trị TB/đơn', 'type' => 'currency'],
                ]),
                'output_format' => 'export',
            ],
            [
                'code' => 'INVENTORY_SUMMARY',
                'name' => 'Tổng quan Tồn kho',
                'description' => 'Báo cáo tình hình tồn kho hiện tại',
                'category' => 'inventory',
                'type' => 'summary',
                'data_sources' => json_encode(['material_inventory', 'products']),
                'columns' => json_encode([
                    ['name' => 'item_name', 'label' => 'Tên sản phẩm', 'type' => 'text'],
                    ['name' => 'current_stock', 'label' => 'Tồn kho', 'type' => 'number'],
                    ['name' => 'unit_cost', 'label' => 'Giá vốn', 'type' => 'currency'],
                    ['name' => 'total_value', 'label' => 'Giá trị tồn kho', 'type' => 'currency'],
                ]),
                'output_format' => 'table',
            ],
            [
                'code' => 'CUSTOMER_ANALYSIS',
                'name' => 'Phân tích Khách hàng',
                'description' => 'Báo cáo phân tích hành vi khách hàng',
                'category' => 'customer',
                'type' => 'detail',
                'data_sources' => json_encode(['customers', 'sales_orders']),
                'columns' => json_encode([
                    ['name' => 'customer_name', 'label' => 'Tên khách hàng', 'type' => 'text'],
                    ['name' => 'total_orders', 'label' => 'Số đơn hàng', 'type' => 'number'],
                    ['name' => 'total_spent', 'label' => 'Tổng chi tiêu', 'type' => 'currency'],
                    ['name' => 'last_order_date', 'label' => 'Đơn hàng cuối', 'type' => 'date'],
                ]),
                'output_format' => 'export',
            ],
            [
                'code' => 'FINANCIAL_SUMMARY',
                'name' => 'Tổng quan Tài chính',
                'description' => 'Báo cáo tổng quan tình hình tài chính',
                'category' => 'financial',
                'type' => 'summary',
                'data_sources' => json_encode(['cash_transactions', 'payments']),
                'columns' => json_encode([
                    ['name' => 'period', 'label' => 'Kỳ', 'type' => 'text'],
                    ['name' => 'revenue', 'label' => 'Doanh thu', 'type' => 'currency'],
                    ['name' => 'expenses', 'label' => 'Chi phí', 'type' => 'currency'],
                    ['name' => 'profit', 'label' => 'Lợi nhuận', 'type' => 'currency'],
                ]),
                'output_format' => 'chart',
            ],
            [
                'code' => 'EMPLOYEE_PERFORMANCE',
                'name' => 'Hiệu suất Nhân viên',
                'description' => 'Báo cáo đánh giá hiệu suất làm việc',
                'category' => 'employee',
                'type' => 'summary',
                'data_sources' => json_encode(['employees', 'employee_timesheets']),
                'columns' => json_encode([
                    ['name' => 'employee_name', 'label' => 'Tên nhân viên', 'type' => 'text'],
                    ['name' => 'total_hours', 'label' => 'Tổng giờ làm', 'type' => 'number'],
                    ['name' => 'sales_amount', 'label' => 'Doanh số bán hàng', 'type' => 'currency'],
                    ['name' => 'commission', 'label' => 'Hoa hồng', 'type' => 'currency'],
                ]),
                'output_format' => 'export',
            ],
            [
                'code' => 'MATERIAL_MOVEMENT',
                'name' => 'Báo cáo Xuất nhập Vật liệu',
                'description' => 'Báo cáo chi tiết xuất nhập kho vật liệu',
                'category' => 'inventory',
                'type' => 'detail',
                'data_sources' => json_encode(['inventory_movements', 'building_materials']),
                'columns' => json_encode([
                    ['name' => 'material_name', 'label' => 'Tên vật liệu', 'type' => 'text'],
                    ['name' => 'movement_type', 'label' => 'Loại xuất nhập', 'type' => 'text'],
                    ['name' => 'quantity', 'label' => 'Số lượng', 'type' => 'number'],
                    ['name' => 'unit_price', 'label' => 'Đơn giá', 'type' => 'currency'],
                    ['name' => 'total_value', 'label' => 'Thành tiền', 'type' => 'currency'],
                ]),
                'output_format' => 'export',
            ],
            [
                'code' => 'SUPPLIER_PERFORMANCE',
                'name' => 'Hiệu quả Nhà cung cấp',
                'description' => 'Báo cáo đánh giá hiệu quả nhà cung cấp',
                'category' => 'supplier',
                'type' => 'summary',
                'data_sources' => json_encode(['material_suppliers', 'purchase_orders']),
                'columns' => json_encode([
                    ['name' => 'supplier_name', 'label' => 'Tên nhà cung cấp', 'type' => 'text'],
                    ['name' => 'total_orders', 'label' => 'Số đơn hàng', 'type' => 'number'],
                    ['name' => 'total_value', 'label' => 'Tổng giá trị', 'type' => 'currency'],
                    ['name' => 'on_time_delivery', 'label' => 'Giao hàng đúng hạn (%)', 'type' => 'percentage'],
                ]),
                'output_format' => 'table',
            ],
        ];

        $createdTemplates = collect();
        
        foreach ($templates as $templateData) {
            $template = ReportTemplate::create(array_merge($templateData, [
                'store_id' => $store->id,
                'is_active' => true,
                'is_system' => true, // Mark as system templates
                'is_public' => rand(0, 1) === 1,
                'cache_enabled' => true,
                'cache_duration' => rand(15, 60),
                'auto_refresh' => rand(0, 1) === 1,
                'refresh_interval' => rand(300, 1800), // 5-30 minutes
                'created_by' => $createdBy?->id,
                'metadata' => json_encode([
                    'created_via' => 'seeder',
                    'version' => '1.0',
                    'tags' => ['system', 'default']
                ])
            ]));
            
            $createdTemplates->push($template);
            
            $this->logSeedingProgress('report_template_created', [
                'template_id' => $template->id,
                'template_code' => $template->code,
                'store_id' => $store->id
            ]);
        }

        return $createdTemplates;
    }

    private function createReportInstances(ReportTemplate $template, int $count): void
    {
        $this->logSeedingProgress('creating_report_instances', [
            'template_id' => $template->id,
            'template_code' => $template->code,
            'instance_count' => $count
        ]);

        $createdBy = User::whereHas('stores', function($query) use ($template) {
            $query->where('store_id', $template->store_id);
        })->first();

        for ($i = 0; $i < $count; $i++) {
            $generatedAt = Carbon::now()->subDays(rand(1, 90));

            $status = $this->getRandomInstanceStatus();
            $totalRecords = rand(10, 1000);
            $totalAmount = rand(1000000, 50000000);
            $generationTime = rand(5, 60);

            $instance = ReportInstance::create([
                'store_id' => $template->store_id,
                'template_id' => $template->id,
                'instance_number' => 'RPT-' . $generatedAt->format('Ymd') . '-' . rand(1000, 9999),
                'title' => $template->name . ' - ' . $generatedAt->format('d/m/Y'),
                'report_date' => $generatedAt->format('Y-m-d'),
                'period_start' => $generatedAt->format('Y-m-d'),
                'period_end' => $generatedAt->format('Y-m-d'),
                'parameters' => json_encode($this->getInstanceParameters($template)),
                'status' => $status,
                'generated_at' => $generatedAt,
                'completed_at' => $status === 'completed' ? $generatedAt->copy()->addMinutes(rand(1, 10)) : null,
                'generation_time' => $status === 'completed' ? $generationTime : null,
                'total_records' => $status === 'completed' ? $totalRecords : 0,
                'total_amount' => $status === 'completed' ? $totalAmount : 0,
                'export_files' => $status === 'completed' ? json_encode([
                    'pdf' => $this->generateFilePath($template, $generatedAt),
                    'excel' => str_replace('.pdf', '.xlsx', $this->generateFilePath($template, $generatedAt)),
                ]) : null,
                'has_pdf' => $status === 'completed',
                'has_excel' => $status === 'completed' && rand(0, 1) === 1,
                'has_csv' => $status === 'completed' && rand(0, 1) === 1,
                'summary_data' => $status === 'completed' ? json_encode([
                    'total_records' => $totalRecords,
                    'total_amount' => $totalAmount,
                    'generation_time' => $generationTime,
                    'categories' => $this->getInstanceSummaryCategories($template),
                ]) : null,
                'error_message' => $status === 'failed' ? $this->getRandomErrorMessage() : null,
                'generated_by' => $createdBy?->id,
            ]);

            if ($i === 0) { // Log only first instance creation per template
                $this->logSeedingProgress('report_instances_created', [
                    'template_id' => $template->id,
                    'instance_count' => $count,
                    'first_instance_id' => $instance->id
                ]);
            }
        }
    }

    private function createReportSchedule(ReportTemplate $template): void
    {
        $this->logSeedingProgress('creating_report_schedule', [
            'template_id' => $template->id,
            'template_code' => $template->code
        ]);

        $createdBy = User::whereHas('stores', function($query) use ($template) {
            $query->where('store_id', $template->store_id);
        })->first();

        $schedule = ReportSchedule::create([
            'store_id' => $template->store_id,
            'template_id' => $template->id,
            'name' => 'Lịch tự động - ' . $template->name,
            'frequency' => $this->getRandomFrequency(),
            'schedule_config' => json_encode([
                'run_time' => $this->getRandomScheduleTime(),
                'timezone' => 'Asia/Ho_Chi_Minh',
            ]),
            'run_time' => $this->getRandomScheduleTime(),
            'parameters' => json_encode($this->getScheduleParameters($template)),
            'email_recipients' => json_encode($this->getRandomRecipients()),
            'auto_email' => true,
            'email_formats' => json_encode(['pdf']),
            'export_formats' => json_encode(['pdf']),
            'is_active' => true,
            'next_run_at' => $this->getNextRunTime(),
            'last_run_at' => Carbon::now()->subDays(rand(1, 7)),
            'run_count' => rand(5, 50),
            'success_count' => rand(4, 45),
            'failure_count' => rand(0, 5),
            'retention_days' => 30,
            'max_instances' => 100,
            'created_by' => $createdBy?->id,
            'metadata' => json_encode([
                'auto_delete_after_days' => 30,
                'email_notification' => true,
                'created_via' => 'seeder'
            ]),
        ]);

        $this->logSeedingProgress('report_schedule_created', [
            'schedule_id' => $schedule->id,
            'template_id' => $template->id,
            'frequency' => $schedule->frequency
        ]);
    }

    private function createReportDashboard(Store $store): void
    {
        $this->logSeedingProgress('creating_report_dashboard', [
            'store_id' => $store->id,
            'store_name' => $store->name
        ]);

        $createdBy = User::whereHas('stores', function($query) use ($store) {
            $query->where('store_id', $store->id);
        })->first();

        $dashboard = ReportDashboard::create([
            'store_id' => $store->id,
            'code' => 'OVERVIEW_DASHBOARD',
            'name' => 'Dashboard Tổng quan',
            'description' => 'Dashboard hiển thị các báo cáo chính của cửa hàng',
            'layout_config' => json_encode([
                'grid_size' => [12, 8],
                'widget_spacing' => 10,
                'responsive' => true,
            ]),
            'widgets' => json_encode([
                [
                    'id' => 'sales_chart',
                    'type' => 'chart',
                    'title' => 'Biểu đồ Doanh thu',
                    'position' => ['x' => 0, 'y' => 0],
                    'size' => ['w' => 6, 'h' => 4],
                    'config' => ['chart_type' => 'line', 'data_source' => 'sales_orders']
                ],
                [
                    'id' => 'inventory_summary',
                    'type' => 'summary',
                    'title' => 'Tổng quan Tồn kho',
                    'position' => ['x' => 6, 'y' => 0],
                    'size' => ['w' => 6, 'h' => 4],
                    'config' => ['data_source' => 'material_inventory']
                ],
                [
                    'id' => 'customer_stats',
                    'type' => 'stats',
                    'title' => 'Thống kê Khách hàng',
                    'position' => ['x' => 0, 'y' => 4],
                    'size' => ['w' => 4, 'h' => 3],
                    'config' => ['data_source' => 'customers']
                ],
                [
                    'id' => 'financial_summary',
                    'type' => 'financial',
                    'title' => 'Tổng quan Tài chính',
                    'position' => ['x' => 4, 'y' => 4],
                    'size' => ['w' => 8, 'h' => 3],
                    'config' => ['data_source' => 'cash_transactions']
                ],
            ]),
            'filters' => json_encode([
                'date_range' => ['type' => 'relative', 'value' => 'last_30_days'],
                'store_filter' => true,
            ]),
            'refresh_interval' => 300, // 5 minutes
            'permissions' => json_encode([
                'roles' => ['admin', 'manager'],
                'users' => []
            ]),
            'is_active' => true,
            'is_default' => true,
            'created_by' => $createdBy?->id,
            'metadata' => json_encode([
                'theme' => 'light',
                'created_via' => 'seeder',
                'version' => '1.0',
                'auto_layout' => true
            ]),
        ]);

        $this->logSeedingProgress('report_dashboard_created', [
            'dashboard_id' => $dashboard->id,
            'store_id' => $store->id,
            'widget_count' => count(json_decode($dashboard->widgets, true))
        ]);
    }

    private function getInstanceParameters(ReportTemplate $template): array
    {
        return [
            'date_range' => [
                'start' => Carbon::now()->subDays(30)->format('Y-m-d'),
                'end' => Carbon::now()->format('Y-m-d'),
            ],
            'filters' => [
                'status' => 'active',
                'category' => 'all',
            ],
            'grouping' => 'daily',
            'currency' => 'VND',
        ];
    }

    private function getRandomInstanceStatus(): string
    {
        $statuses = ['completed', 'failed', 'generating'];
        $weights = [80, 15, 5]; // 80% completed, 15% failed, 5% generating

        $random = rand(1, 100);
        if ($random <= 80) {
            return 'completed';
        } elseif ($random <= 95) {
            return 'failed';
        } else {
            return 'generating';
        }
    }

    private function generateFilePath(ReportTemplate $template, Carbon $generatedAt): string
    {
        $extension = $template->output_format === 'export' ? 'pdf' : 'xlsx';
        return "reports/{$template->code}/" . $generatedAt->format('Y/m') . "/{$template->code}_{$generatedAt->format('Ymd_His')}.{$extension}";
    }

    private function getRandomFrequency(): string
    {
        $frequencies = ['daily', 'weekly', 'monthly', 'quarterly'];
        return $frequencies[array_rand($frequencies)];
    }

    private function getRandomScheduleTime(): string
    {
        $hours = str_pad(rand(6, 23), 2, '0', STR_PAD_LEFT);
        $minutes = str_pad(rand(0, 59), 2, '0', STR_PAD_LEFT);
        return "{$hours}:{$minutes}";
    }

    private function getScheduleParameters(ReportTemplate $template): array
    {
        return [
            'auto_date_range' => true,
            'include_weekends' => false,
            'format' => 'pdf',
            'email_subject' => "Báo cáo tự động: {$template->name}",
        ];
    }

    private function getRandomRecipients(): array
    {
        return [
            'admin@demo.boxpos.vn',
            'manager@demo.boxpos.vn',
            'accounting@demo.boxpos.vn',
        ];
    }

    private function getNextRunTime(): Carbon
    {
        return Carbon::now()->addDays(rand(1, 7))->setTime(rand(6, 23), rand(0, 59));
    }

    private function getInstanceSummaryCategories(ReportTemplate $template): array
    {
        return match($template->category) {
            'sales' => [
                'orders' => rand(10, 100),
                'revenue' => rand(1000000, 10000000),
                'customers' => rand(5, 50)
            ],
            'inventory' => [
                'materials' => rand(20, 200),
                'total_value' => rand(5000000, 50000000),
                'low_stock_items' => rand(0, 10)
            ],
            'customer' => [
                'total_customers' => rand(50, 500),
                'new_customers' => rand(5, 50),
                'returning_customers' => rand(20, 200)
            ],
            'financial' => [
                'income' => rand(2000000, 20000000),
                'expenses' => rand(1000000, 15000000),
                'profit' => rand(500000, 5000000)
            ],
            'employee' => [
                'total_employees' => rand(5, 50),
                'total_hours' => rand(100, 1000),
                'total_commission' => rand(100000, 1000000)
            ],
            default => [
                'total_records' => rand(10, 1000),
                'processed' => rand(8, 950),
                'errors' => rand(0, 50)
            ]
        };
    }

    private function getRandomErrorMessage(): string
    {
        $errors = [
            'Không thể kết nối đến cơ sở dữ liệu',
            'Dữ liệu không đủ để tạo báo cáo',
            'Lỗi xử lý tham số báo cáo',
            'Hết thời gian chờ khi tạo báo cáo',
            'Lỗi xuất file PDF',
            'Không đủ quyền truy cập dữ liệu',
            'Lỗi định dạng ngày tháng',
            'Dữ liệu bị thiếu hoặc không hợp lệ'
        ];

        return $errors[array_rand($errors)];
    }
}
