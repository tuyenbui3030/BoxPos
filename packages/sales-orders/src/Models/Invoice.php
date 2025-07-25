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

class Invoice extends Model
{
    use HasFactory, HasTenantScope;

    protected $fillable = [
        'store_id',
        'sales_order_id',
        'customer_id',
        'invoice_number',
        'tax_invoice_number',
        'invoice_date',
        'due_date',
        'invoice_type',
        'status',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'shipping_cost',
        'other_charges',
        'total_amount',
        'currency',
        'payment_status',
        'paid_amount',
        'balance_due',
        'payment_method',
        'customer_name',
        'customer_email',
        'customer_phone',
        'billing_address',
        'shipping_address',
        'customer_tax_code',
        'tax_rate',
        'is_tax_inclusive',
        'is_e_invoice',
        'e_invoice_code',
        'e_invoice_sent_at',
        'e_invoice_data',
        'sales_person_id',
        'created_by',
        'approved_by',
        'approved_at',
        'notes',
        'terms_conditions',
        'metadata',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'approved_at' => 'datetime',
        'e_invoice_sent_at' => 'datetime',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'other_charges' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'balance_due' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'is_tax_inclusive' => 'boolean',
        'is_e_invoice' => 'boolean',
        'e_invoice_data' => 'array',
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
        return $this->hasMany(InvoiceItem::class);
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

    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now())
            ->whereIn('payment_status', ['pending', 'partial']);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('invoice_date', [$startDate, $endDate]);
    }

    // Helper methods
    public static function generateInvoiceNumber(int $storeId): string
    {
        $lastInvoice = static::where('store_id', $storeId)
            ->orderBy('id', 'desc')
            ->first();
        
        $number = $lastInvoice ? ($lastInvoice->id + 1) : 1;
        return 'INV' . str_pad($number, 8, '0', STR_PAD_LEFT);
    }

    public function calculateTotals(): void
    {
        $this->subtotal = $this->items->sum('line_total');
        $this->tax_amount = $this->items->sum('tax_amount');
        $this->total_amount = $this->subtotal + $this->tax_amount + $this->shipping_cost + $this->other_charges - $this->discount_amount;
        $this->balance_due = $this->total_amount - $this->paid_amount;
    }

    public function isOverdue(): bool
    {
        return $this->due_date && $this->due_date->isPast() && !$this->isFullyPaid();
    }

    public function isFullyPaid(): bool
    {
        return $this->payment_status === 'paid' && $this->balance_due <= 0;
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['draft', 'sent']) && $this->payment_status === 'pending';
    }
}
