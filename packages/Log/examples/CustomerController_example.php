<?php

// Example usage in Customer package - CustomerController.php
namespace Packages\Customer\Http\Controllers;

use Illuminate\Http\Request;
use Packages\Customer\Services\CustomerService;
use Packages\Customer\Http\Requests\StoreCustomerRequest;
use Packages\Customer\Http\Resources\CustomerResource;
use Packages\Log\Traits\Loggable;

class CustomerController extends Controller
{
    use Loggable;

    protected CustomerService $customerService;

    public function __construct(CustomerService $customerService)
    {
        $this->customerService = $customerService;
        
        // Apply logging middleware
        $this->middleware('log.requests')->only(['store', 'update', 'destroy']);
        $this->middleware('log.performance')->only(['index', 'search']);
        $this->middleware('log.sql')->only(['store', 'update']);
    }

    /**
     * Display a listing of customers
     */
    public function index(Request $request)
    {
        $customers = $this->customerService->searchCustomers(
            $request->get('search', ''),
            $request->only(['type', 'group', 'gender']),
            $request->get('per_page', 15)
        );

        // Log user activity
        $this->logActivity('customers_viewed', [
            'search_term' => $request->get('search'),
            'filters' => $request->only(['type', 'group', 'gender']),
            'results_count' => $customers->count(),
        ]);

        return $request->expectsJson() 
            ? CustomerResource::collection($customers)
            : view('customers.index', compact('customers'));
    }

    /**
     * Store a newly created customer
     */
    public function store(StoreCustomerRequest $request)
    {
        try {
            $customer = $this->withPerformanceLogging('customer_creation', function () use ($request) {
                return $this->customerService->createCustomer($request->validated());
            });

            // Log successful creation
            $this->logActivity('customer_created', [
                'customer_id' => $customer->id,
                'customer_email' => $customer->email,
                'customer_type' => $customer->type,
            ]);

            return response()->json([
                'message' => 'Customer created successfully',
                'customer' => new CustomerResource($customer)
            ], 201);

        } catch (\Exception $e) {
            // Log error with context
            $this->logError($e, [
                'action' => 'customer_creation',
                'request_data' => $request->except(['password']), // Exclude sensitive data
            ]);

            return response()->json([
                'message' => 'Failed to create customer',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified customer
     */
    public function update(StoreCustomerRequest $request, Customer $customer)
    {
        $originalData = $customer->toArray();

        try {
            $updatedCustomer = $this->customerService->updateCustomer($customer, $request->validated());

            // Log model update with changes
            $this->logModelEvent('updated', $updatedCustomer, [
                'changed_fields' => array_keys($updatedCustomer->getChanges()),
                'original_data' => $originalData,
            ]);

            return response()->json([
                'message' => 'Customer updated successfully',
                'customer' => new CustomerResource($updatedCustomer)
            ]);

        } catch (\Exception $e) {
            $this->logError($e, [
                'action' => 'customer_update',
                'customer_id' => $customer->id,
            ]);

            return response()->json([
                'message' => 'Failed to update customer',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified customer
     */
    public function destroy(Customer $customer)
    {
        try {
            $this->customerService->deleteCustomer($customer);

            // Log deletion
            $this->logActivity('customer_deleted', [
                'customer_id' => $customer->id,
                'customer_email' => $customer->email,
                'deleted_by' => auth()->id(),
            ]);

            return response()->json([
                'message' => 'Customer deleted successfully'
            ]);

        } catch (\Exception $e) {
            $this->logError($e, [
                'action' => 'customer_deletion',
                'customer_id' => $customer->id,
            ]);

            return response()->json([
                'message' => 'Failed to delete customer',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search customers with advanced filters
     */
    public function search(Request $request)
    {
        $startTime = microtime(true);

        try {
            $results = $this->customerService->advancedSearch($request->all());

            // Log search operation performance
            $this->logOperationPerformance('customer_search', $startTime, [
                'search_criteria' => $request->all(),
                'results_count' => $results->count(),
                'user_id' => auth()->id(),
            ]);

            return CustomerResource::collection($results);

        } catch (\Exception $e) {
            $this->logError($e, [
                'action' => 'customer_search',
                'search_criteria' => $request->all(),
            ]);

            return response()->json([
                'message' => 'Search failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export customers with logging
     */
    public function export(Request $request)
    {
        // Log process step
        $this->logProcessStep('customer_export', 'started', [
            'export_format' => $request->get('format', 'csv'),
            'filters' => $request->only(['type', 'group', 'date_range']),
        ]);

        try {
            $exportData = $this->withQueryLogging('customer_export_query', function () use ($request) {
                return $this->customerService->exportCustomers($request->all());
            });

            $this->logProcessStep('customer_export', 'completed', [
                'records_exported' => count($exportData),
                'file_size_mb' => round(strlen(serialize($exportData)) / 1024 / 1024, 2),
            ]);

            return response()->json([
                'message' => 'Export completed successfully',
                'data' => $exportData
            ]);

        } catch (\Exception $e) {
            $this->logProcessStep('customer_export', 'failed', [
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
