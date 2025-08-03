<?php

namespace Packages\Common\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Packages\Common\Contracts\RepositoryInterface;
use Packages\Common\Traits\Loggable;

abstract class BaseRepository implements RepositoryInterface
{
    use Loggable;

    protected Model $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * Get all records with optional store scoping
     */
    public function all(array $columns = ['*']): Collection
    {
        $this->logActivity('repository_all_query', [
            'model' => get_class($this->model),
            'columns' => $columns,
        ]);

        return $this->applyStoreScope($this->model->newQuery())
            ->get($columns);
    }

    /**
     * Find a record by ID with store scoping
     */
    public function find(int $id, array $columns = ['*']): ?Model
    {
        $this->logActivity('repository_find_query', [
            'model' => get_class($this->model),
            'id' => $id,
            'columns' => $columns,
        ]);

        return $this->applyStoreScope($this->model->newQuery())
            ->find($id, $columns);
    }

    /**
     * Find a record by ID or fail with store scoping
     */
    public function findOrFail(int $id, array $columns = ['*']): Model
    {
        $this->logActivity('repository_find_or_fail_query', [
            'model' => get_class($this->model),
            'id' => $id,
            'columns' => $columns,
        ]);

        return $this->applyStoreScope($this->model->newQuery())
            ->findOrFail($id, $columns);
    }

    /**
     * Create a new record
     */
    public function create(array $data): Model
    {
        $this->logActivity('repository_create', [
            'model' => get_class($this->model),
            'data_keys' => array_keys($data),
        ]);

        // Automatically add store_id if model supports it
        if ($this->model->isFillable('store_id') && !isset($data['store_id'])) {
            $data['store_id'] = $this->getCurrentStoreId();
        }

        return $this->model->create($data);
    }

    /**
     * Update a record
     */
    public function update(Model $model, array $data): bool
    {
        $this->logActivity('repository_update', [
            'model' => get_class($model),
            'id' => $model->getKey(),
            'data_keys' => array_keys($data),
        ]);

        return $model->update($data);
    }

    /**
     * Delete a record
     */
    public function delete(Model $model): bool
    {
        $this->logActivity('repository_delete', [
            'model' => get_class($model),
            'id' => $model->getKey(),
        ]);

        return $model->delete();
    }

    /**
     * Get paginated results with store scoping
     */
    public function paginate(int $perPage = 15, array $columns = ['*']): LengthAwarePaginator
    {
        $this->logActivity('repository_paginate_query', [
            'model' => get_class($this->model),
            'per_page' => $perPage,
            'columns' => $columns,
        ]);

        return $this->applyStoreScope($this->model->newQuery())
            ->paginate($perPage, $columns);
    }

    /**
     * Find records by criteria with store scoping
     */
    public function findWhere(array $criteria, array $columns = ['*']): Collection
    {
        $this->logActivity('repository_find_where_query', [
            'model' => get_class($this->model),
            'criteria' => array_keys($criteria),
            'columns' => $columns,
        ]);

        $query = $this->applyStoreScope($this->model->newQuery());

        foreach ($criteria as $field => $value) {
            $query->where($field, $value);
        }

        return $query->get($columns);
    }

    /**
     * Apply store scope to query if applicable
     */
    protected function applyStoreScope(Builder $query): Builder
    {
        if ($this->model->isFillable('store_id') && $this->getCurrentStoreId()) {
            $query->where('store_id', $this->getCurrentStoreId());
        }

        return $query;
    }

    /**
     * Get current store ID from authenticated user
     */
    protected function getCurrentStoreId(): ?int
    {
        return auth()->check() ? auth()->user()->current_store_id : null;
    }

    /**
     * Get a new query builder instance
     */
    public function newQuery(): Builder
    {
        return $this->applyStoreScope($this->model->newQuery());
    }

    /**
     * Count records with store scoping
     */
    public function count(): int
    {
        $this->logActivity('repository_count_query', [
            'model' => get_class($this->model),
        ]);

        return $this->applyStoreScope($this->model->newQuery())->count();
    }
}