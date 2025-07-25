<?php

namespace Packages\Warehouse\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Packages\MaterialCatalog\Models\BuildingMaterial;
use Packages\User\Models\User;

class InventoryDisposalItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'disposal_id',
        'material_id',
        'material_code',
        'material_name',
        'material_description',
        'quantity',
        'unit',
        'unit_cost',
        'total_cost',
        'disposal_reason',
        'disposal_reason_detail',
        'condition',
        'batch_number',
        'serial_numbers',
        'manufacture_date',
        'expiry_date',
        'location',
        'warehouse_zone',
        'recovery_value',
        'recovery_notes',
        'status',
        'inventory_adjusted',
        'processed_at',
        'processed_by',
        'notes',
        'metadata',
    ];

    protected $casts = [
        'manufacture_date' => 'date',
        'expiry_date' => 'date',
        'processed_at' => 'datetime',
        'quantity' => 'decimal:3',
        'unit_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'recovery_value' => 'decimal:2',
        'inventory_adjusted' => 'boolean',
        'serial_numbers' => 'array',
        'metadata' => 'array',
    ];

    // Relationships
    public function disposal(): BelongsTo
    {
        return $this->belongsTo(InventoryDisposal::class, 'disposal_id');
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(BuildingMaterial::class, 'material_id');
    }

    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    // Scopes
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByDisposalReason($query, string $reason)
    {
        return $query->where('disposal_reason', $reason);
    }

    public function scopeByCondition($query, string $condition)
    {
        return $query->where('condition', $condition);
    }

    public function scopePendingProcessing($query)
    {
        return $query->whereIn('status', ['pending', 'approved']);
    }

    public function scopeProcessed($query)
    {
        return $query->whereIn('status', ['processed', 'completed']);
    }

    // Helper methods
    public function calculateTotalCost(): void
    {
        $this->total_cost = $this->quantity * $this->unit_cost;
    }

    public function markAsProcessed(int $userId): void
    {
        $this->status = 'processed';
        $this->processed_by = $userId;
        $this->processed_at = now();
        $this->save();
    }

    public function canBeProcessed(): bool
    {
        return in_array($this->status, ['pending', 'approved']) && 
               $this->disposal->canBeProcessed();
    }

    public function canAdjustInventory(): bool
    {
        return !$this->inventory_adjusted && 
               $this->status === 'processed';
    }

    public function getNetLossAttribute(): float
    {
        return $this->total_cost - $this->recovery_value;
    }

    public function getRecoveryPercentageAttribute(): float
    {
        return $this->total_cost > 0 ? 
            ($this->recovery_value / $this->total_cost) * 100 : 0;
    }

    public function getDisposalReasonDisplayAttribute(): string
    {
        return match($this->disposal_reason) {
            'damaged' => 'Hư hỏng',
            'expired' => 'Hết hạn',
            'obsolete' => 'Lỗi thời',
            'defective' => 'Lỗi sản xuất',
            'contaminated' => 'Bị nhiễm',
            'theft' => 'Mất trộm',
            'loss' => 'Thất thoát',
            'quality_issue' => 'Vấn đề chất lượng',
            'recall' => 'Thu hồi',
            'other' => 'Khác',
            default => $this->disposal_reason
        };
    }

    public function getConditionDisplayAttribute(): string
    {
        return match($this->condition) {
            'damaged' => 'Hư hỏng',
            'expired' => 'Hết hạn',
            'good' => 'Tốt',
            'fair' => 'Khá',
            'poor' => 'Kém',
            default => $this->condition
        };
    }

    public function getStatusDisplayAttribute(): string
    {
        return match($this->status) {
            'pending' => 'Chờ xử lý',
            'approved' => 'Đã duyệt',
            'processed' => 'Đã xử lý',
            'completed' => 'Hoàn thành',
            default => $this->status
        };
    }

    public function getFormattedQuantityAttribute(): string
    {
        return number_format($this->quantity, 3) . ' ' . $this->unit;
    }

    public function getFormattedTotalCostAttribute(): string
    {
        return number_format($this->total_cost, 0, ',', '.') . ' VND';
    }

    public function getFormattedRecoveryValueAttribute(): string
    {
        return number_format($this->recovery_value, 0, ',', '.') . ' VND';
    }

    public function getFormattedNetLossAttribute(): string
    {
        return number_format($this->net_loss, 0, ',', '.') . ' VND';
    }
}
