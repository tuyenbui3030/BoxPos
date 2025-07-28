<?php

namespace Packages\SalesOrders\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesReturnItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'sales_return_id',
        'sales_order_item_id',
        'invoice_item_id',
        'item_type',
        'item_id',
        'item_code',
        'item_name',
        'item_description',
        'original_quantity',
        'return_quantity',
        'unit',
        'original_unit_price',
        'return_unit_price',
        'line_total',
        'return_reason',
        'return_reason_detail',
        'item_condition',
        'can_restock',
        'batch_number',
        'serial_numbers',
        'expiry_date',
        'notes',
        'metadata',
    ];

    protected $casts = [
        'original_quantity' => 'decimal:3',
        'return_quantity' => 'decimal:3',
        'original_unit_price' => 'decimal:2',
        'return_unit_price' => 'decimal:2',
        'line_total' => 'decimal:2',
        'can_restock' => 'boolean',
        'expiry_date' => 'date',
        'metadata' => 'array',
    ];

    // Relationships
    public function salesReturn(): BelongsTo
    {
        return $this->belongsTo(SalesReturn::class);
    }

    public function salesOrderItem(): BelongsTo
    {
        return $this->belongsTo(SalesOrderItem::class);
    }

    public function invoiceItem(): BelongsTo
    {
        return $this->belongsTo(InvoiceItem::class);
    }

    // Helper methods
    public function calculateLineTotal(): void
    {
        $this->line_total = $this->return_quantity * $this->return_unit_price;
    }

    public function getReturnPercentageAttribute(): float
    {
        return $this->original_quantity > 0 ? ($this->return_quantity / $this->original_quantity) * 100 : 0;
    }

    public function isFullReturn(): bool
    {
        return $this->return_quantity >= $this->original_quantity;
    }

    public function isPartialReturn(): bool
    {
        return $this->return_quantity > 0 && $this->return_quantity < $this->original_quantity;
    }

    public function shouldRestock(): bool
    {
        return $this->can_restock && 
               $this->salesReturn->restock_items && 
               in_array($this->item_condition, ['new', 'good']);
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return \Packages\SalesOrders\Database\Factories\SalesReturnItemFactory::new();
    }
}
