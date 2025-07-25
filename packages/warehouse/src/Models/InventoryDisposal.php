<?php

namespace Packages\Warehouse\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Packages\Store\Models\Store;
use Packages\Tenant\Traits\HasTenantScope;
use Packages\User\Models\User;

class InventoryDisposal extends Model
{
    use HasFactory, HasTenantScope;

    protected $fillable = [
        'store_id',
        'disposal_number',
        'title',
        'description',
        'disposal_date',
        'disposal_type',
        'disposal_method',
        'status',
        'total_cost_value',
        'recovery_value',
        'net_loss',
        'currency',
        'disposal_location',
        'disposal_company',
        'disposal_certificate',
        'disposal_certificate_date',
        'requires_approval',
        'is_approved',
        'approved_by',
        'approved_at',
        'approval_notes',
        'inventory_adjusted',
        'inventory_adjusted_at',
        'inventory_adjusted_by',
        'insurance_claim',
        'insurance_claim_number',
        'insurance_claim_amount',
        'insurance_claim_status',
        'attachments',
        'disposal_notes',
        'investigation_notes',
        'metadata',
        'created_by',
    ];

    protected $casts = [
        'disposal_date' => 'date',
        'disposal_certificate_date' => 'date',
        'approved_at' => 'datetime',
        'inventory_adjusted_at' => 'datetime',
        'total_cost_value' => 'decimal:2',
        'recovery_value' => 'decimal:2',
        'net_loss' => 'decimal:2',
        'insurance_claim_amount' => 'decimal:2',
        'requires_approval' => 'boolean',
        'is_approved' => 'boolean',
        'inventory_adjusted' => 'boolean',
        'insurance_claim' => 'boolean',
        'attachments' => 'array',
        'metadata' => 'array',
    ];

    // Relationships
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function inventoryAdjuster(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inventory_adjusted_by');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(InventoryDisposalItem::class, 'disposal_id');
    }

    // Scopes
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('disposal_type', $type);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('disposal_date', [$startDate, $endDate]);
    }

    public function scopePendingApproval($query)
    {
        return $query->where('requires_approval', true)
                    ->where('is_approved', false)
                    ->where('status', 'pending');
    }

    public function scopeWithInsuranceClaim($query)
    {
        return $query->where('insurance_claim', true);
    }

    // Helper methods
    public static function generateDisposalNumber(int $storeId): string
    {
        $year = date('Y');
        $month = date('m');
        $lastDisposal = static::where('store_id', $storeId)
            ->where('disposal_number', 'like', "DIS{$year}{$month}%")
            ->orderBy('id', 'desc')
            ->first();
        
        $sequence = $lastDisposal ? (intval(substr($lastDisposal->disposal_number, -4)) + 1) : 1;
        return "DIS{$year}{$month}" . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    public function calculateTotals(): void
    {
        $this->total_cost_value = $this->items->sum('total_cost');
        $this->recovery_value = $this->items->sum('recovery_value');
        $this->net_loss = $this->total_cost_value - $this->recovery_value;
        $this->save();
    }

    public function canBeApproved(): bool
    {
        return $this->requires_approval && 
               !$this->is_approved && 
               $this->status === 'pending';
    }

    public function canBeProcessed(): bool
    {
        return $this->status === 'approved' || 
               (!$this->requires_approval && $this->status === 'pending');
    }

    public function canAdjustInventory(): bool
    {
        return !$this->inventory_adjusted && 
               $this->status === 'processed' &&
               $this->items()->where('status', 'completed')->count() > 0;
    }

    public function hasInsuranceClaim(): bool
    {
        return $this->insurance_claim;
    }

    public function getStatusDisplayAttribute(): string
    {
        return match($this->status) {
            'draft' => 'Nháp',
            'pending' => 'Chờ duyệt',
            'approved' => 'Đã duyệt',
            'processed' => 'Đang xử lý',
            'completed' => 'Hoàn thành',
            'cancelled' => 'Đã hủy',
            default => $this->status
        };
    }

    public function getDisposalTypeDisplayAttribute(): string
    {
        return match($this->disposal_type) {
            'damage' => 'Hư hỏng',
            'expired' => 'Hết hạn',
            'obsolete' => 'Lỗi thời',
            'theft' => 'Mất trộm',
            'loss' => 'Thất thoát',
            'quality_issue' => 'Vấn đề chất lượng',
            'other' => 'Khác',
            default => $this->disposal_type
        };
    }

    public function getDisposalMethodDisplayAttribute(): string
    {
        return match($this->disposal_method) {
            'destroy' => 'Tiêu hủy',
            'sell' => 'Bán thanh lý',
            'donate' => 'Tặng',
            'return_supplier' => 'Trả nhà cung cấp',
            'recycle' => 'Tái chế',
            'other' => 'Khác',
            default => $this->disposal_method
        };
    }

    public function getFormattedNetLossAttribute(): string
    {
        return number_format($this->net_loss, 0, ',', '.') . ' ' . $this->currency;
    }

    public function getRecoveryPercentageAttribute(): float
    {
        return $this->total_cost_value > 0 ? 
            ($this->recovery_value / $this->total_cost_value) * 100 : 0;
    }
}
