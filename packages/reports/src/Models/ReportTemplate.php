<?php

namespace Packages\Reports\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Packages\Store\Models\Store;
use Packages\Tenant\Traits\HasTenantScope;
use Packages\User\Models\User;

class ReportTemplate extends Model
{
    use HasFactory, HasTenantScope;

    protected $fillable = [
        'store_id',
        'code',
        'name',
        'description',
        'category',
        'type',
        'frequency',
        'data_sources',
        'filters',
        'columns',
        'grouping',
        'sorting',
        'calculations',
        'output_format',
        'chart_config',
        'layout_config',
        'permissions',
        'is_public',
        'is_system',
        'is_active',
        'auto_refresh',
        'refresh_interval',
        'cache_enabled',
        'cache_duration',
        'email_settings',
        'export_settings',
        'metadata',
        'created_by',
    ];

    protected $casts = [
        'data_sources' => 'array',
        'filters' => 'array',
        'columns' => 'array',
        'grouping' => 'array',
        'sorting' => 'array',
        'calculations' => 'array',
        'chart_config' => 'array',
        'layout_config' => 'array',
        'permissions' => 'array',
        'email_settings' => 'array',
        'export_settings' => 'array',
        'metadata' => 'array',
        'is_public' => 'boolean',
        'is_system' => 'boolean',
        'is_active' => 'boolean',
        'auto_refresh' => 'boolean',
        'cache_enabled' => 'boolean',
    ];

    // Relationships
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function instances(): HasMany
    {
        return $this->hasMany(ReportInstance::class, 'template_id');
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(ReportSchedule::class, 'template_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    public function scopeSystem($query)
    {
        return $query->where('is_system', true);
    }

    public function scopeUserCreated($query)
    {
        return $query->where('is_system', false);
    }

    // Helper methods
    public function getCategoryDisplayAttribute(): string
    {
        return match($this->category) {
            'sales' => 'Báo cáo Bán hàng',
            'daily_summary' => 'Báo cáo Cuối ngày',
            'inventory' => 'Báo cáo Hàng hóa',
            'customer' => 'Báo cáo Khách hàng',
            'employee' => 'Báo cáo Nhân viên',
            'financial' => 'Báo cáo Tài chính',
            'channel' => 'Báo cáo Kênh bán hàng',
            'supplier' => 'Báo cáo Nhà cung cấp',
            'purchasing' => 'Báo cáo Đặt hàng',
            default => $this->category
        };
    }

    public function getTypeDisplayAttribute(): string
    {
        return match($this->type) {
            'summary' => 'Tổng hợp',
            'detail' => 'Chi tiết',
            'comparison' => 'So sánh',
            'trend' => 'Xu hướng',
            'custom' => 'Tùy chỉnh',
            default => $this->type
        };
    }

    public function getFrequencyDisplayAttribute(): string
    {
        return match($this->frequency) {
            'real_time' => 'Thời gian thực',
            'daily' => 'Hàng ngày',
            'weekly' => 'Hàng tuần',
            'monthly' => 'Hàng tháng',
            'quarterly' => 'Hàng quý',
            'yearly' => 'Hàng năm',
            'custom' => 'Tùy chỉnh',
            default => $this->frequency
        };
    }

    public function canBeDeleted(): bool
    {
        return !$this->is_system && $this->instances()->count() === 0;
    }

    public function canBeModified(): bool
    {
        return !$this->is_system;
    }

    public function hasPermission(User $user): bool
    {
        if ($this->is_public) {
            return true;
        }

        if (!$this->permissions) {
            return true;
        }

        // Check user permissions
        if (in_array($user->id, $this->permissions['users'] ?? [])) {
            return true;
        }

        // Check role permissions
        if (in_array($user->role, $this->permissions['roles'] ?? [])) {
            return true;
        }

        return false;
    }

    public function getLastInstanceAttribute(): ?ReportInstance
    {
        return $this->instances()->latest()->first();
    }

    public function getTotalInstancesAttribute(): int
    {
        return $this->instances()->count();
    }

    public function isScheduled(): bool
    {
        return $this->schedules()->where('is_active', true)->exists();
    }
}
