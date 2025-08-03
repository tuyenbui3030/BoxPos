<?php

namespace Packages\MaterialCatalog\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Packages\Store\Models\Store;
use Packages\User\Models\User;
use Packages\Tenant\Traits\HasTenantScope;

class BuildingMaterial extends Model
{
    use HasFactory, HasTenantScope;

    protected $fillable = [
        'store_id',
        'category_id',
        'primary_unit_id',
        'material_code',
        'name',
        'slug',
        'description',
        'short_description',
        'brand',
        'model',
        'origin_country',
        'images',
        'barcode',
        'qr_code',
        'weight_per_unit',
        'dimensions',
        'is_hazardous',
        'storage_requirements',
        'quality_standards',
        'certifications',
        'expiry_date',
        'shelf_life_days',
        'requires_quality_check',
        'is_active',
        'is_featured',
        'track_serial_numbers',
        'track_batch_numbers',
        'technical_specs',
        'tags',
        'internal_notes',
        'created_by',
    ];

    protected $casts = [
        'images' => 'array',
        'weight_per_unit' => 'decimal:3',
        'dimensions' => 'array',
        'is_hazardous' => 'boolean',
        'storage_requirements' => 'array',
        'quality_standards' => 'array',
        'certifications' => 'array',
        'expiry_date' => 'date',
        'shelf_life_days' => 'integer',
        'requires_quality_check' => 'boolean',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'track_serial_numbers' => 'boolean',
        'track_batch_numbers' => 'boolean',
        'technical_specs' => 'array',
        'tags' => 'array',
    ];

    /**
     * Get the store that owns this material.
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    /**
     * Get the category this material belongs to.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(MaterialCategory::class, 'category_id');
    }

    /**
     * Get the primary unit for this material.
     */
    public function primaryUnit(): BelongsTo
    {
        return $this->belongsTo(MaterialUnit::class, 'primary_unit_id');
    }

    /**
     * Get the user who created this material.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get specifications for this material.
     */
    public function specifications(): HasMany
    {
        return $this->hasMany(MaterialSpecification::class, 'material_id')
                    ->orderBy('sort_order');
    }

    /**
     * Scope for active materials.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for featured materials.
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope by category.
     */
    public function scopeByCategory($query, int $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    /**
     * Scope by brand.
     */
    public function scopeByBrand($query, string $brand)
    {
        return $query->where('brand', $brand);
    }

    /**
     * Scope to search materials.
     */
    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('material_code', 'like', "%{$search}%")
              ->orWhere('name', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%")
              ->orWhere('brand', 'like', "%{$search}%")
              ->orWhere('model', 'like', "%{$search}%")
              ->orWhere('barcode', 'like', "%{$search}%");
        });
    }

    /**
     * Get primary image URL.
     */
    public function getPrimaryImageUrlAttribute(): string
    {
        if ($this->images && count($this->images) > 0) {
            $firstImage = $this->images[0];
            
            // Check if it's R2 format (array with url key)
            if (is_array($firstImage) && isset($firstImage['url'])) {
                return $firstImage['url'];
            }
            
            // Check if it's already a full URL (R2 or external)
            if (filter_var($firstImage, FILTER_VALIDATE_URL)) {
                return $firstImage;
            }
            
            // Fallback to local storage
            return asset('storage/' . $firstImage);
        }
        
        return asset('images/default-material.png');
    }

    /**
     * Get all image URLs.
     */
    public function getImageUrlsAttribute(): array
    {
        if (!$this->images || !is_array($this->images)) {
            return [];
        }

        return collect($this->images)->map(function ($image) {
            // Check if it's R2 format (array with url key)
            if (is_array($image) && isset($image['url'])) {
                return $image['url'];
            }
            
            // Check if it's already a full URL (R2 or external)
            if (filter_var($image, FILTER_VALIDATE_URL)) {
                return $image;
            }
            
            // Fallback to local storage
            return asset('storage/' . $image);
        })->toArray();
    }

    /**
     * Check if material is active.
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * Check if material is featured.
     */
    public function isFeatured(): bool
    {
        return $this->is_featured;
    }

    /**
     * Check if material is hazardous.
     */
    public function isHazardous(): bool
    {
        return $this->is_hazardous;
    }

    /**
     * Generate unique material code.
     */
    public static function generateMaterialCode(int $storeId): string
    {
        $lastMaterial = static::where('store_id', $storeId)
                             ->orderBy('id', 'desc')
                             ->first();
        
        $number = $lastMaterial ? ($lastMaterial->id + 1) : 1;
        return 'MAT' . str_pad($number, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Generate unique slug.
     */
    public static function generateSlug(string $name, int $storeId, ?int $excludeId = null): string
    {
        $baseSlug = \Str::slug($name);
        $slug = $baseSlug;
        $counter = 1;

        $query = static::where('store_id', $storeId)->where('slug', $slug);
        
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        while ($query->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
            $query = static::where('store_id', $storeId)->where('slug', $slug);
            
            if ($excludeId) {
                $query->where('id', '!=', $excludeId);
            }
        }

        return $slug;
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return \Packages\MaterialCatalog\Database\Factories\BuildingMaterialFactory::new();
    }
}
