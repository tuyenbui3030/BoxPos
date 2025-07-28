<?php

namespace Packages\Payments\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Packages\Store\Models\Store;
use Packages\User\Models\User;

class PaymentMethod extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_id',
        'code',
        'name',
        'description',
        'type',
        'category',
        'requires_verification',
        'provider',
        'provider_code',
        'provider_config',
        'processing_fee_percent',
        'processing_fee_fixed',
        'min_amount',
        'max_amount',
        'currency',
        'settlement_days',
        'cutoff_time',
        'processing_days',
        'api_endpoint',
        'webhook_url',
        'api_credentials',
        'sandbox_mode',
        'icon',
        'color',
        'show_on_pos',
        'show_on_website',
        'show_on_mobile',
        'sort_order',
        'is_active',
        'is_default',
        'availability_schedule',
        'unavailable_message',
        'requires_pin',
        'requires_signature',
        'supports_refund',
        'supports_partial_refund',
        'refund_days_limit',
        'metadata',
        'created_by',
    ];

    protected $casts = [
        'provider_config' => 'array',
        'processing_days' => 'array',
        'api_credentials' => 'array',
        'availability_schedule' => 'array',
        'metadata' => 'array',
        'requires_verification' => 'boolean',
        'sandbox_mode' => 'boolean',
        'show_on_pos' => 'boolean',
        'show_on_website' => 'boolean',
        'show_on_mobile' => 'boolean',
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'requires_pin' => 'boolean',
        'requires_signature' => 'boolean',
        'supports_refund' => 'boolean',
        'supports_partial_refund' => 'boolean',
        'processing_fee_percent' => 'decimal:4',
        'processing_fee_fixed' => 'decimal:2',
        'min_amount' => 'decimal:2',
        'max_amount' => 'decimal:2',
        'cutoff_time' => 'datetime:H:i',
    ];

    /**
     * Get the store that owns this payment method.
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    /**
     * Get the user who created this payment method.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the payments using this method.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Scope to get active payment methods.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get payment methods for a specific store.
     */
    public function scopeForStore($query, $storeId)
    {
        return $query->where('store_id', $storeId);
    }

    /**
     * Scope to get payment methods by type.
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Get the default payment method for a store.
     */
    public static function getDefault($storeId)
    {
        return static::where('store_id', $storeId)
            ->where('is_default', true)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Check if this payment method supports refunds.
     */
    public function supportsRefunds(): bool
    {
        return $this->supports_refund;
    }

    /**
     * Check if this payment method supports partial refunds.
     */
    public function supportsPartialRefunds(): bool
    {
        return $this->supports_partial_refund;
    }

    /**
     * Calculate processing fee for an amount.
     */
    public function calculateProcessingFee(float $amount): float
    {
        $percentageFee = $amount * ($this->processing_fee_percent / 100);
        return $percentageFee + $this->processing_fee_fixed;
    }

    /**
     * Check if amount is within limits.
     */
    public function isAmountValid(float $amount): bool
    {
        if ($amount < $this->min_amount) {
            return false;
        }

        if ($this->max_amount && $amount > $this->max_amount) {
            return false;
        }

        return true;
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return \Packages\Payments\Database\Factories\PaymentMethodFactory::new();
    }
}
