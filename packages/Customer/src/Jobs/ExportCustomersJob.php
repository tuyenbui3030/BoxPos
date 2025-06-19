<?php

namespace Packages\Customer\Jobs;

use Packages\Customer\Models\Customer;
use Packages\Log\Traits\Loggable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Export Customers Job
 * 
 * Handles exporting customer data to various formats.
 */
class ExportCustomersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Loggable; // ⚠️ MANDATORY: Use Loggable trait

    /**
     * The export format
     *
     * @var string
     */
    protected string $format;

    /**
     * The file path for export
     *
     * @var string
     */
    protected string $filePath;

    /**
     * Create a new job instance.
     *
     * @param string $format
     * @param string $filePath
     */
    public function __construct(string $format = 'csv', string $filePath = null)
    {
        $this->format = $format;
        $this->filePath = $filePath ?? storage_path('exports/customers_' . now()->format('Y-m-d_H-i-s') . '.csv');
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        $startTime = microtime(true);

        // ⚠️ MANDATORY: Log job start
        $this->logActivity('customer_export_job_started', [
            'format' => $this->format,
            'file_path' => $this->filePath,
            'job_id' => $this->job?->getJobId(),
            'queue' => $this->queue,
        ]);

        try {
            $customers = Customer::all();

            // Create export directory if it doesn't exist
            $directory = dirname($this->filePath);
            if (!is_dir($directory)) {
                mkdir($directory, 0755, true);
            }

            // ⚠️ MANDATORY: Log process step
            $this->logProcessStep('customer_export', 'data_retrieval_completed', [
                'format' => $this->format,
                'records_count' => $customers->count(),
                'memory_usage' => memory_get_usage(true),
            ]);

            // Export based on format
            switch ($this->format) {
                case 'csv':
                    $this->exportToCsv($customers);
                    break;
                case 'json':
                    $this->exportToJson($customers);
                    break;
                default:
                    throw new \InvalidArgumentException("Unsupported export format: {$this->format}");
            }

            // ⚠️ MANDATORY: Log successful completion
            $this->logActivity('customer_export_job_completed', [
                'format' => $this->format,
                'file_path' => $this->filePath,
                'records_count' => $customers->count(),
                'file_size' => file_exists($this->filePath) ? filesize($this->filePath) : 0,
                'job_id' => $this->job?->getJobId(),
            ]);

            // ⚠️ MANDATORY: Log operation performance
            $this->logOperationPerformance('customer_export_job', $startTime, [
                'format' => $this->format,
                'records_count' => $customers->count(),
                'file_size' => file_exists($this->filePath) ? filesize($this->filePath) : 0,
                'memory_peak' => memory_get_peak_usage(true),
            ]);

        } catch (\Exception $e) {
            // ⚠️ MANDATORY: Log error with context
            $this->logError($e, [
                'job' => 'ExportCustomersJob',
                'format' => $this->format,
                'file_path' => $this->filePath,
                'job_id' => $this->job?->getJobId(),
                'memory_usage' => memory_get_usage(true),
            ]);

            throw $e;
        }
    }

    /**
     * Export customers to CSV format
     *
     * @param \Illuminate\Database\Eloquent\Collection $customers
     * @return void
     */
    protected function exportToCsv($customers): void
    {
        $file = fopen($this->filePath, 'w');

        // Write header
        fputcsv($file, [
            'ID', 'Name', 'Email', 'Phone', 'Address', 'City', 
            'State', 'Postal Code', 'Country', 'Status', 'Created At'
        ]);

        // Write data
        foreach ($customers as $customer) {
            fputcsv($file, [
                $customer->id,
                $customer->name,
                $customer->email,
                $customer->phone,
                $customer->address,
                $customer->city,
                $customer->state,
                $customer->postal_code,
                $customer->country,
                $customer->status,
                $customer->created_at?->toDateTimeString(),
            ]);
        }

        fclose($file);
    }

    /**
     * Export customers to JSON format
     *
     * @param \Illuminate\Database\Eloquent\Collection $customers
     * @return void
     */
    protected function exportToJson($customers): void
    {
        $data = [
            'exported_at' => now()->toISOString(),
            'total_records' => $customers->count(),
            'customers' => $customers->toArray(),
        ];

        file_put_contents($this->filePath, json_encode($data, JSON_PRETTY_PRINT));
    }
}
