<?php

namespace Packages\CashManagement\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Packages\Store\Models\Store;
use Packages\Tenant\Traits\HasTenantScope;

class CashCategory extends Model
{
    use HasFactory, HasTenantScope;

    protected $fillable = [
        'store_id',
        'parent_id',
        'code',
        'name',
        'description',
        'type',
        'color',
        'is_active',
        'is_system',
        'sort_order',
        'metadata',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_system' => 'boolean',
        'metadata' => 'array',
    ];

    // Relationships
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(CashCategory::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(CashCategory::class, 'parent_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(CashTransaction::class, 'category_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeRootCategories($query)
    {
        return $query->whereNull('parent_id');
    }

    public function scopeSystemCategories($query)
    {
        return $query->where('is_system', true);
    }

    public function scopeUserCategories($query)
    {
        return $query->where('is_system', false);
    }

    // Helper methods
    public function getHierarchyLevelAttribute(): int
    {
        $level = 0;
        $parent = $this->parent;
        
        while ($parent) {
            $level++;
            $parent = $parent->parent;
        }
        
        return $level;
    }

    public function getFullNameAttribute(): string
    {
        $names = [$this->name];
        $parent = $this->parent;
        
        while ($parent) {
            array_unshift($names, $parent->name);
            $parent = $parent->parent;
        }
        
        return implode(' > ', $names);
    }

    public function getAllChildrenIds(): array
    {
        $childrenIds = [];
        
        foreach ($this->children as $child) {
            $childrenIds[] = $child->id;
            $childrenIds = array_merge($childrenIds, $child->getAllChildrenIds());
        }
        
        return $childrenIds;
    }

    public function canBeDeleted(): bool
    {
        return !$this->is_system && 
               $this->transactions()->count() === 0 && 
               $this->children()->count() === 0;
    }

    public function isIncomeCategory(): bool
    {
        return $this->type === 'income';
    }

    public function isExpenseCategory(): bool
    {
        return $this->type === 'expense';
    }

    public function isTransferCategory(): bool
    {
        return $this->type === 'transfer';
    }
}
