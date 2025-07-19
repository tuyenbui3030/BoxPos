<?php

namespace Packages\MaterialSuppliers\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplierContact extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_id',
        'name',
        'position',
        'department',
        'phone',
        'mobile',
        'email',
        'fax',
        'is_primary',
        'is_active',
        'responsibilities',
        'notes',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'is_active' => 'boolean',
        'responsibilities' => 'array',
    ];

    /**
     * Get the supplier that owns this contact.
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(MaterialSupplier::class, 'supplier_id');
    }

    /**
     * Scope for active contacts.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for primary contacts.
     */
    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }

    /**
     * Scope by department.
     */
    public function scopeByDepartment($query, string $department)
    {
        return $query->where('department', $department);
    }

    /**
     * Get formatted contact display.
     */
    public function getFormattedNameAttribute(): string
    {
        if ($this->position) {
            return "{$this->name} - {$this->position}";
        }
        
        return $this->name;
    }

    /**
     * Get primary phone number.
     */
    public function getPrimaryPhoneAttribute(): string
    {
        return $this->mobile ?: $this->phone ?: '';
    }

    /**
     * Check if contact is primary.
     */
    public function isPrimary(): bool
    {
        return $this->is_primary;
    }

    /**
     * Check if contact is active.
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * Get contact responsibilities as string.
     */
    public function getResponsibilitiesStringAttribute(): string
    {
        if (is_array($this->responsibilities)) {
            return implode(', ', $this->responsibilities);
        }
        
        return '';
    }
}
