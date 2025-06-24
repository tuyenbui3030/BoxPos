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
     * Search customers by criteria
     *
     * @param array $criteria
     * @return Collection
     */
    public function searchCustomers(array $criteria): Collection
    {
        return $this->customerRepository->search($criteria);
    }

    /**
     * Get active customers
     *
     * @return Collection
     */
    public function getActiveCustomers(): Collection
    {
        return $this->customerRepository->getActive();
    }
}
