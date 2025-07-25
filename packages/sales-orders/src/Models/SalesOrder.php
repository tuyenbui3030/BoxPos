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

class SalesOrder extends Model
{
    use HasFactory, HasTenantScope;

    protected $fillable = [
        'store_id',
        'customer_id',
        'order_number',
        'customer_reference',
        'order_date',
        'delivery_date',
        'actual_delivery_date',
        'priority',
        'status',
        'order_type',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'shipping_cost',
        'other_charges',
        'total_amount',
        'currency',
        'payment_status',
        'payment_method',
        'paid_amount',
        'balance_due',
        'payment_due_date',
        'delivery_address',
        'delivery_contact',
        'delivery_phone',
        'delivery_instructions',
        'delivery_method',
        'tracking_number',
        'sales_channel',
        'sales_person_id',
        'created_by',
        'approved_by',
        'approved_at',
        'notes',
        'internal_notes',
        'metadata',
        'is_recurring',
        'recurring_frequency',
    ];

    protected $casts = [
        'order_date' => 'date',
        'delivery_date' => 'date',
        'actual_delivery_date' => 'date',
        'payment_due_date' => 'date',
        'approved_at' => 'datetime',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'other_charges' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'balance_due' => 'decimal:2',
        'metadata' => 'array',
        'is_recurring' => 'boolean',
    ];

    // Relationships
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function salesPerson(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sales_person_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(SalesOrderItem::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function returns(): HasMany
    {
        return $this->hasMany(SalesReturn::class);
    }

    // Scopes
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByPaymentStatus($query, string $paymentStatus)
    {
        return $query->where('payment_status', $paymentStatus);
    }

    public function scopeBySalesChannel($query, string $channel)
    {
        return $query->where('sales_channel', $channel);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('order_date', [$startDate, $endDate]);
    }

    // Helper methods
    public static function generateOrderNumber(int $storeId): string
    {
        $lastOrder = static::where('store_id', $storeId)
            ->orderBy('id', 'desc')
            ->first();
        
        $number = $lastOrder ? ($lastOrder->id + 1) : 1;
        return 'SO' . str_pad($number, 8, '0', STR_PAD_LEFT);
    }

    public function calculateTotals(): void
    {
        $this->subtotal = $this->items->sum('line_total');
        $this->tax_amount = $this->items->sum('tax_amount');
        $this->total_amount = $this->subtotal + $this->tax_amount + $this->shipping_cost + $this->other_charges - $this->discount_amount;
        $this->balance_due = $this->total_amount - $this->paid_amount;
    }

    public function isFullyPaid(): bool
    {
        return $this->payment_status === 'paid' && $this->balance_due <= 0;
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['draft', 'pending', 'confirmed']);
    }
}
