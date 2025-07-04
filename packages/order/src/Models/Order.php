<?php

namespace Packages\Order\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Packages\Order\Builders\OrderBuilder;
use Packages\Store\Models\Store;
use Packages\Customer\Models\Customer;
use Packages\User\Models\User;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_id',
        'branch_id',
        'customer_id',
        'order_number',
        'order_date',
        'status',
        'channel',
        'subtotal',
        'discount_amount',
        'tax_amount',
        'shipping_amount',
        'total_amount',
        'payment_status',
        'notes',
        'shipping_address',
        'created_by',
    ];

    protected $casts = [
        'order_date' => 'datetime',
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'shipping_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'shipping_address' => 'array',
    ];

    /**
     * Create a new Eloquent query builder for the model.
     */
    public function newEloquentBuilder($query): OrderBuilder
    {
        return new OrderBuilder($query);
    }

    /**
     * Relationships
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Store::class, 'branch_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scopes
     */
    public function scopeForStore($query, $storeId)
    {
        return $query->where('store_id', $storeId);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByChannel($query, $channel)
    {
        return $query->where('channel', $channel);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('order_date', [$startDate, $endDate]);
    }

    /**
     * Helper methods
     */
    public function isPaid(): bool
    {
        return $this->payment_status === 'paid';
    }

    public function isCompleted(): bool
    {
        return in_array($this->status, ['delivered', 'completed']);
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['draft', 'pending', 'confirmed']);
    }

    public function getTotalProfitAttribute(): float
    {
        return $this->items->sum(function ($item) {
            return ($item->unit_price - $item->cost_price) * $item->quantity;
        });
    }

    /**
     * Generate unique order number
     */
    public static function generateOrderNumber(int $storeId): string
    {
        $prefix = 'ORD' . date('Ymd');
        $lastOrder = static::where('store_id', $storeId)
            ->where('order_number', 'like', $prefix . '%')
            ->orderBy('order_number', 'desc')
            ->first();

        if (!$lastOrder) {
            return $prefix . '001';
        }

        $lastNumber = (int) substr($lastOrder->order_number, strlen($prefix));
        return $prefix . str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
    }
}