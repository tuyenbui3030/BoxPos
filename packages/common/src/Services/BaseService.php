<?php

namespace Packages\Common\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Packages\Common\Contracts\RepositoryInterface;
use Packages\Common\Traits\Loggable;

abstract class BaseService
{
    use Loggable;

    protected RepositoryInterface $repository;

    public function __construct(RepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Create a new record with transaction management
     */
    public function create(array $data): Model
    {
        $this->logActivity('service_create_started', [
            'service' => static::class,
            'data_keys' => array_keys($data),
        ]);

        return DB::transaction(function () use ($data) {
            try {
                $model = $this->repository->create($data);

                $this->logActivity('service_create_completed', [
                    'service' => static::class,
                    'model_id' => $model->getKey(),
                    'model_class' => get_class($model),
                ]);

                return $model;
            } catch (\Exception $e) {
                $this->logError($e, [
                    'service' => static::class,
                    'action' => 'create',
                    'data' => $data,
                ]);

                throw $e;
            }
        });
    }

    /**
     * Update a record with transaction management
     */
    public function update(Model $model, array $data): Model
    {
        $this->logActivity('service_update_started', [
            'service' => static::class,
            'model_id' => $model->getKey(),
            'model_class' => get_class($model),
            'data_keys' => array_keys($data),
        ]);

        return DB::transaction(function () use ($model, $data) {
            try {
                $this->repository->update($model, $data);
                $model->refresh();

                $this->logActivity('service_update_completed', [
                    'service' => static::class,
                    'model_id' => $model->getKey(),
                    'model_class' => get_class($model),
                ]);

                return $model;
            } catch (\Exception $e) {
                $this->logError($e, [
                    'service' => static::class,
                    'action' => 'update',
                    'model_id' => $model->getKey(),
                    'data' => $data,
                ]);

                throw $e;
            }
        });
    }

    /**
     * Delete a record with transaction management
     */
    public function delete(Model $model): bool
    {
        $this->logActivity('service_delete_started', [
            'service' => static::class,
            'model_id' => $model->getKey(),
            'model_class' => get_class($model),
        ]);

        return DB::transaction(function () use ($model) {
            try {
                $result = $this->repository->delete($model);

                $this->logActivity('service_delete_completed', [
                    'service' => static::class,
                    'model_id' => $model->getKey(),
                    'model_class' => get_class($model),
                    'result' => $result,
                ]);

                return $result;
            } catch (\Exception $e) {
                $this->logError($e, [
                    'service' => static::class,
                    'action' => 'delete',
                    'model_id' => $model->getKey(),
                ]);

                throw $e;
            }
        });
    }

    /**
     * Find a record by ID
     */
    public function find(int $id): ?Model
    {
        $this->logActivity('service_find', [
            'service' => static::class,
            'id' => $id,
        ]);

        return $this->repository->find($id);
    }

    /**
     * Find a record by ID or fail
     */
    public function findOrFail(int $id): Model
    {
        $this->logActivity('service_find_or_fail', [
            'service' => static::class,
            'id' => $id,
        ]);

        return $this->repository->findOrFail($id);
    }

    /**
     * Get all records
     */
    public function all()
    {
        $this->logActivity('service_all', [
            'service' => static::class,
        ]);

        return $this->repository->all();
    }

    /**
     * Get paginated records
     */
    public function paginate(int $perPage = 15)
    {
        $this->logActivity('service_paginate', [
            'service' => static::class,
            'per_page' => $perPage,
        ]);

        return $this->repository->paginate($perPage);
    }

    /**
     * Execute a callback within a database transaction
     */
    protected function executeInTransaction(callable $callback)
    {
        $this->logActivity('service_transaction_started', [
            'service' => static::class,
        ]);

        return DB::transaction(function () use ($callback) {
            try {
                $result = $callback();

                $this->logActivity('service_transaction_completed', [
                    'service' => static::class,
                ]);

                return $result;
            } catch (\Exception $e) {
                $this->logError($e, [
                    'service' => static::class,
                    'action' => 'transaction',
                ]);

                throw $e;
            }
        });
    }

    /**
     * Log performance metrics for operations
     */
    protected function logPerformance(string $operation, float $startTime, array $context = []): void
    {
        $duration = microtime(true) - $startTime;

        $this->logPerformanceMetric($operation, $duration, array_merge($context, [
            'service' => static::class,
        ]));
    }
}