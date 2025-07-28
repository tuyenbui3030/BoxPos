<?php

namespace Packages\SalesOrders\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'sales_order_item_id',
        'item_type',
        'item_id',
        'item_code',
        'item_name',
        'item_description',
        'quantity',
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
        'notes',
        'metadata',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'unit_price' => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_percent' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'line_total' => 'decimal:2',
        'unit_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'expiry_date' => 'date',
        'metadata' => 'array',
    ];

    // Relationships
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function salesOrderItem(): BelongsTo
    {
        return $this->belongsTo(SalesOrderItem::class);
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

    public function getProfitAttribute(): float
    {
        return $this->line_total - $this->total_cost;
    }

    public function getProfitMarginAttribute(): float
    {
        return $this->line_total > 0 ? ($this->profit / $this->line_total) * 100 : 0;
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return \Packages\SalesOrders\Database\Factories\InvoiceItemFactory::new();
    }
}
