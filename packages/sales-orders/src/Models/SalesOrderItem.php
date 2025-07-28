<?php

namespace Packages\SalesOrders\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalesOrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'sales_order_id',
        'item_type',
        'item_id',
        'item_code',
        'item_name',
        'item_description',
        'quantity',
        'delivered_quantity',
        'returned_quantity',
        'unit',
        'unit_price',
        'discount_percent',
        'discount_amount',
        'tax_percent',
        'tax_amount',
        'line_total',
        'unit_cost',
        'total_cost',
        'batch_number',
        'serial_numbers',
        'expiry_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'delivered_quantity' => 'decimal:3',
        'returned_quantity' => 'decimal:3',
        'unit_price' => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_percent' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'line_total' => 'decimal:2',
        'unit_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'expiry_date' => 'date',
    ];

    // Relationships
    public function salesOrder(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class);
    }

    public function invoiceItems(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function returnItems(): HasMany
    {
        return $this->hasMany(SalesReturnItem::class);
    }

    // Helper methods
    public function calculateLineTotal(): void
    {
        $subtotal = $this->quantity * $this->unit_price;
        $afterDiscount = $subtotal - $this->discount_amount;
        $this->line_total = $afterDiscount + $this->tax_amount;
    }

    public function calculateTaxAmount(): void
    {
        $subtotal = $this->quantity * $this->unit_price - $this->discount_amount;
        $this->tax_amount = $subtotal * ($this->tax_percent / 100);
    }

    public function getRemainingQuantityAttribute(): float
    {
        return $this->quantity - $this->delivered_quantity - $this->returned_quantity;
    }

    public function canBeDelivered(): bool
    {
        return $this->remaining_quantity > 0 && in_array($this->status, ['confirmed', 'processing']);
    }

    public function canBeReturned(): bool
    {
        return $this->delivered_quantity > $this->returned_quantity;
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return \Packages\SalesOrders\Database\Factories\SalesOrderItemFactory::new();
    }
}
