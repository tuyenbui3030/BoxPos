<?php

namespace Packages\Customer\Repositories;

use Packages\Customer\Models\Customer;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Customer Repository Class
 * 
 * Handles all data access operations for customers.
 * Provides a clean interface for database operations.
 */
class CustomerRepository
{
    /**
     * Customer model instance
     *
     * @var Customer
     */
    protected Customer $model;

    /**
     * Constructor
     *
     * @param Customer $model
     */
    public function __construct(Customer $model)
    {
        $this->model = $model;
    }

    /**
     * Get all customers
     *
     * @return Collection
     */
    public function all(): Collection
    {
        return $this->model->all();
    }

    /**
     * Get customers with pagination
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->paginate($perPage);
    }

    /**
     * Find customer by ID
     *
     * @param int $id
     * @return Customer|null
     */
    public function findById(int $id): ?Customer
    {
        return $this->model->find($id);
    }

    /**
     * Create new customer
     *
     * @param array $data
     * @return Customer
     */
    public function create(array $data): Customer
    {
        return $this->model->create($data);
    }

    /**
     * Update customer
     *
     * @param Customer $customer
     * @param array $data
     * @return Customer
     */
    public function update(Customer $customer, array $data): Customer
    {
        $customer->update($data);
        return $customer->fresh();
    }

    /**
     * Delete customer
     *
     * @param Customer $customer
     * @return bool
     */
    public function delete(Customer $customer): bool
    {
        return $customer->delete();
    }

    /**
     * Search customers by criteria
     *
     * @param array $criteria
     * @return Collection
     */
    public function search(array $criteria): Collection
    {
        $query = $this->model->newQuery();

        if (isset($criteria['name'])) {
            $query->where('name', 'like', '%' . $criteria['name'] . '%');
        }

        if (isset($criteria['email'])) {
            $query->where('email', 'like', '%' . $criteria['email'] . '%');
        }

        if (isset($criteria['phone'])) {
            $query->where('phone', 'like', '%' . $criteria['phone'] . '%');
        }

        if (isset($criteria['status'])) {
            $query->where('status', $criteria['status']);
        }

        return $query->get();
    }

    /**
     * Get active customers
     *
     * @return Collection
     */
    public function getActive(): Collection
    {
        return $this->model->where('status', 'active')->get();
    }

    /**
     * Get customers by status
     *
     * @param string $status
     * @return Collection
     */
    public function getByStatus(string $status): Collection
    {
        return $this->model->where('status', $status)->get();
    }
}
