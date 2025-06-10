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
     * Search customers by criteria using Builder Pattern
     *
     * @param array $criteria
     * @return Collection
     */
    public function search(array $criteria): Collection
    {
        $query = $this->model->query();

        // Use Builder Pattern methods
        if (!empty($criteria['search'])) {
            $query->search($criteria['search']);
        }

        if (!empty($criteria['type'])) {
            $query->byType($criteria['type']);
        }

        if (!empty($criteria['group'])) {
            $query->byGroup($criteria['group']);
        }

        if (!empty($criteria['gender'])) {
            $query->byGender($criteria['gender']);
        }

        if (!empty($criteria['has_debt'])) {
            $query->withDebt();
        }

        if (!empty($criteria['min_sales'])) {
            $query->salesAbove($criteria['min_sales']);
        }

        if (!empty($criteria['max_debt'])) {
            $query->debtBelow($criteria['max_debt']);
        }

        if (!empty($criteria['active_days'])) {
            $query->activeInLastDays($criteria['active_days']);
        }

        return $query->orderByName()->get();
    }

    /**
     * Get VIP customers using Builder Pattern
     *
     * @param float $minSales
     * @param float $maxDebt
     * @return Collection
     */
    public function getVipCustomers(float $minSales = 10000, float $maxDebt = 1000): Collection
    {
        return $this->model->query()
            ->vipCustomers($minSales, $maxDebt)
            ->withCreator()
            ->orderByHighestSales()
            ->get();
    }

    /**
     * Get at-risk customers using Builder Pattern
     *
     * @param float $minDebt
     * @param int $inactiveDays
     * @return Collection
     */
    public function getAtRiskCustomers(float $minDebt = 5000, int $inactiveDays = 30): Collection
    {
        return $this->model->query()
            ->atRiskCustomers($minDebt, $inactiveDays)
            ->withCreator()
            ->orderByHighestDebt()
            ->get();
    }

    /**
     * Get customers with upcoming birthdays
     *
     * @return Collection
     */
    public function getBirthdayCustomers(): Collection
    {
        return $this->model->query()
            ->birthdayThisWeek()
            ->orderByName()
            ->get();
    }

    /**
     * Get top customers by sales
     *
     * @param int $limit
     * @return Collection
     */
    public function getTopCustomers(int $limit = 10): Collection
    {
        return $this->model->query()
            ->topCustomers($limit)
            ->withCreator()
            ->get();
    }

    /**
     * Get customers with debt
     *
     * @param string $orderBy
     * @return Collection
     */
    public function getCustomersWithDebt(string $orderBy = 'highest_debt'): Collection
    {
        $query = $this->model->query()->withDebt();

        return match ($orderBy) {
            'highest_debt' => $query->orderByHighestDebt()->get(),
            'recent_transaction' => $query->orderByRecentTransaction()->get(),
            'name' => $query->orderByName()->get(),
            default => $query->get(),
        };
    }

    /**
     * Get active customers using Builder Pattern
     *
     * @param int $days
     * @return Collection
     */
    public function getActiveCustomers(int $days = 30): Collection
    {
        return $this->model->query()
            ->activeInLastDays($days)
            ->orderByRecentTransaction()
            ->get();
    }

    /**
     * Get inactive customers
     *
     * @param int $days
     * @return Collection
     */
    public function getInactiveCustomers(int $days = 30): Collection
    {
        return $this->model->query()
            ->inactiveInLastDays($days)
            ->orderByName()
            ->get();
    }

    /**
     * Get customers by group with filters
     *
     * @param string $group
     * @param array $filters
     * @return Collection
     */
    public function getCustomersByGroup(string $group, array $filters = []): Collection
    {
        $query = $this->model->query()->byGroup($group);

        // Apply additional filters
        if (!empty($filters['gender'])) {
            $query->byGender($filters['gender']);
        }

        if (!empty($filters['min_sales'])) {
            $query->salesAbove($filters['min_sales']);
        }

        if (!empty($filters['max_debt'])) {
            $query->debtBelow($filters['max_debt']);
        }

        if (!empty($filters['active_days'])) {
            $query->activeInLastDays($filters['active_days']);
        }

        return $query->orderByName()->get();
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
