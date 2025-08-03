<?php

namespace Packages\Common\Contracts;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

interface RepositoryInterface
{
    /**
     * Get all records
     */
    public function all(array $columns = ['*']): Collection;

    /**
     * Find a record by ID
     */
    public function find(int $id, array $columns = ['*']): ?Model;

    /**
     * Find a record by ID or fail
     */
    public function findOrFail(int $id, array $columns = ['*']): Model;

    /**
     * Create a new record
     */
    public function create(array $data): Model;

    /**
     * Update a record
     */
    public function update(Model $model, array $data): bool;

    /**
     * Delete a record
     */
    public function delete(Model $model): bool;

    /**
     * Get paginated results
     */
    public function paginate(int $perPage = 15, array $columns = ['*']): LengthAwarePaginator;

    /**
     * Find records by criteria
     */
    public function findWhere(array $criteria, array $columns = ['*']): Collection;

    /**
     * Get a new query builder instance
     */
    public function newQuery(): Builder;

    /**
     * Count records
     */
    public function count(): int;
}