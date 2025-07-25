<?php

namespace Packages\SalesOrders\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Packages\Customer\Models\Customer;
use Packages\Store\Models\Store;
use Packages\Tenant\Traits\HasTenantScope;
use Packages\User\Models\User;

class SalesReturn extends Model
{
    use HasFactory, HasTenantScope;

    protected $fillable = [
        'store_id',
        'sales_order_id',
        'invoice_id',
        'customer_id',
        'return_number',
        'return_date',
        'return_type',
        'status',
        'return_reason',
        'return_reason_detail',
        'subtotal',
        'tax_amount',
        'total_amount',
        'currency',
        'refund_method',
        'refund_status',
        'refund_amount',
        'restocking_fee',
        'refund_processed_date',
        'restock_items',
        'item_condition',
        'processed_by',
        'approved_by',
        'approved_at',
        'notes',
        'internal_notes',
        'attachments',
        'metadata',
    ];

    protected $casts = [
        'return_date' => 'date',
        'refund_processed_date' => 'date',
        'approved_at' => 'datetime',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'refund_amount' => 'decimal:2',
        'restocking_fee' => 'decimal:2',
        'restock_items' => 'boolean',
        'attachments' => 'array',
        'metadata' => 'array',
    ];

    // Relationships
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function salesOrder(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(SalesReturnItem::class);
    }

    // Scopes
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByRefundStatus($query, string $refundStatus)
    {
        return $query->where('refund_status', $refundStatus);
    }

    public function scopeByReturnReason($query, string $reason)
    {
        return $query->where('return_reason', $reason);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('return_date', [$startDate, $endDate]);
    }

    // Helper methods
    public static function generateReturnNumber(int $storeId): string
    {
        $lastReturn = static::where('store_id', $storeId)
            ->orderBy('id', 'desc')
            ->first();
        
        $number = $lastReturn ? ($lastReturn->id + 1) : 1;
        return 'RET' . str_pad($number, 8, '0', STR_PAD_LEFT);
    }

    public function calculateTotals(): void
    {
        $this->subtotal = $this->items->sum('line_total');
        $this->tax_amount = $this->items->sum(function ($item) {
            return $item->line_total * ($item->tax_percent / 100);
        });
        $this->total_amount = $this->subtotal + $this->tax_amount;
        $this->refund_amount = $this->total_amount - $this->restocking_fee;
    }

    public function canBeApproved(): bool
    {
        return $this->status === 'pending' && !$this->approved_at;
    }

    public function canBeProcessed(): bool
    {
        return $this->status === 'approved' && $this->refund_status === 'pending';
    }

    public function isFullyRefunded(): bool
    {
        return $this->refund_status === 'completed';
    }
}
