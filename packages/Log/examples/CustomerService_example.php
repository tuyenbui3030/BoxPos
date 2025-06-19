<?php

// Example usage in Customer package - CustomerService.php
namespace Packages\Customer\Services;

use Packages\Customer\Models\Customer;
use Packages\Customer\Repositories\CustomerRepository;
use Packages\Log\Traits\LogsQueries;
use Packages\Log\Traits\Loggable;
use Illuminate\Support\Facades\DB;

class CustomerService
{
    use LogsQueries, Loggable;

    protected CustomerRepository $customerRepository;

    public function __construct(CustomerRepository $customerRepository)
    {
        $this->customerRepository = $customerRepository;
    }

    /**
     * Create a new customer with query logging
     */
    public function createCustomer(array $data): Customer
    {
        return $this->withQueryLoggingTransaction('customer_creation', function () use ($data) {
            // Validate business rules
            $this->validateCustomerData($data);

            // Create customer
            $customer = $this->customerRepository->create([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'type' => $data['type'] ?? 'regular',
                'group_id' => $data['group_id'] ?? null,
                'created_by' => auth()->id(),
            ]);

            // Create customer profile
            $customer->profile()->create([
                'address' => $data['address'] ?? null,
                'city' => $data['city'] ?? null,
                'country' => $data['country'] ?? null,
                'birth_date' => $data['birth_date'] ?? null,
                'gender' => $data['gender'] ?? null,
            ]);

            // Initialize customer balance
            $customer->balance()->create([
                'current_balance' => 0,
                'credit_limit' => $data['credit_limit'] ?? 0,
                'last_updated' => now(),
            ]);

            // Log business event
            $this->logEvent('customer_account_setup_completed', [
                'customer_id' => $customer->id,
                'setup_steps' => ['profile', 'balance'],
                'initial_credit_limit' => $data['credit_limit'] ?? 0,
            ]);

            return $customer->fresh();
        });
    }

    /**
     * Update customer with performance monitoring
     */
    public function updateCustomer(Customer $customer, array $data): Customer
    {
        return $this->withQueryLogging('customer_update', function () use ($customer, $data) {
            $originalData = $customer->toArray();

            // Update main customer record
            $customer->update([
                'name' => $data['name'] ?? $customer->name,
                'email' => $data['email'] ?? $customer->email,
                'phone' => $data['phone'] ?? $customer->phone,
                'type' => $data['type'] ?? $customer->type,
                'group_id' => $data['group_id'] ?? $customer->group_id,
                'updated_by' => auth()->id(),
            ]);

            // Update profile if provided
            if (isset($data['profile'])) {
                $customer->profile()->updateOrCreate(
                    ['customer_id' => $customer->id],
                    $data['profile']
                );
            }

            // Update balance/credit settings if provided
            if (isset($data['credit_limit'])) {
                $customer->balance()->updateOrCreate(
                    ['customer_id' => $customer->id],
                    ['credit_limit' => $data['credit_limit']]
                );
            }

            // Log significant changes
            $changes = $customer->getChanges();
            if (!empty($changes)) {
                $this->logEvent('customer_data_updated', [
                    'customer_id' => $customer->id,
                    'changed_fields' => array_keys($changes),
                    'old_values' => array_intersect_key($originalData, $changes),
                    'new_values' => $changes,
                ]);
            }

            return $customer->fresh();
        });
    }

    /**
     * Search customers with N+1 detection
     */
    public function searchCustomers(string $search = '', array $filters = [], int $perPage = 15)
    {
        return $this->withNPlusOneDetection(function () use ($search, $filters, $perPage) {
            $query = $this->customerRepository->searchQuery($search, $filters);
            
            // This might trigger N+1 if not properly handled
            $customers = $query->with(['profile', 'group', 'balance'])->paginate($perPage);

            // Log search metrics
            $this->logEvent('customer_search_performed', [
                'search_term' => $search,
                'filters_applied' => array_keys(array_filter($filters)),
                'results_count' => $customers->count(),
                'total_results' => $customers->total(),
                'page' => $customers->currentPage(),
            ]);

            return $customers;
        });
    }

    /**
     * Advanced search with query limit monitoring
     */
    public function advancedSearch(array $criteria): Collection
    {
        return $this->withQueryLimit(30, function () use ($criteria) {
            // Complex search that might generate many queries
            $baseQuery = $this->customerRepository->advancedSearchQuery($criteria);

            // Apply various filters and joins
            if (!empty($criteria['has_orders'])) {
                $baseQuery->whereHas('orders');
            }

            if (!empty($criteria['order_value_min'])) {
                $baseQuery->whereHas('orders', function ($q) use ($criteria) {
                    $q->where('total', '>=', $criteria['order_value_min']);
                });
            }

            if (!empty($criteria['last_activity_days'])) {
                $baseQuery->where('last_activity_at', '>=', 
                    now()->subDays($criteria['last_activity_days'])
                );
            }

            $results = $baseQuery->get();

            // Log complex search
            $this->logEvent('advanced_customer_search', [
                'criteria_count' => count(array_filter($criteria)),
                'results_found' => $results->count(),
                'query_performance' => $this->getQueryStats(),
            ]);

            return $results;
        });
    }

    /**
     * Export customers with performance logging
     */
    public function exportCustomers(array $filters): array
    {
        return $this->withPerformanceLogging('customer_export', function () use ($filters) {
            // Get customers in batches to avoid memory issues
            $customers = [];
            
            $this->customerRepository
                ->exportQuery($filters)
                ->with(['profile', 'group', 'balance', 'orders'])
                ->chunk(1000, function ($batch) use (&$customers) {
                    foreach ($batch as $customer) {
                        $customers[] = [
                            'id' => $customer->id,
                            'name' => $customer->name,
                            'email' => $customer->email,
                            'phone' => $customer->phone,
                            'type' => $customer->type,
                            'group' => $customer->group->name ?? 'N/A',
                            'total_orders' => $customer->orders_count,
                            'total_spent' => $customer->orders_sum_total,
                            'current_balance' => $customer->balance->current_balance ?? 0,
                            'created_at' => $customer->created_at->format('Y-m-d H:i:s'),
                        ];
                    }
                });

            return $customers;
        }, [
            'export_filters' => $filters,
            'total_records' => count($customers ?? []),
        ]);
    }

    /**
     * Delete customer with cleanup tracking
     */
    public function deleteCustomer(Customer $customer): bool
    {
        return $this->withQueryLoggingTransaction('customer_deletion', function () use ($customer) {
            $customerData = $customer->toArray();

            // Soft delete related records
            $customer->orders()->delete();
            $customer->profile()->delete();
            $customer->balance()->delete();
            $customer->activities()->delete();

            // Delete main record
            $deleted = $customer->delete();

            if ($deleted) {
                $this->logEvent('customer_account_deleted', [
                    'customer_id' => $customer->id,
                    'customer_email' => $customerData['email'],
                    'related_records_cleaned' => [
                        'orders', 'profile', 'balance', 'activities'
                    ],
                    'deleted_by' => auth()->id(),
                ]);
            }

            return $deleted;
        });
    }

    /**
     * Business validation with error logging
     */
    protected function validateCustomerData(array $data): void
    {
        try {
            // Check for duplicate email
            if ($this->customerRepository->emailExists($data['email'])) {
                throw new \InvalidArgumentException('Email already exists');
            }

            // Validate phone format
            if (isset($data['phone']) && !$this->isValidPhone($data['phone'])) {
                throw new \InvalidArgumentException('Invalid phone format');
            }

            // Validate credit limit
            if (isset($data['credit_limit']) && $data['credit_limit'] < 0) {
                throw new \InvalidArgumentException('Credit limit cannot be negative');
            }

        } catch (\Exception $e) {
            $this->logError($e, [
                'validation_data' => $data,
                'validation_step' => 'customer_data_validation',
            ]);
            throw $e;
        }
    }

    /**
     * Helper method for phone validation
     */
    protected function isValidPhone(?string $phone): bool
    {
        if (!$phone) return true;
        return preg_match('/^[\+]?[1-9][\d]{0,15}$/', $phone);
    }
}
