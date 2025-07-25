<?php

namespace Packages\MaterialPurchasing\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Packages\MaterialCatalog\Models\BuildingMaterial;

class MaterialPurchaseOrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_order_id',
        'material_id',
        'material_code',
        'material_name',
        'material_description',
        'quantity_ordered',
        'quantity_received',
        'quantity_pending',
        'quantity_cancelled',
        'unit',
        'unit_price',
        'discount_percent',
        'discount_amount',
        'tax_percent',
        'tax_amount',
        'line_total',
        'expected_delivery_date',
        'actual_delivery_date',
        'delivery_status',
        'specifications',
        'quality_requirements',
        'quality_checked',
        'quality_status',
        'quality_notes',
        'batch_number',
        'serial_numbers',
        'manufacture_date',
        'expiry_date',
        'status',
        'notes',
        'metadata',
    ];

    protected $casts = [
        'quantity_ordered' => 'decimal:3',
        'quantity_received' => 'decimal:3',
        'quantity_pending' => 'decimal:3',
        'quantity_cancelled' => 'decimal:3',
        'unit_price' => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_percent' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'line_total' => 'decimal:2',
        'expected_delivery_date' => 'date',
        'actual_delivery_date' => 'date',
        'quality_checked' => 'boolean',
        'manufacture_date' => 'date',
        'expiry_date' => 'date',
        'specifications' => 'array',
        'metadata' => 'array',
    ];

    // Relationships
    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(MaterialPurchaseOrder::class, 'purchase_order_id');
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(BuildingMaterial::class, 'material_id');
    }

    // Scopes
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByDeliveryStatus($query, string $status)
    {
        return $query->where('delivery_status', $status);
    }

    public function scopePendingDelivery($query)
    {
        return $query->whereIn('delivery_status', ['pending', 'partial']);
    }

    public function scopeOverdue($query)
    {
        return $query->where('expected_delivery_date', '<', now())
                    ->whereIn('delivery_status', ['pending', 'partial']);
    }

    // Helper methods
    public function calculateLineTotal(): void
    {
        $subtotal = $this->quantity_ordered * $this->unit_price;
        $afterDiscount = $subtotal - $this->discount_amount;
        $this->line_total = $afterDiscount + $this->tax_amount;
    }

    public function calculateTaxAmount(): void
    {
        $subtotal = $this->quantity_ordered * $this->unit_price - $this->discount_amount;
        $this->tax_amount = $subtotal * ($this->tax_percent / 100);
    }

    public function updatePendingQuantity(): void
    {
        $this->quantity_pending = $this->quantity_ordered - $this->quantity_received - $this->quantity_cancelled;
    }

    public function getRemainingQuantityAttribute(): float
    {
        return $this->quantity_ordered - $this->quantity_received - $this->quantity_cancelled;
    }

    public function getDeliveryProgressAttribute(): float
    {
        return $this->quantity_ordered > 0 ? ($this->quantity_received / $this->quantity_ordered) * 100 : 0;
    }

    public function isFullyDelivered(): bool
    {
        return $this->delivery_status === 'delivered' && $this->remaining_quantity <= 0;
    }

    public function isPartiallyDelivered(): bool
    {
        return $this->delivery_status === 'partial' && $this->quantity_received > 0;
    }

    public function isOverdue(): bool
    {
        return $this->expected_delivery_date && 
               $this->expected_delivery_date->isPast() && 
               !$this->isFullyDelivered();
    }

    public function canBeReceived(): bool
    {
        return in_array($this->status, ['confirmed', 'partial_received']) && 
               $this->remaining_quantity > 0;
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['pending', 'confirmed']) && 
               $this->quantity_received == 0;
    }

    public function passedQualityCheck(): bool
    {
        return $this->quality_checked && $this->quality_status === 'passed';
    }

    public function failedQualityCheck(): bool
    {
        return $this->quality_checked && $this->quality_status === 'failed';
    }
}
