<?php

namespace Packages\Customer\Services;

use Packages\Customer\Models\Customer;
use Packages\Customer\Repositories\CustomerRepository;
use Packages\Customer\Events\CustomerCreated;
use Packages\Customer\Events\CustomerUpdated;
use Packages\Customer\Events\CustomerDeleted;
use Packages\Customer\Exceptions\CustomerNotFoundException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Customer Service Class
 * 
 * Handles all business logic related to customer operations.
 * Acts as a layer between controllers and data access.
 */
class CustomerService
{
    /**
     * Customer repository instance
     *
     * @var CustomerRepository
     */
    protected CustomerRepository $customerRepository;

    /**
     * Constructor
     *
     * @param CustomerRepository $customerRepository
     */
    public function __construct(CustomerRepository $customerRepository)
    {
        $this->customerRepository = $customerRepository;
    }

    /**
     * Get all customers with pagination
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getAllCustomers(int $perPage = 15): LengthAwarePaginator
    {
        return $this->customerRepository->paginate($perPage);
    }

    /**
     * Get customer by ID
     *
     * @param int $id
     * @return Customer
     * @throws CustomerNotFoundException
     */
    public function getCustomerById(int $id): Customer
    {
        $customer = $this->customerRepository->findById($id);
        
        if (!$customer) {
            throw new CustomerNotFoundException("Customer with ID {$id} not found");
        }

        return $customer;
    }

    /**
     * Create a new customer
     *
     * @param array $data
     * @return Customer
     */
    public function createCustomer(array $data): Customer
    {
        $customer = $this->customerRepository->create($data);
        
        // Dispatch customer created event
        event(new CustomerCreated($customer));
        
        return $customer;
    }

    /**
     * Update existing customer
     *
     * @param int $id
     * @param array $data
     * @return Customer
     * @throws CustomerNotFoundException
     */
    public function updateCustomer(int $id, array $data): Customer
    {
        $customer = $this->getCustomerById($id);
        $updatedCustomer = $this->customerRepository->update($customer, $data);
        
        // Dispatch customer updated event
        event(new CustomerUpdated($updatedCustomer));
        
        return $updatedCustomer;
    }

    /**
     * Delete customer
     *
     * @param int $id
     * @return bool
     * @throws CustomerNotFoundException
     */
    public function deleteCustomer(int $id): bool
    {
        $customer = $this->getCustomerById($id);
        $result = $this->customerRepository->delete($customer);
        
        if ($result) {
            // Dispatch customer deleted event
            event(new CustomerDeleted($customer));
        }
        
        return $result;
    }

    /**
     * Search customers by criteria using Builder Pattern
     *
     * @param string $search Search term
     * @param array $filters Additional filters
     * @param int $perPage Items per page
     * @return LengthAwarePaginator
     */
    public function searchCustomers(string $search = '', array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        // Prepare criteria for repository
        $criteria = array_merge($filters, ['search' => $search]);
        
        // Use repository method but add pagination
        $query = Customer::query();

        // Apply search if provided
        if (!empty($search)) {
            $query->search($search);
        }

        // Apply filters dynamically
        if (!empty($filters['type'])) {
            $query->byType($filters['type']);
        }

        if (!empty($filters['group'])) {
            $query->byGroup($filters['group']);
        }

        if (!empty($filters['gender'])) {
            $query->byGender($filters['gender']);
        }

        if (isset($filters['has_debt']) && $filters['has_debt']) {
            $query->withDebt();
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

        return $query->orderByName()->paginate($perPage);
    }

    /**
     * Get VIP customers (high sales, low debt)
     *
     * @param float $minSales
     * @param float $maxDebt
     * @return Collection
     */
    public function getVipCustomers(float $minSales = 10000, float $maxDebt = 1000): Collection
    {
        return $this->customerRepository->getVipCustomers($minSales, $maxDebt);
    }

    /**
     * Get at-risk customers (high debt, inactive)
     *
     * @param float $minDebt
     * @param int $inactiveDays
     * @return Collection
     */
    public function getAtRiskCustomers(float $minDebt = 5000, int $inactiveDays = 30): Collection
    {
        return $this->customerRepository->getAtRiskCustomers($minDebt, $inactiveDays);
    }

    /**
     * Get customers with upcoming birthdays
     *
     * @return Collection
     */
    public function getUpcomingBirthdays(): Collection
    {
        return $this->customerRepository->getBirthdayCustomers();
    }

    /**
     * Get top customers by sales
     *
     * @param int $limit
     * @return Collection
     */
    public function getTopCustomers(int $limit = 10): Collection
    {
        return $this->customerRepository->getTopCustomers($limit);
    }

    /**
     * Get customers with debt
     *
     * @param string|null $orderBy
     * @return Collection
     */
    public function getCustomersWithDebt(string $orderBy = 'highest_debt'): Collection
    {
        return $this->customerRepository->getCustomersWithDebt($orderBy);
    }

    /**
     * Get active customers using Builder Pattern
     *
     * @param int $days Days to consider active
     * @return Collection
     */
    public function getActiveCustomers(int $days = 30): Collection
    {
        return $this->customerRepository->getActiveCustomers($days);
    }

    /**
     * Get inactive customers (no transactions in last N days)
     *
     * @param int $days
     * @return Collection
     */
    public function getInactiveCustomers(int $days = 30): Collection
    {
        return $this->customerRepository->getInactiveCustomers($days);
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
        return $this->customerRepository->getCustomersByGroup($group, $filters);
    }
}
