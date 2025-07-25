<?php

namespace Packages\Warehouse\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Packages\MaterialCatalog\Models\BuildingMaterial;
use Packages\Store\Models\Store;
use Packages\Tenant\Traits\HasTenantScope;
use Packages\User\Models\User;

class InventoryMovement extends Model
{
    use HasFactory, HasTenantScope;

    protected $fillable = [
        'store_id',
        'material_id',
        'movement_number',
        'movement_date',
        'movement_type',
        'movement_reason',
        'quantity',
        'unit',
        'unit_cost',
        'total_cost',
        'balance_before',
        'balance_after',
        'batch_number',
        'serial_numbers',
        'expiry_date',
        'related_document_type',
        'related_document_id',
        'related_document_number',
        'from_location',
        'to_location',
        'warehouse_zone',
        'requires_approval',
        'is_approved',
        'approved_by',
        'approved_at',
        'is_verified',
        'verified_by',
        'verified_at',
        'description',
        'notes',
        'metadata',
        'created_by',
    ];

    protected $casts = [
        'movement_date' => 'date',
        'expiry_date' => 'date',
        'approved_at' => 'datetime',
        'verified_at' => 'datetime',
        'quantity' => 'decimal:3',
        'unit_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'balance_before' => 'decimal:3',
        'balance_after' => 'decimal:3',
        'requires_approval' => 'boolean',
        'is_approved' => 'boolean',
        'is_verified' => 'boolean',
        'metadata' => 'array',
    ];

    // Relationships
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(BuildingMaterial::class, 'material_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scopes
    public function scopeByType($query, string $type)
    {
        return $query->where('movement_type', $type);
    }

    public function scopeByReason($query, string $reason)
    {
        return $query->where('movement_reason', $reason);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('movement_date', [$startDate, $endDate]);
    }

    public function scopeInbound($query)
    {
        return $query->where('movement_type', 'in');
    }

    public function scopeOutbound($query)
    {
        return $query->where('movement_type', 'out');
    }

    public function scopePendingApproval($query)
    {
        return $query->where('requires_approval', true)
                    ->where('is_approved', false);
    }

    public function scopePendingVerification($query)
    {
        return $query->where('is_verified', false);
    }

    // Helper methods
    public static function generateMovementNumber(int $storeId): string
    {
        $date = now()->format('Ymd');
        $lastMovement = static::where('store_id', $storeId)
            ->where('movement_number', 'like', "MOV{$date}%")
            ->orderBy('id', 'desc')
            ->first();
        
        $sequence = $lastMovement ? (intval(substr($lastMovement->movement_number, -4)) + 1) : 1;
        return "MOV{$date}" . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    public function calculateTotalCost(): void
    {
        $this->total_cost = $this->quantity * $this->unit_cost;
    }

    public function isInbound(): bool
    {
        return $this->movement_type === 'in';
    }

    public function isOutbound(): bool
    {
        return $this->movement_type === 'out';
    }

    public function isAdjustment(): bool
    {
        return $this->movement_type === 'adjustment';
    }

    public function isTransfer(): bool
    {
        return $this->movement_type === 'transfer';
    }

    public function isDisposal(): bool
    {
        return $this->movement_type === 'disposal';
    }

    public function canBeApproved(): bool
    {
        return $this->requires_approval && !$this->is_approved;
    }

    public function canBeVerified(): bool
    {
        return !$this->is_verified && (!$this->requires_approval || $this->is_approved);
    }

    public function hasRelatedDocument(): bool
    {
        return !empty($this->related_document_type) && !empty($this->related_document_id);
    }

    public function getMovementTypeDisplayAttribute(): string
    {
        return match($this->movement_type) {
            'in' => 'Nhập kho',
            'out' => 'Xuất kho',
            'adjustment' => 'Điều chỉnh',
            'transfer' => 'Chuyển kho',
            'disposal' => 'Xuất hủy',
            'return' => 'Trả hàng',
            default => $this->movement_type
        };
    }

    public function getQuantityWithSignAttribute(): string
    {
        $sign = $this->isInbound() ? '+' : '-';
        return $sign . number_format($this->quantity, 3) . ' ' . $this->unit;
    }
}
