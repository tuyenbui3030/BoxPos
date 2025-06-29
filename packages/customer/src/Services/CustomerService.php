<?php

namespace Packages\Customer\Services;

use Packages\Customer\Models\Customer;
use Packages\Customer\Repositories\CustomerRepository;
use Packages\Customer\Events\CustomerCreated;
use Packages\Customer\Events\CustomerUpdated;
use Packages\Customer\Events\CustomerDeleted;
use Packages\Customer\Exceptions\CustomerNotFoundException;
use Packages\Log\Traits\Loggable;
use Packages\Tenant\Services\TenantService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

/**
 * Customer Service Class
 *
 * Handles all business logic related to customer operations.
 * Acts as a layer between controllers and data access.
 */
class CustomerService
{
    use Loggable; // ⚠️ MANDATORY: Use Loggable trait

    /**
     * Customer repository instance
     *
     * @var CustomerRepository
     */
    protected CustomerRepository $customerRepository;

    /**
     * Tenant service instance
     *
     * @var TenantService
     */
    protected TenantService $tenantService;

    /**
     * Constructor
     *
     * @param CustomerRepository $customerRepository
     * @param TenantService $tenantService
     */
    public function __construct(CustomerRepository $customerRepository, TenantService $tenantService)
    {
        $this->customerRepository = $customerRepository;
        $this->tenantService = $tenantService;
    }

    /**
     * Get all customers with pagination
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getAllCustomers(int $perPage = 15): LengthAwarePaginator
    {
        // ⚠️ MANDATORY: Log business operation
        $this->logActivity('customers_list_viewed', [
            'user_id' => Auth::id(),
            'per_page' => $perPage,
            'action' => 'get_all_customers',
        ]);

        try {
            return $this->customerRepository->paginate($perPage);
        } catch (\Exception $e) {
            // ⚠️ MANDATORY: Log errors with context
            $this->logError($e, [
                'action' => 'get_all_customers',
                'user_id' => Auth::id(),
                'per_page' => $perPage,
            ]);

            throw $e;
        }
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
        // ⚠️ MANDATORY: Log business operation
        $this->logActivity('customer_viewed', [
            'user_id' => Auth::id(),
            'customer_id' => $id,
            'action' => 'get_customer_by_id',
        ]);

        try {
            $customer = $this->customerRepository->findById($id);

            if (!$customer) {
                throw new CustomerNotFoundException("Customer with ID {$id} not found");
            }

            return $customer;
        } catch (CustomerNotFoundException $e) {
            // ⚠️ MANDATORY: Log business exception
            $this->logError($e, [
                'action' => 'get_customer_by_id',
                'user_id' => Auth::id(),
                'customer_id' => $id,
                'error_type' => 'customer_not_found',
            ]);

            throw $e;
        } catch (\Exception $e) {
            // ⚠️ MANDATORY: Log errors with context
            $this->logError($e, [
                'action' => 'get_customer_by_id',
                'user_id' => Auth::id(),
                'customer_id' => $id,
            ]);

            throw $e;
        }
    }

    /**
     * Create a new customer
     *
     * @param array $data
     * @return Customer
     */
    public function createCustomer(array $data): Customer
    {
        $startTime = microtime(true);

        // ⚠️ MANDATORY: Log operation start
        $this->logActivity('customer_creation_started', [
            'user_id' => Auth::id(),
            'data_keys' => array_keys($data),
            'action' => 'create_customer',
        ]);

        DB::beginTransaction();

        try {
            // Automatically set store_id from current tenant context
            if (!isset($data['store_id'])) {
                $currentStoreId = $this->tenantService->getCurrentStoreId();
                if ($currentStoreId) {
                    $data['store_id'] = $currentStoreId;
                }
            }

            // Set created_by if not provided
            if (!isset($data['created_by']) && Auth::id()) {
                $data['created_by'] = Auth::id();
            }

            $customer = $this->customerRepository->create($data);

            // ⚠️ MANDATORY: Log successful operation
            $this->logActivity('customer_created', [
                'customer_id' => $customer->id,
                'customer_email' => $customer->email ?? null,
                'customer_name' => $customer->name ?? null,
                'user_id' => Auth::id(),
                'action' => 'create_customer',
            ]);

            // ⚠️ MANDATORY: Log model event
            $this->logModelEvent('created', $customer, [
                'created_fields' => array_keys($data),
                'user_id' => Auth::id(),
            ]);

            // ⚠️ MANDATORY: Log operation performance
            $this->logOperationPerformance('customer_creation', $startTime, [
                'customer_id' => $customer->id,
                'user_id' => Auth::id(),
            ]);

            // Dispatch customer created event
            event(new CustomerCreated($customer));

            DB::commit();
            return $customer;
        } catch (\Exception $e) {
            DB::rollback();

            // ⚠️ MANDATORY: Log error with context
            $this->logError($e, [
                'action' => 'create_customer',
                'user_id' => Auth::id(),
                'data' => array_diff_key($data, array_flip(['password'])), // Exclude sensitive data
            ]);

            throw $e;
        }
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
        $startTime = microtime(true);

        // ⚠️ MANDATORY: Log operation start
        $this->logActivity('customer_update_started', [
            'user_id' => Auth::id(),
            'customer_id' => $id,
            'data_keys' => array_keys($data),
            'action' => 'update_customer',
        ]);

        DB::beginTransaction();

        try {
            $customer = $this->getCustomerById($id);
            $originalData = $customer->toArray();

            $updatedCustomer = $this->customerRepository->update($customer, $data);

            // ⚠️ MANDATORY: Log successful operation
            $this->logActivity('customer_updated', [
                'customer_id' => $updatedCustomer->id,
                'customer_email' => $updatedCustomer->email ?? null,
                'customer_name' => $updatedCustomer->name ?? null,
                'user_id' => Auth::id(),
                'updated_fields' => array_keys($data),
                'action' => 'update_customer',
            ]);

            // ⚠️ MANDATORY: Log model event with changes
            $this->logModelEvent('updated', $updatedCustomer, [
                'updated_fields' => array_keys($data),
                'original_data' => array_intersect_key($originalData, $data),
                'new_data' => array_intersect_key($updatedCustomer->toArray(), $data),
                'user_id' => Auth::id(),
            ]);

            // ⚠️ MANDATORY: Log operation performance
            $this->logOperationPerformance('customer_update', $startTime, [
                'customer_id' => $updatedCustomer->id,
                'user_id' => Auth::id(),
            ]);

            // Dispatch customer updated event
            event(new CustomerUpdated($updatedCustomer));

            DB::commit();
            return $updatedCustomer;
        } catch (\Exception $e) {
            DB::rollback();

            // ⚠️ MANDATORY: Log error with context
            $this->logError($e, [
                'action' => 'update_customer',
                'user_id' => Auth::id(),
                'customer_id' => $id,
                'data' => array_diff_key($data, array_flip(['password'])), // Exclude sensitive data
            ]);

            throw $e;
        }
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
        $startTime = microtime(true);

        // ⚠️ MANDATORY: Log operation start
        $this->logActivity('customer_deletion_started', [
            'user_id' => Auth::id(),
            'customer_id' => $id,
            'action' => 'delete_customer',
        ]);

        DB::beginTransaction();

        try {
            $customer = $this->getCustomerById($id);
            $customerData = $customer->toArray();

            $result = $this->customerRepository->delete($customer);

            if ($result) {
                // ⚠️ MANDATORY: Log successful operation
                $this->logActivity('customer_deleted', [
                    'customer_id' => $id,
                    'customer_email' => $customerData['email'] ?? null,
                    'customer_name' => $customerData['name'] ?? null,
                    'user_id' => Auth::id(),
                    'action' => 'delete_customer',
                ]);

                // ⚠️ MANDATORY: Log model event
                $this->logModelEvent('deleted', $customer, [
                    'deleted_data' => $customerData,
                    'user_id' => Auth::id(),
                ]);

                // ⚠️ MANDATORY: Log operation performance
                $this->logOperationPerformance('customer_deletion', $startTime, [
                    'customer_id' => $id,
                    'user_id' => Auth::id(),
                ]);

                // Dispatch customer deleted event
                event(new CustomerDeleted($customer));
            }

            DB::commit();
            return $result;
        } catch (\Exception $e) {
            DB::rollback();

            // ⚠️ MANDATORY: Log error with context
            $this->logError($e, [
                'action' => 'delete_customer',
                'user_id' => Auth::id(),
                'customer_id' => $id,
            ]);

            throw $e;
        }
    }

    /**
     * Search customers by criteria using Repository
     *
     * @param string $search Search term
     * @param array $filters Additional filters
     * @param int $perPage Items per page
     * @return LengthAwarePaginator
     */
    public function searchCustomers(string $search = '', array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        // ⚠️ MANDATORY: Log business operation
        $this->logActivity('customers_search_performed', [
            'user_id' => Auth::id(),
            'search_term' => $search,
            'filters' => array_filter($filters), // Only log non-empty filters
            'per_page' => $perPage,
            'action' => 'search_customers',
        ]);

        try {
            // Prepare criteria for repository
            $criteria = array_merge($filters, ['search' => $search]);

            // Repository handles the Builder Pattern, Service handles pagination
            $result = $this->customerRepository->searchWithPagination($criteria, $perPage);

            // ⚠️ MANDATORY: Log search results
            $this->logActivity('customers_search_completed', [
                'user_id' => Auth::id(),
                'search_term' => $search,
                'results_count' => $result->total(),
                'per_page' => $perPage,
                'current_page' => $result->currentPage(),
                'action' => 'search_customers',
            ]);

            return $result;
        } catch (\Exception $e) {
            // ⚠️ MANDATORY: Log error with context
            $this->logError($e, [
                'action' => 'search_customers',
                'user_id' => Auth::id(),
                'search_term' => $search,
                'filters' => $filters,
                'per_page' => $perPage,
            ]);

            throw $e;
        }
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
        // ⚠️ MANDATORY: Log business operation
        $this->logActivity('vip_customers_retrieved', [
            'user_id' => Auth::id(),
            'min_sales' => $minSales,
            'max_debt' => $maxDebt,
            'action' => 'get_vip_customers',
        ]);

        try {
            $customers = $this->customerRepository->getVipCustomers($minSales, $maxDebt);

            // ⚠️ MANDATORY: Log results
            $this->logActivity('vip_customers_found', [
                'user_id' => Auth::id(),
                'count' => $customers->count(),
                'min_sales' => $minSales,
                'max_debt' => $maxDebt,
            ]);

            return $customers;
        } catch (\Exception $e) {
            // ⚠️ MANDATORY: Log error with context
            $this->logError($e, [
                'action' => 'get_vip_customers',
                'user_id' => Auth::id(),
                'min_sales' => $minSales,
                'max_debt' => $maxDebt,
            ]);

            throw $e;
        }
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
        // ⚠️ MANDATORY: Log business operation
        $this->logActivity('at_risk_customers_analysis', [
            'user_id' => Auth::id(),
            'min_debt' => $minDebt,
            'inactive_days' => $inactiveDays,
            'action' => 'get_at_risk_customers',
        ]);

        try {
            $customers = $this->customerRepository->getAtRiskCustomers($minDebt, $inactiveDays);

            // ⚠️ MANDATORY: Log analysis results
            $this->logActivity('at_risk_customers_found', [
                'user_id' => Auth::id(),
                'count' => $customers->count(),
                'min_debt' => $minDebt,
                'inactive_days' => $inactiveDays,
            ]);

            return $customers;
        } catch (\Exception $e) {
            // ⚠️ MANDATORY: Log error with context
            $this->logError($e, [
                'action' => 'get_at_risk_customers',
                'user_id' => Auth::id(),
                'min_debt' => $minDebt,
                'inactive_days' => $inactiveDays,
            ]);

            throw $e;
        }
    }

    /**
     * Get customers with upcoming birthdays
     *
     * @return Collection
     */
    public function getUpcomingBirthdays(): Collection
    {
        // ⚠️ MANDATORY: Log business operation
        $this->logActivity('upcoming_birthdays_retrieved', [
            'user_id' => Auth::id(),
            'action' => 'get_upcoming_birthdays',
        ]);

        try {
            $customers = $this->customerRepository->getBirthdayCustomers();

            // ⚠️ MANDATORY: Log results
            $this->logActivity('upcoming_birthdays_found', [
                'user_id' => Auth::id(),
                'count' => $customers->count(),
            ]);

            return $customers;
        } catch (\Exception $e) {
            // ⚠️ MANDATORY: Log error with context
            $this->logError($e, [
                'action' => 'get_upcoming_birthdays',
                'user_id' => Auth::id(),
            ]);

            throw $e;
        }
    }

    /**
     * Get top customers by sales
     *
     * @param int $limit
     * @return Collection
     */
    public function getTopCustomers(int $limit = 10): Collection
    {
        // ⚠️ MANDATORY: Log business operation
        $this->logActivity('top_customers_analysis', [
            'user_id' => Auth::id(),
            'limit' => $limit,
            'action' => 'get_top_customers',
        ]);

        try {
            $customers = $this->customerRepository->getTopCustomers($limit);

            // ⚠️ MANDATORY: Log analysis results
            $this->logActivity('top_customers_found', [
                'user_id' => Auth::id(),
                'count' => $customers->count(),
                'limit' => $limit,
            ]);

            return $customers;
        } catch (\Exception $e) {
            // ⚠️ MANDATORY: Log error with context
            $this->logError($e, [
                'action' => 'get_top_customers',
                'user_id' => Auth::id(),
                'limit' => $limit,
            ]);

            throw $e;
        }
    }

    /**
     * Get customers with debt
     *
     * @param string|null $orderBy
     * @return Collection
     */
    public function getCustomersWithDebt(string $orderBy = 'highest_debt'): Collection
    {
        // ⚠️ MANDATORY: Log business operation
        $this->logActivity('debt_customers_analysis', [
            'user_id' => Auth::id(),
            'order_by' => $orderBy,
            'action' => 'get_customers_with_debt',
        ]);

        try {
            $customers = $this->customerRepository->getCustomersWithDebt($orderBy);

            // ⚠️ MANDATORY: Log analysis results
            $this->logActivity('debt_customers_found', [
                'user_id' => Auth::id(),
                'count' => $customers->count(),
                'order_by' => $orderBy,
            ]);

            return $customers;
        } catch (\Exception $e) {
            // ⚠️ MANDATORY: Log error with context
            $this->logError($e, [
                'action' => 'get_customers_with_debt',
                'user_id' => Auth::id(),
                'order_by' => $orderBy,
            ]);

            throw $e;
        }
    }

    /**
     * Get active customers using Builder Pattern
     *
     * @param int $days Days to consider active
     * @return Collection
     */
    public function getActiveCustomers(int $days = 30): Collection
    {
        // ⚠️ MANDATORY: Log business operation
        $this->logActivity('active_customers_analysis', [
            'user_id' => Auth::id(),
            'days' => $days,
            'action' => 'get_active_customers',
        ]);

        try {
            $customers = $this->customerRepository->getActiveCustomers($days);

            // ⚠️ MANDATORY: Log analysis results
            $this->logActivity('active_customers_found', [
                'user_id' => Auth::id(),
                'count' => $customers->count(),
                'days' => $days,
            ]);

            return $customers;
        } catch (\Exception $e) {
            // ⚠️ MANDATORY: Log error with context
            $this->logError($e, [
                'action' => 'get_active_customers',
                'user_id' => Auth::id(),
                'days' => $days,
            ]);

            throw $e;
        }
    }

    /**
     * Get inactive customers (no transactions in last N days)
     *
     * @param int $days
     * @return Collection
     */
    public function getInactiveCustomers(int $days = 30): Collection
    {
        // ⚠️ MANDATORY: Log business operation
        $this->logActivity('inactive_customers_analysis', [
            'user_id' => Auth::id(),
            'days' => $days,
            'action' => 'get_inactive_customers',
        ]);

        try {
            $customers = $this->customerRepository->getInactiveCustomers($days);

            // ⚠️ MANDATORY: Log analysis results
            $this->logActivity('inactive_customers_found', [
                'user_id' => Auth::id(),
                'count' => $customers->count(),
                'days' => $days,
            ]);

            return $customers;
        } catch (\Exception $e) {
            // ⚠️ MANDATORY: Log error with context
            $this->logError($e, [
                'action' => 'get_inactive_customers',
                'user_id' => Auth::id(),
                'days' => $days,
            ]);

            throw $e;
        }
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
        // ⚠️ MANDATORY: Log business operation
        $this->logActivity('customers_by_group_retrieved', [
            'user_id' => Auth::id(),
            'group' => $group,
            'filters' => array_filter($filters),
            'action' => 'get_customers_by_group',
        ]);

        try {
            $customers = $this->customerRepository->getCustomersByGroup($group, $filters);

            // ⚠️ MANDATORY: Log results
            $this->logActivity('customers_by_group_found', [
                'user_id' => Auth::id(),
                'group' => $group,
                'count' => $customers->count(),
                'filters_applied' => count(array_filter($filters)),
            ]);

            return $customers;
        } catch (\Exception $e) {
            // ⚠️ MANDATORY: Log error with context
            $this->logError($e, [
                'action' => 'get_customers_by_group',
                'user_id' => Auth::id(),
                'group' => $group,
                'filters' => $filters,
            ]);

            throw $e;
        }
    }
}
