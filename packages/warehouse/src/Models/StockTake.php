<?php

namespace Packages\Warehouse\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Packages\Store\Models\Store;
use Packages\Tenant\Traits\HasTenantScope;
use Packages\User\Models\User;

class StockTake extends Model
{
    use HasFactory, HasTenantScope;

    protected $fillable = [
        'store_id',
        'stock_take_number',
        'name',
        'description',
        'scheduled_date',
        'start_date',
        'end_date',
        'type',
        'status',
        'material_categories',
        'material_filters',
        'location_filter',
        'include_zero_stock',
        'include_negative_stock',
        'total_materials',
        'counted_materials',
        'variance_count',
        'total_variance_value',
        'assigned_users',
        'supervisor_id',
        'requires_approval',
        'is_approved',
        'approved_by',
        'approved_at',
        'approval_notes',
        'auto_adjust',
        'adjustments_posted',
        'adjustments_posted_at',
        'adjustments_posted_by',
        'notes',
        'metadata',
        'created_by',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'start_date' => 'date',
        'end_date' => 'date',
        'approved_at' => 'datetime',
        'adjustments_posted_at' => 'datetime',
        'total_variance_value' => 'decimal:2',
        'include_zero_stock' => 'boolean',
        'include_negative_stock' => 'boolean',
        'requires_approval' => 'boolean',
        'is_approved' => 'boolean',
        'auto_adjust' => 'boolean',
        'adjustments_posted' => 'boolean',
        'material_categories' => 'array',
        'material_filters' => 'array',
        'assigned_users' => 'array',
        'metadata' => 'array',
    ];

    // Relationships
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function adjustmentsPoster(): BelongsTo
    {
        return $this->belongsTo(User::class, 'adjustments_posted_by');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(StockTakeItem::class);
    }

    // Scopes
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeScheduledBetween($query, $startDate, $endDate)
    {
        return $query->whereBetween('scheduled_date', [$startDate, $endDate]);
    }

    public function scopePendingApproval($query)
    {
        return $query->where('requires_approval', true)
                    ->where('is_approved', false)
                    ->where('status', 'completed');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    // Helper methods
    public static function generateStockTakeNumber(int $storeId): string
    {
        $year = date('Y');
        $month = date('m');
        $lastStockTake = static::where('store_id', $storeId)
            ->where('stock_take_number', 'like', "ST{$year}{$month}%")
            ->orderBy('id', 'desc')
            ->first();
        
        $sequence = $lastStockTake ? (intval(substr($lastStockTake->stock_take_number, -4)) + 1) : 1;
        return "ST{$year}{$month}" . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    public function updateProgress(): void
    {
        $this->counted_materials = $this->items()->whereNotNull('physical_quantity')->count();
        $this->variance_count = $this->items()->where('variance_quantity', '!=', 0)->count();
        $this->total_variance_value = $this->items()->sum('variance_value');
        $this->save();
    }

    public function getProgressPercentageAttribute(): float
    {
        return $this->total_materials > 0 ? ($this->counted_materials / $this->total_materials) * 100 : 0;
    }

    public function canStart(): bool
    {
        return $this->status === 'planned' && $this->scheduled_date->isToday();
    }

    public function canComplete(): bool
    {
        return $this->status === 'in_progress' && $this->progress_percentage >= 100;
    }

    public function canBeApproved(): bool
    {
        return $this->requires_approval && 
               !$this->is_approved && 
               $this->status === 'completed';
    }

    public function canPostAdjustments(): bool
    {
        return $this->status === 'completed' && 
               (!$this->requires_approval || $this->is_approved) && 
               !$this->adjustments_posted &&
               $this->variance_count > 0;
    }

    public function isFullStockTake(): bool
    {
        return $this->type === 'full';
    }

    public function isPartialStockTake(): bool
    {
        return $this->type === 'partial';
    }

    public function isCycleCount(): bool
    {
        return $this->type === 'cycle';
    }

    public function isSpotCheck(): bool
    {
        return $this->type === 'spot';
    }

    public function hasVariances(): bool
    {
        return $this->variance_count > 0;
    }

    public function getStatusDisplayAttribute(): string
    {
        return match($this->status) {
            'planned' => 'Đã lên kế hoạch',
            'in_progress' => 'Đang thực hiện',
            'completed' => 'Hoàn thành',
            'cancelled' => 'Đã hủy',
            'approved' => 'Đã duyệt',
            default => $this->status
        };
    }

    public function getTypeDisplayAttribute(): string
    {
        return match($this->type) {
            'full' => 'Kiểm kê toàn bộ',
            'partial' => 'Kiểm kê một phần',
            'cycle' => 'Kiểm kê chu kỳ',
            'spot' => 'Kiểm tra đột xuất',
            default => $this->type
        };
    }
}
