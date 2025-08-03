<?php

namespace Packages\Common\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Packages\Common\Traits\Loggable;

abstract class StoreAwareModel extends Model
{
    use Loggable;

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Boot the model and apply global scopes
     */
    protected static function booted(): void
    {
        // Apply store scope automatically
        static::addGlobalScope('store', function (Builder $builder) {
            if (auth()->check() && auth()->user()->current_store_id) {
                $builder->where('store_id', auth()->user()->current_store_id);
            }
        });

        // Automatically set store_id on creation
        static::creating(function (self $model) {
            if ($model->isFillable('store_id') && !$model->store_id && auth()->check()) {
                $model->store_id = auth()->user()->current_store_id;
            }

            if ($model->isFillable('created_by') && !$model->created_by && auth()->check()) {
                $model->created_by = auth()->id();
            }

            // Log model creation
            $model->logActivity('model_creating', [
                'model' => get_class($model),
                'store_id' => $model->store_id,
                'user_id' => auth()->id(),
            ]);
        });

        // Log model updates
        static::updating(function (self $model) {
            if ($model->isFillable('updated_by') && auth()->check()) {
                $model->updated_by = auth()->id();
            }

            // Log model update
            $model->logActivity('model_updating', [
                'model' => get_class($model),
                'id' => $model->getKey(),
                'store_id' => $model->store_id,
                'user_id' => auth()->id(),
                'changed_attributes' => array_keys($model->getDirty()),
            ]);
        });

        // Log model deletion
        static::deleting(function (self $model) {
            $model->logActivity('model_deleting', [
                'model' => get_class($model),
                'id' => $model->getKey(),
                'store_id' => $model->store_id,
                'user_id' => auth()->id(),
            ]);
        });

        // Log model creation completion
        static::created(function (self $model) {
            $model->logActivity('model_created', [
                'model' => get_class($model),
                'id' => $model->getKey(),
                'store_id' => $model->store_id,
                'user_id' => auth()->id(),
            ]);
        });

        // Log model update completion
        static::updated(function (self $model) {
            $model->logActivity('model_updated', [
                'model' => get_class($model),
                'id' => $model->getKey(),
                'store_id' => $model->store_id,
                'user_id' => auth()->id(),
            ]);
        });

        // Log model deletion completion
        static::deleted(function (self $model) {
            $model->logActivity('model_deleted', [
                'model' => get_class($model),
                'id' => $model->getKey(),
                'store_id' => $model->store_id,
                'user_id' => auth()->id(),
            ]);
        });
    }

    /**
     * Get query without store scope
     */
    public static function withoutStoreScope(): Builder
    {
        return static::withoutGlobalScope('store');
    }

    /**
     * Get query for specific store
     */
    public static function forStore(int $storeId): Builder
    {
        return static::withoutGlobalScope('store')->where('store_id', $storeId);
    }

    /**
     * Check if model belongs to current store
     */
    public function belongsToCurrentStore(): bool
    {
        if (!auth()->check() || !auth()->user()->current_store_id) {
            return false;
        }

        return $this->store_id === auth()->user()->current_store_id;
    }

    /**
     * Check if model belongs to specific store
     */
    public function belongsToStore(int $storeId): bool
    {
        return $this->store_id === $storeId;
    }

    /**
     * Scope query to current store
     */
    public function scopeCurrentStore(Builder $query): Builder
    {
        if (auth()->check() && auth()->user()->current_store_id) {
            return $query->where('store_id', auth()->user()->current_store_id);
        }

        return $query;
    }

    /**
     * Scope query to specific store
     */
    public function scopeForStore(Builder $query, int $storeId): Builder
    {
        return $query->where('store_id', $storeId);
    }

    /**
     * Get the store relationship (to be implemented by child classes if needed)
     */
    public function store()
    {
        return $this->belongsTo(\App\Models\Store::class);
    }

    /**
     * Get the creator relationship
     */
    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    /**
     * Get the updater relationship
     */
    public function updater()
    {
        return $this->belongsTo(\App\Models\User::class, 'updated_by');
    }
}