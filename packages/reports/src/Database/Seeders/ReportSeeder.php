<?php

namespace Packages\Reports\Database\Seeders;

use Illuminate\Database\Seeder;
use Packages\Reports\Models\ReportTemplate;
use Packages\Reports\Models\ReportInstance;
use Packages\Reports\Models\ReportSchedule;
use Packages\Reports\Models\ReportDashboard;
use Packages\Store\Models\Store;
use Packages\User\Models\User;
use Carbon\Carbon;

class ReportSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('📊 Seeding Reports...');

        $stores = Store::all();
        
        foreach ($stores as $store) {
            // Create report templates
            $templates = $this->createReportTemplates($store);
            
            // Create report instances for each template
            foreach ($templates as $template) {
                $this->createReportInstances($template, rand(5, 15));
            }
            
            // Create report schedules
            foreach ($templates->take(3) as $template) {
                $this->createReportSchedule($template);
            }
            
            // Create report dashboard
            $this->createReportDashboard($store);
        }

        $this->command->info('✅ Reports seeded successfully!');
    }

    private function createReportTemplates(Store $store): \Illuminate\Support\Collection
    {
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
        ];

        $createdTemplates = collect();
        
        foreach ($templates as $templateData) {
            $template = ReportTemplate::create(array_merge($templateData, [
                'store_id' => $store->id,
                'is_active' => true,
                'is_public' => rand(0, 1) === 1,
                'cache_enabled' => true,
                'cache_duration' => rand(15, 60),
                'created_by' => $createdBy?->id,
            ]));
            
            $createdTemplates->push($template);
        }

        return $createdTemplates;
    }

    private function createReportInstances(ReportTemplate $template, int $count): void
    {
        $createdBy = User::whereHas('stores', function($query) use ($template) {
            $query->where('store_id', $template->store_id);
        })->first();

        for ($i = 0; $i < $count; $i++) {
            $generatedAt = Carbon::now()->subDays(rand(1, 90));

            ReportInstance::create([
                'store_id' => $template->store_id,
                'template_id' => $template->id,
                'instance_number' => 'RPT-' . $generatedAt->format('Ymd') . '-' . rand(1000, 9999),
                'title' => $template->name . ' - ' . $generatedAt->format('d/m/Y'),
                'report_date' => $generatedAt->format('Y-m-d'),
                'period_start' => $generatedAt->format('Y-m-d'),
                'period_end' => $generatedAt->format('Y-m-d'),
                'parameters' => json_encode($this->getInstanceParameters($template)),
                'status' => $this->getRandomInstanceStatus(),
                'generated_at' => $generatedAt,
                'completed_at' => $generatedAt->copy()->addMinutes(rand(1, 10)),
                'generation_time' => rand(5, 60),
                'total_records' => rand(10, 1000),
                'total_amount' => rand(1000000, 50000000), // 1M-50M VND
                'export_files' => json_encode([
                    'pdf' => $this->generateFilePath($template, $generatedAt),
                    'excel' => str_replace('.pdf', '.xlsx', $this->generateFilePath($template, $generatedAt)),
                ]),
                'has_pdf' => true,
                'has_excel' => rand(0, 1) === 1,
                'has_csv' => rand(0, 1) === 1,
                'summary_data' => json_encode([
                    'total_records' => rand(10, 1000),
                    'total_amount' => rand(1000000, 50000000),
                    'generation_time' => rand(5, 60),
                ]),
                'generated_by' => $createdBy?->id,
            ]);
        }
    }

    private function createReportSchedule(ReportTemplate $template): void
    {
        $createdBy = User::whereHas('stores', function($query) use ($template) {
            $query->where('store_id', $template->store_id);
        })->first();

        ReportSchedule::create([
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
            'export_formats' => json_encode(['pdf']),
            'is_active' => true,
            'next_run_at' => $this->getNextRunTime(),
            'last_run_at' => Carbon::now()->subDays(rand(1, 7)),
            'created_by' => $createdBy?->id,
            'metadata' => json_encode([
                'auto_delete_after_days' => 30,
                'email_notification' => true,
            ]),
        ]);
    }

    private function createReportDashboard(Store $store): void
    {
        $createdBy = User::whereHas('stores', function($query) use ($store) {
            $query->where('store_id', $store->id);
        })->first();

        ReportDashboard::create([
            'store_id' => $store->id,
            'name' => 'Dashboard Tổng quan',
            'code' => 'MAIN_DASHBOARD',
            'description' => 'Dashboard hiển thị các báo cáo chính',
            'layout_config' => json_encode([
                'grid_size' => [12, 8],
                'widget_spacing' => 10,
            ]),
            'widgets' => json_encode([
                ['type' => 'sales_chart', 'position' => [0, 0], 'size' => [6, 4]],
                ['type' => 'inventory_summary', 'position' => [6, 0], 'size' => [6, 4]],
                ['type' => 'customer_stats', 'position' => [0, 4], 'size' => [4, 3]],
                ['type' => 'financial_summary', 'position' => [4, 4], 'size' => [8, 3]],
            ]),
            'auto_refresh' => true,
            'refresh_interval' => 300, // 5 minutes
            'is_active' => true,
            'is_default' => true,
            'created_by' => $createdBy?->id,
            'metadata' => json_encode([
                'theme' => 'light',
                'created_via' => 'seeder',
            ]),
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
}
