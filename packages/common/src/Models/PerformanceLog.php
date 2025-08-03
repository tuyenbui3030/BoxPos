<?php

namespace Packages\Common\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PerformanceLog extends Model
{
    protected $fillable = [
        'operation',
        'duration',
        'memory_usage',
        'peak_memory',
        'context',
        'user_id',
        'store_id',
        'created_at',
    ];

    protected $casts = [
        'duration' => 'float',
        'memory_usage' => 'integer',
        'peak_memory' => 'integer',
        'context' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user associated with the performance log
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    /**
     * Get the store where the operation occurred
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Store::class);
    }

    /**
     * Scope to filter by operation
     */
    public function scopeByOperation($query, string $operation)
    {
        return $query->where('operation', $operation);
    }

    /**
     * Scope to filter by user
     */
    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to filter by store
     */
    public function scopeByStore($query, int $storeId)
    {
        return $query->where('store_id', $storeId);
    }

    /**
     * Scope to filter by date range
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Scope to filter slow operations
     */
    public function scopeSlowOperations($query, float $threshold = 1.0)
    {
        return $query->where('duration', '>', $threshold);
    }

    /**
     * Get formatted duration
     */
    public function getFormattedDurationAttribute(): string
    {
        if ($this->duration < 1) {
            return round($this->duration * 1000, 2) . 'ms';
        }
        
        return round($this->duration, 2) . 's';
    }

    /**
     * Get formatted memory usage
     */
    public function getFormattedMemoryUsageAttribute(): string
    {
        return $this->formatBytes($this->memory_usage);
    }

    /**
     * Get formatted peak memory
     */
    public function getFormattedPeakMemoryAttribute(): string
    {
        return $this->formatBytes($this->peak_memory);
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);

        return round($bytes, 2) . ' ' . $units[$pow];
    }
}