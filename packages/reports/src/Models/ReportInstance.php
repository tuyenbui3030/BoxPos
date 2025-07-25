<?php

namespace Packages\Reports\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Packages\Store\Models\Store;
use Packages\Tenant\Traits\HasTenantScope;
use Packages\User\Models\User;

class ReportInstance extends Model
{
    use HasFactory, HasTenantScope;

    protected $fillable = [
        'store_id',
        'template_id',
        'instance_number',
        'title',
        'report_date',
        'period_start',
        'period_end',
        'filters_applied',
        'parameters',
        'status',
        'generated_at',
        'completed_at',
        'generation_time',
        'error_message',
        'summary_data',
        'report_data',
        'total_records',
        'total_amount',
        'export_files',
        'has_pdf',
        'has_excel',
        'has_csv',
        'is_shared',
        'shared_with',
        'share_token',
        'expires_at',
        'email_sent',
        'email_recipients',
        'email_sent_at',
        'notes',
        'metadata',
        'generated_by',
    ];

    protected $casts = [
        'report_date' => 'date',
        'period_start' => 'date',
        'period_end' => 'date',
        'generated_at' => 'datetime',
        'completed_at' => 'datetime',
        'expires_at' => 'datetime',
        'email_sent_at' => 'datetime',
        'filters_applied' => 'array',
        'parameters' => 'array',
        'summary_data' => 'array',
        'export_files' => 'array',
        'shared_with' => 'array',
        'email_recipients' => 'array',
        'metadata' => 'array',
        'total_amount' => 'decimal:2',
        'has_pdf' => 'boolean',
        'has_excel' => 'boolean',
        'has_csv' => 'boolean',
        'is_shared' => 'boolean',
        'email_sent' => 'boolean',
    ];

    // Relationships
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(ReportTemplate::class, 'template_id');
    }

    public function generator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    // Scopes
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('report_date', [$startDate, $endDate]);
    }

    public function scopeShared($query)
    {
        return $query->where('is_shared', true);
    }

    public function scopeNotExpired($query)
    {
        return $query->where(function($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', now());
        });
    }

    // Helper methods
    public static function generateInstanceNumber(int $storeId): string
    {
        $date = now()->format('Ymd');
        $lastInstance = static::where('store_id', $storeId)
            ->where('instance_number', 'like', "RPT{$date}%")
            ->orderBy('id', 'desc')
            ->first();
        
        $sequence = $lastInstance ? (intval(substr($lastInstance->instance_number, -4)) + 1) : 1;
        return "RPT{$date}" . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    public function markAsGenerating(): void
    {
        $this->update([
            'status' => 'generating',
            'generated_at' => now(),
        ]);
    }

    public function markAsCompleted(array $data = []): void
    {
        $this->update(array_merge([
            'status' => 'completed',
            'completed_at' => now(),
            'generation_time' => $this->generated_at ? now()->diffInSeconds($this->generated_at) : null,
        ], $data));
    }

    public function markAsFailed(string $error): void
    {
        $this->update([
            'status' => 'failed',
            'completed_at' => now(),
            'error_message' => $error,
            'generation_time' => $this->generated_at ? now()->diffInSeconds($this->generated_at) : null,
        ]);
    }

    public function generateShareToken(): string
    {
        $token = \Str::random(32);
        $this->update(['share_token' => $token]);
        return $token;
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function canBeShared(): bool
    {
        return $this->isCompleted() && !$this->isExpired();
    }

    public function canBeDownloaded(): bool
    {
        return $this->isCompleted() && !$this->isExpired();
    }

    public function getStatusDisplayAttribute(): string
    {
        return match($this->status) {
            'pending' => 'Chờ xử lý',
            'generating' => 'Đang tạo',
            'completed' => 'Hoàn thành',
            'failed' => 'Thất bại',
            'cancelled' => 'Đã hủy',
            default => $this->status
        };
    }

    public function getFormattedGenerationTimeAttribute(): string
    {
        if (!$this->generation_time) {
            return 'N/A';
        }

        if ($this->generation_time < 60) {
            return $this->generation_time . ' giây';
        }

        $minutes = floor($this->generation_time / 60);
        $seconds = $this->generation_time % 60;
        return $minutes . ' phút ' . $seconds . ' giây';
    }

    public function getPeriodDisplayAttribute(): string
    {
        return $this->period_start->format('d/m/Y') . ' - ' . $this->period_end->format('d/m/Y');
    }

    public function getFormattedTotalAmountAttribute(): string
    {
        return number_format($this->total_amount, 0, ',', '.') . ' VND';
    }

    public function hasExportFiles(): bool
    {
        return $this->has_pdf || $this->has_excel || $this->has_csv;
    }

    public function getAvailableFormatsAttribute(): array
    {
        $formats = [];
        if ($this->has_pdf) $formats[] = 'PDF';
        if ($this->has_excel) $formats[] = 'Excel';
        if ($this->has_csv) $formats[] = 'CSV';
        return $formats;
    }
}
