<?php

namespace Packages\MaterialPurchasing\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Packages\MaterialSuppliers\Models\MaterialSupplier;
use Packages\Store\Models\Store;
use Packages\User\Models\User;
use Packages\Tenant\Traits\HasTenantScope;

class MaterialPurchaseOrder extends Model
{
    use HasFactory, HasTenantScope;

    protected $fillable = [
        'store_id',
        'supplier_id',
        'po_number',
        'supplier_reference',
        'order_date',
        'expected_delivery_date',
        'actual_delivery_date',
        'priority',
        'status',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'shipping_cost',
        'other_charges',
        'total_amount',
        'currency',
        'payment_terms',
        'payment_due_date',
        'payment_status',
        'delivery_address',
        'delivery_contact',
        'delivery_phone',
        'delivery_instructions',
        'delivery_method',
        'tracking_number',
        'requested_by',
        'approved_by',
        'approved_at',
        'approval_notes',
        'notes',
        'terms_conditions',
        'attachments',
        'is_recurring',
        'recurring_frequency',
    ];

    protected $casts = [
        'order_date' => 'date',
        'expected_delivery_date' => 'date',
        'actual_delivery_date' => 'date',
        'payment_due_date' => 'date',
        'approved_at' => 'datetime',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'other_charges' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'is_recurring' => 'boolean',
        'attachments' => 'array',
    ];

    const STATUS_DRAFT = 'draft';
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_SENT = 'sent';
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_PARTIAL_RECEIVED = 'partial_received';
    const STATUS_RECEIVED = 'received';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_CLOSED = 'closed';

    const PRIORITY_LOW = 'low';
    const PRIORITY_NORMAL = 'normal';
    const PRIORITY_HIGH = 'high';
    const PRIORITY_URGENT = 'urgent';

    const PAYMENT_PENDING = 'pending';
    const PAYMENT_PARTIAL = 'partial';
    const PAYMENT_PAID = 'paid';
    const PAYMENT_OVERDUE = 'overdue';

    /**
     * Get the store that owns this purchase order.
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    /**
     * Get the supplier for this purchase order.
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(MaterialSupplier::class, 'supplier_id');
    }

    /**
     * Get the user who requested this purchase order.
     */
    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    /**
     * Get the user who approved this purchase order.
     */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Scope by status.
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope by priority.
     */
    public function scopeByPriority($query, string $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Scope for pending orders.
     */
    public function scopePending($query)
    {
        return $query->whereIn('status', [self::STATUS_DRAFT, self::STATUS_PENDING]);
    }

    /**
     * Scope for approved orders.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    /**
     * Scope for overdue orders.
     */
    public function scopeOverdue($query)
    {
        return $query->where('expected_delivery_date', '<', now())
                    ->whereNotIn('status', [self::STATUS_RECEIVED, self::STATUS_CLOSED, self::STATUS_CANCELLED]);
    }

    /**
     * Check if order is pending approval.
     */
    public function isPendingApproval(): bool
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_PENDING]);
    }

    /**
     * Check if order is approved.
     */
    public function isApproved(): bool
    {
        return !is_null($this->approved_at);
    }

    /**
     * Check if order is overdue.
     */
    public function isOverdue(): bool
    {
        return $this->expected_delivery_date < now() && 
               !in_array($this->status, [self::STATUS_RECEIVED, self::STATUS_CLOSED, self::STATUS_CANCELLED]);
    }

    /**
     * Check if order is completed.
     */
    public function isCompleted(): bool
    {
        return in_array($this->status, [self::STATUS_RECEIVED, self::STATUS_CLOSED]);
    }

    /**
     * Get status badge color.
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            self::STATUS_DRAFT => 'gray',
            self::STATUS_PENDING => 'yellow',
            self::STATUS_APPROVED => 'blue',
            self::STATUS_SENT => 'indigo',
            self::STATUS_CONFIRMED => 'purple',
            self::STATUS_PARTIAL_RECEIVED => 'orange',
            self::STATUS_RECEIVED => 'green',
            self::STATUS_CLOSED => 'green',
            self::STATUS_CANCELLED => 'red',
            default => 'gray',
        };
    }

    /**
     * Get priority badge color.
     */
    public function getPriorityColorAttribute(): string
    {
        return match($this->priority) {
            self::PRIORITY_LOW => 'gray',
            self::PRIORITY_NORMAL => 'blue',
            self::PRIORITY_HIGH => 'orange',
            self::PRIORITY_URGENT => 'red',
            default => 'gray',
        };
    }

    /**
     * Get formatted total amount.
     */
    public function getFormattedTotalAttribute(): string
    {
        return number_format($this->total_amount, 0, ',', '.') . ' ' . $this->currency;
    }

    /**
     * Generate unique PO number.
     */
    public static function generatePONumber(int $storeId): string
    {
        $year = date('Y');
        $month = date('m');
        $lastPO = static::where('store_id', $storeId)
                       ->where('po_number', 'like', "PO{$year}{$month}{$storeId}%")
                       ->orderBy('id', 'desc')
                       ->first();
        
        $sequence = $lastPO ? (intval(substr($lastPO->po_number, -4)) + 1) : 1;
        return "PO{$year}{$month}{$storeId}" . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }
}
