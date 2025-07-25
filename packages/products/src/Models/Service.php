<?php

namespace Packages\Products\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Packages\Store\Models\Store;
use Packages\Tenant\Traits\HasTenantScope;
use Packages\User\Models\User;

class Service extends Model
{
    use HasFactory, HasTenantScope;

    protected $fillable = [
        'store_id',
        'category_id',
        'code',
        'name',
        'description',
        'short_description',
        'type',
        'status',
        'billing_type',
        'base_price',
        'hourly_rate',
        'setup_fee',
        'currency',
        'estimated_duration',
        'min_duration',
        'max_duration',
        'requires_booking',
        'booking_lead_time',
        'available_days',
        'available_from',
        'available_to',
        'is_taxable',
        'tax_rate',
        'tax_class',
        'delivery_method',
        'location',
        'requires_materials',
        'required_materials',
        'requires_staff',
        'required_skills',
        'min_staff',
        'max_staff',
        'images',
        'featured_image',
        'meta_title',
        'meta_description',
        'tags',
        'is_featured',
        'allow_online_booking',
        'send_confirmation',
        'send_reminder',
        'reminder_hours',
        'sort_order',
        'custom_fields',
        'metadata',
        'created_by',
    ];

    protected $casts = [
        'available_from' => 'datetime',
        'available_to' => 'datetime',
        'base_price' => 'decimal:2',
        'hourly_rate' => 'decimal:2',
        'setup_fee' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'requires_booking' => 'boolean',
        'is_taxable' => 'boolean',
        'requires_materials' => 'boolean',
        'requires_staff' => 'boolean',
        'is_featured' => 'boolean',
        'allow_online_booking' => 'boolean',
        'send_confirmation' => 'boolean',
        'send_reminder' => 'boolean',
        'available_days' => 'array',
        'required_materials' => 'array',
        'required_skills' => 'array',
        'images' => 'array',
        'tags' => 'array',
        'custom_fields' => 'array',
        'metadata' => 'array',
    ];

    // Relationships
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeRequiringBooking($query)
    {
        return $query->where('requires_booking', true);
    }

    public function scopeByCategory($query, int $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeByDeliveryMethod($query, string $method)
    {
        return $query->where('delivery_method', $method);
    }

    // Helper methods
    public static function generateCode(int $storeId): string
    {
        $lastService = static::where('store_id', $storeId)
            ->orderBy('id', 'desc')
            ->first();
        
        $number = $lastService ? ($lastService->id + 1) : 1;
        return 'SRV' . str_pad($number, 6, '0', STR_PAD_LEFT);
    }

    public function calculatePrice(int $duration = null, array $customOptions = []): float
    {
        $price = $this->base_price;
        
        if ($this->billing_type === 'hourly' && $duration) {
            $hours = ceil($duration / 60); // Convert minutes to hours
            $price = $this->hourly_rate * $hours;
        }
        
        if ($this->billing_type === 'daily' && $duration) {
            $days = ceil($duration / (60 * 24)); // Convert minutes to days
            $price = $this->base_price * $days;
        }
        
        // Add setup fee for new services
        if (isset($customOptions['include_setup_fee']) && $customOptions['include_setup_fee']) {
            $price += $this->setup_fee;
        }
        
        return $price;
    }

    public function isAvailableOnDay(string $dayOfWeek): bool
    {
        if (!$this->available_days) {
            return true; // Available all days if not specified
        }
        
        return in_array(strtolower($dayOfWeek), array_map('strtolower', $this->available_days));
    }

    public function isAvailableAtTime(string $time): bool
    {
        if (!$this->available_from || !$this->available_to) {
            return true; // Available all time if not specified
        }
        
        $checkTime = \Carbon\Carbon::createFromFormat('H:i', $time);
        $fromTime = \Carbon\Carbon::createFromFormat('H:i', $this->available_from->format('H:i'));
        $toTime = \Carbon\Carbon::createFromFormat('H:i', $this->available_to->format('H:i'));
        
        return $checkTime->between($fromTime, $toTime);
    }

    public function canBeBookedAt(\Carbon\Carbon $dateTime): bool
    {
        // Check if booking is required
        if (!$this->requires_booking) {
            return true;
        }
        
        // Check lead time
        $leadTimeHours = $this->booking_lead_time ?: 0;
        $minimumBookingTime = now()->addHours($leadTimeHours);
        
        if ($dateTime->lt($minimumBookingTime)) {
            return false;
        }
        
        // Check day availability
        if (!$this->isAvailableOnDay($dateTime->format('l'))) {
            return false;
        }
        
        // Check time availability
        if (!$this->isAvailableAtTime($dateTime->format('H:i'))) {
            return false;
        }
        
        return true;
    }

    public function getEstimatedDurationInHoursAttribute(): float
    {
        return $this->estimated_duration ? $this->estimated_duration / 60 : 0;
    }

    public function getFormattedPriceAttribute(): string
    {
        $price = $this->base_price;
        
        if ($this->billing_type === 'hourly') {
            return number_format($price, 0, ',', '.') . ' ' . $this->currency . '/giờ';
        }
        
        if ($this->billing_type === 'daily') {
            return number_format($price, 0, ',', '.') . ' ' . $this->currency . '/ngày';
        }
        
        return number_format($price, 0, ',', '.') . ' ' . $this->currency;
    }

    public function getDeliveryMethodDisplayAttribute(): string
    {
        return match($this->delivery_method) {
            'in_person' => 'Trực tiếp',
            'remote' => 'Từ xa',
            'hybrid' => 'Kết hợp',
            'on_site' => 'Tại chỗ',
            default => $this->delivery_method
        };
    }

    public function getTypeDisplayAttribute(): string
    {
        return match($this->type) {
            'standard' => 'Tiêu chuẩn',
            'custom' => 'Tùy chỉnh',
            'subscription' => 'Đăng ký',
            'one_time' => 'Một lần',
            default => $this->type
        };
    }

    public function getBillingTypeDisplayAttribute(): string
    {
        return match($this->billing_type) {
            'fixed' => 'Cố định',
            'hourly' => 'Theo giờ',
            'daily' => 'Theo ngày',
            'monthly' => 'Theo tháng',
            'custom' => 'Tùy chỉnh',
            default => $this->billing_type
        };
    }
}
