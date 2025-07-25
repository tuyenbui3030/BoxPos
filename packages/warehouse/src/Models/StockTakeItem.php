<?php

namespace Packages\Warehouse\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Packages\MaterialCatalog\Models\BuildingMaterial;
use Packages\User\Models\User;

class StockTakeItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'stock_take_id',
        'material_id',
        'material_code',
        'material_name',
        'system_quantity',
        'physical_quantity',
        'variance_quantity',
        'unit',
        'unit_cost',
        'system_value',
        'physical_value',
        'variance_value',
        'count_status',
        'count_attempts',
        'first_counted_at',
        'last_counted_at',
        'counted_by',
        'verified_by',
        'verified_at',
        'batch_number',
        'serial_numbers',
        'expiry_date',
        'location',
        'warehouse_zone',
        'bin_location',
        'variance_reason',
        'variance_notes',
        'requires_investigation',
        'investigation_completed',
        'investigation_notes',
        'adjustment_required',
        'adjustment_posted',
        'adjustment_posted_by',
        'adjustment_posted_at',
        'notes',
        'metadata',
    ];

    protected $casts = [
        'first_counted_at' => 'datetime',
        'last_counted_at' => 'datetime',
        'verified_at' => 'datetime',
        'adjustment_posted_at' => 'datetime',
        'expiry_date' => 'date',
        'system_quantity' => 'decimal:3',
        'physical_quantity' => 'decimal:3',
        'variance_quantity' => 'decimal:3',
        'unit_cost' => 'decimal:2',
        'system_value' => 'decimal:2',
        'physical_value' => 'decimal:2',
        'variance_value' => 'decimal:2',
        'requires_investigation' => 'boolean',
        'investigation_completed' => 'boolean',
        'adjustment_required' => 'boolean',
        'adjustment_posted' => 'boolean',
        'serial_numbers' => 'array',
        'metadata' => 'array',
    ];

    // Relationships
    public function stockTake(): BelongsTo
    {
        return $this->belongsTo(StockTake::class);
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(BuildingMaterial::class, 'material_id');
    }

    public function counter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'counted_by');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function adjustmentPoster(): BelongsTo
    {
        return $this->belongsTo(User::class, 'adjustment_posted_by');
    }

    // Scopes
    public function scopeByStatus($query, string $status)
    {
        return $query->where('count_status', $status);
    }

    public function scopeWithVariance($query)
    {
        return $query->where('variance_quantity', '!=', 0);
    }

    public function scopeRequiringInvestigation($query)
    {
        return $query->where('requires_investigation', true)
                    ->where('investigation_completed', false);
    }

    public function scopeRequiringAdjustment($query)
    {
        return $query->where('adjustment_required', true)
                    ->where('adjustment_posted', false);
    }

    public function scopePendingCount($query)
    {
        return $query->where('count_status', 'pending');
    }

    public function scopeCounted($query)
    {
        return $query->whereIn('count_status', ['counted', 'recounted', 'verified']);
    }

    // Helper methods
    public function recordCount(float $physicalQuantity, int $userId): void
    {
        $this->physical_quantity = $physicalQuantity;
        $this->variance_quantity = $physicalQuantity - $this->system_quantity;
        $this->physical_value = $physicalQuantity * $this->unit_cost;
        $this->variance_value = $this->variance_quantity * $this->unit_cost;
        
        $this->count_attempts++;
        $this->counted_by = $userId;
        $this->last_counted_at = now();
        
        if ($this->count_attempts === 1) {
            $this->first_counted_at = now();
            $this->count_status = 'counted';
        } else {
            $this->count_status = 'recounted';
        }
        
        // Determine if investigation is required for significant variances
        $this->checkIfInvestigationRequired();
        
        $this->save();
    }

    public function verify(int $userId): void
    {
        $this->count_status = 'verified';
        $this->verified_by = $userId;
        $this->verified_at = now();
        $this->save();
    }

    public function checkIfInvestigationRequired(): void
    {
        // Require investigation for variances > 5% or > 1000 VND value
        $percentageVariance = $this->system_quantity > 0 ? 
            abs($this->variance_quantity / $this->system_quantity) * 100 : 0;
        
        $this->requires_investigation = $percentageVariance > 5 || abs($this->variance_value) > 1000;
        
        if ($this->requires_investigation) {
            $this->adjustment_required = true;
        }
    }

    public function hasVariance(): bool
    {
        return $this->variance_quantity != 0;
    }

    public function hasPositiveVariance(): bool
    {
        return $this->variance_quantity > 0;
    }

    public function hasNegativeVariance(): bool
    {
        return $this->variance_quantity < 0;
    }

    public function getVariancePercentageAttribute(): float
    {
        return $this->system_quantity > 0 ? 
            ($this->variance_quantity / $this->system_quantity) * 100 : 0;
    }

    public function canBeRecounted(): bool
    {
        return in_array($this->count_status, ['counted', 'recounted']) && 
               $this->stockTake->status === 'in_progress';
    }

    public function canBeVerified(): bool
    {
        return in_array($this->count_status, ['counted', 'recounted']) && 
               !in_array($this->count_status, ['verified', 'adjusted']);
    }

    public function needsInvestigation(): bool
    {
        return $this->requires_investigation && !$this->investigation_completed;
    }

    public function canPostAdjustment(): bool
    {
        return $this->adjustment_required && 
               !$this->adjustment_posted && 
               $this->count_status === 'verified' &&
               (!$this->requires_investigation || $this->investigation_completed);
    }

    public function getCountStatusDisplayAttribute(): string
    {
        return match($this->count_status) {
            'pending' => 'Chờ kiểm đếm',
            'counted' => 'Đã kiểm đếm',
            'recounted' => 'Đã kiểm đếm lại',
            'verified' => 'Đã xác minh',
            'adjusted' => 'Đã điều chỉnh',
            default => $this->count_status
        };
    }

    public function getVarianceDisplayAttribute(): string
    {
        if ($this->variance_quantity == 0) {
            return 'Không có chênh lệch';
        }
        
        $sign = $this->variance_quantity > 0 ? '+' : '';
        return $sign . number_format($this->variance_quantity, 3) . ' ' . $this->unit;
    }
}
