<?php

namespace Packages\User\Jobs;

use Packages\User\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Export Users Job
 * 
 * Handles exporting user data to various formats.
 */
class ExportUsersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

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
     * Include sensitive data flag
     *
     * @var bool
     */
    protected bool $includeSensitive;

    /**
     * Create a new job instance.
     *
     * @param string $format
     * @param string|null $filePath
     * @param bool $includeSensitive
     */
    public function __construct(string $format = 'csv', string $filePath = null, bool $includeSensitive = false)
    {
        $this->format = $format;
        $this->filePath = $filePath ?? storage_path('exports/users_' . now()->format('Y-m-d_H-i-s') . '.csv');
        $this->includeSensitive = $includeSensitive;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        try {
            Log::info('Starting user export', [
                'format' => $this->format,
                'file_path' => $this->filePath,
                'include_sensitive' => $this->includeSensitive,
            ]);

            $users = User::all();

            // Create export directory if it doesn't exist
            $directory = dirname($this->filePath);
            if (!is_dir($directory)) {
                mkdir($directory, 0755, true);
            }

            // Export based on format
            switch ($this->format) {
                case 'csv':
                    $this->exportToCsv($users);
                    break;
                case 'json':
                    $this->exportToJson($users);
                    break;
                default:
                    throw new \InvalidArgumentException("Unsupported export format: {$this->format}");
            }

            Log::info('User export completed successfully', [
                'format' => $this->format,
                'file_path' => $this->filePath,
                'records_count' => $users->count(),
            ]);

        } catch (\Exception $e) {
            Log::error('User export failed', [
                'format' => $this->format,
                'file_path' => $this->filePath,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Export users to CSV format
     *
     * @param \Illuminate\Database\Eloquent\Collection $users
     * @return void
     */
    protected function exportToCsv($users): void
    {
        $file = fopen($this->filePath, 'w');

        // Write header
        $headers = ['ID', 'Name', 'Email', 'Role', 'Active', 'Created At'];
        if ($this->includeSensitive) {
            $headers[] = 'Email Verified';
            $headers[] = 'Last Login';
        }
        fputcsv($file, $headers);

        // Write data
        foreach ($users as $user) {
            $row = [
                $user->id,
                $user->name,
                $user->email,
                $user->role ?? 'user',
                $user->is_active ? 'Yes' : 'No',
                $user->created_at?->toDateTimeString(),
            ];

            if ($this->includeSensitive) {
                $row[] = $user->email_verified_at ? 'Yes' : 'No';
                $row[] = $user->last_login_at?->toDateTimeString() ?? 'Never';
            }

            fputcsv($file, $row);
        }

        fclose($file);
    }

    /**
     * Export users to JSON format
     *
     * @param \Illuminate\Database\Eloquent\Collection $users
     * @return void
     */
    protected function exportToJson($users): void
    {
        $data = [
            'exported_at' => now()->toISOString(),
            'total_records' => $users->count(),
            'include_sensitive' => $this->includeSensitive,
            'users' => $users->map(function ($user) {
                $userData = [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role ?? 'user',
                    'is_active' => $user->is_active,
                    'created_at' => $user->created_at?->toISOString(),
                ];

                if ($this->includeSensitive) {
                    $userData['email_verified_at'] = $user->email_verified_at?->toISOString();
                    $userData['last_login_at'] = $user->last_login_at?->toISOString();
                }

                return $userData;
            })->toArray(),
        ];

        file_put_contents($this->filePath, json_encode($data, JSON_PRETTY_PRINT));
    }
}
