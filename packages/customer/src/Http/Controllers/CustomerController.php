<?php

namespace Packages\Customer\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Packages\Customer\Services\CustomerService;
use Packages\Customer\Http\Requests\StoreCustomerRequest;
use Packages\Customer\Http\Requests\UpdateCustomerRequest;
use Packages\Customer\Http\Resources\CustomerResource;
use Packages\Customer\Exceptions\CustomerNotFoundException;
use Packages\Log\Traits\Loggable;

class CustomerController extends Controller
{
    use Loggable;

    protected CustomerService $customerService;

    public function __construct(CustomerService $customerService)
    {
        $this->customerService = $customerService;
        
        // Apply logging middleware to specific actions
        $this->middleware('log.requests')->only(['store', 'update', 'destroy']);
        $this->middleware('log.sql')->only(['store', 'update']);
    }

    /**
     * Display a listing of customers.
     */
    public function index(Request $request)
    {
        try {
            // Use Builder Pattern for advanced filtering
            $search = $request->get('search', '');
            $filters = [
                'type' => $request->get('type'),
                'group' => $request->get('group'),
                'gender' => $request->get('gender'),
                'has_debt' => $request->boolean('has_debt'),
                'min_sales' => $request->get('min_sales'),
                'max_debt' => $request->get('max_debt'),
                'active_days' => $request->get('active_days'),
            ];
            
            $perPage = $request->get('per_page', 15);
            
            $customers = $this->customerService->searchCustomers($search, $filters, $perPage);

            // Log user activity
            $this->logActivity('customers_viewed', [
                'search_term' => $search,
                'filters' => array_filter($filters), // Only log non-empty filters
                'results_count' => $customers->count(),
                'user_id' => auth()->id(),
            ]);

            if ($request->expectsJson()) {
                return CustomerResource::collection($customers);
            }

            return view('customer::index', compact('customers'));
        } catch (\Exception $e) {
            // Log error with context
            $this->logError($e, [
                'action' => 'customers_listing',
                'search_term' => $request->get('search'),
                'filters' => $request->all(),
            ]);

            if ($request->expectsJson()) {
                return response()->json(['error' => 'Failed to fetch customers'], 500);
            }

            return back()->with('error', 'Failed to fetch customers');
        }
    }

    /**
     * Show the form for creating a new customer.
     */
    public function create()
    {
        return view('customer::create');
    }

    /**
     * Store a newly created customer.
     */
    public function store(StoreCustomerRequest $request)
    {
        try {
            $customer = $this->customerService->createCustomer($request->validated());

            if ($request->expectsJson()) {
                return new CustomerResource($customer);
            }

            return redirect()->route('customers.show', $customer)
                ->with('success', 'Customer created successfully');
        } catch (\Exception $e) {
            // ⚠️ MANDATORY: Log error with context
            $this->logError($e, [
                'controller' => 'CustomerController',
                'action' => 'store',
                'user_id' => auth()->id(),
                'ip_address' => $request->ip(),
            ]);

            if ($request->expectsJson()) {
                return response()->json(['error' => 'Failed to create customer'], 500);
            }

            return back()->withInput()
                ->with('error', 'Failed to create customer');
        }
    }

    /**
     * Display the specified customer.
     */
    public function show(Request $request, $id)
    {
        // ⚠️ MANDATORY: Log user action
        $this->logActivity('customer_view_accessed', [
            'user_id' => auth()->id(),
            'customer_id' => $id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        try {
            $customer = $this->customerService->getCustomerById($id);

            if ($request->expectsJson()) {
                return new CustomerResource($customer);
            }

            return view('customer::show', compact('customer'));
        } catch (CustomerNotFoundException $e) {
            // ⚠️ MANDATORY: Log error with context
            $this->logError($e, [
                'controller' => 'CustomerController',
                'action' => 'show',
                'customer_id' => $id,
                'user_id' => auth()->id(),
                'error_type' => 'customer_not_found',
            ]);

            if ($request->expectsJson()) {
                return response()->json(['error' => 'Customer not found'], 404);
            }

            return redirect()->route('customers.index')
                ->with('error', 'Customer not found');
        } catch (\Exception $e) {
            // ⚠️ MANDATORY: Log error with context
            $this->logError($e, [
                'controller' => 'CustomerController',
                'action' => 'show',
                'customer_id' => $id,
                'user_id' => auth()->id(),
            ]);

            if ($request->expectsJson()) {
                return response()->json(['error' => 'Failed to fetch customer'], 500);
            }

            return redirect()->route('customers.index')
                ->with('error', 'Failed to fetch customer');
        }
    }

    /**
     * Show the form for editing the specified customer.
     */
    public function edit($id)
    {
        // ⚠️ MANDATORY: Log user action
        $this->logActivity('customer_edit_form_accessed', [
            'user_id' => auth()->id(),
            'customer_id' => $id,
            'ip_address' => request()->ip(),
        ]);

        try {
            $customer = $this->customerService->getCustomerById($id);
            return view('customer::edit', compact('customer'));
        } catch (CustomerNotFoundException $e) {
            // ⚠️ MANDATORY: Log error with context
            $this->logError($e, [
                'controller' => 'CustomerController',
                'action' => 'edit',
                'customer_id' => $id,
                'user_id' => auth()->id(),
                'error_type' => 'customer_not_found',
            ]);

            return redirect()->route('customers.index')
                ->with('error', 'Customer not found');
        } catch (\Exception $e) {
            // ⚠️ MANDATORY: Log error with context
            $this->logError($e, [
                'controller' => 'CustomerController',
                'action' => 'edit',
                'customer_id' => $id,
                'user_id' => auth()->id(),
            ]);

            return redirect()->route('customers.index')
                ->with('error', 'Failed to access customer edit form');
        }
    }

    /**
     * Update the specified customer.
     */
    public function update(UpdateCustomerRequest $request, $id)
    {
        // ⚠️ MANDATORY: Log user action
        $this->logActivity('customer_update_form_submitted', [
            'user_id' => auth()->id(),
            'customer_id' => $id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'updated_fields' => array_keys($request->validated()),
        ]);

        try {
            $customer = $this->customerService->updateCustomer($id, $request->validated());

            if ($request->expectsJson()) {
                return new CustomerResource($customer);
            }

            return redirect()->route('customers.show', $customer)
                ->with('success', 'Customer updated successfully');
        } catch (CustomerNotFoundException $e) {
            // ⚠️ MANDATORY: Log error with context
            $this->logError($e, [
                'controller' => 'CustomerController',
                'action' => 'update',
                'customer_id' => $id,
                'user_id' => auth()->id(),
                'error_type' => 'customer_not_found',
            ]);

            if ($request->expectsJson()) {
                return response()->json(['error' => 'Customer not found'], 404);
            }

            return back()->with('error', 'Customer not found');
        } catch (\Exception $e) {
            // ⚠️ MANDATORY: Log error with context
            $this->logError($e, [
                'controller' => 'CustomerController',
                'action' => 'update',
                'customer_id' => $id,
                'user_id' => auth()->id(),
            ]);

            if ($request->expectsJson()) {
                return response()->json(['error' => 'Failed to update customer'], 500);
            }

            return back()->withInput()
                ->with('error', 'Failed to update customer');
        }
    }

    /**
     * Remove the specified customer.
     */
    /**
     * Remove the specified customer.
     */
    public function destroy(Request $request, $id)
    {
        // ⚠️ MANDATORY: Log user action
        $this->logActivity('customer_deletion_requested', [
            'user_id' => auth()->id(),
            'customer_id' => $id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        try {
            $this->customerService->deleteCustomer($id);

            if ($request->expectsJson()) {
                return response()->json(['message' => 'Customer deleted successfully']);
            }

            return redirect()->route('customers.index')
                ->with('success', 'Customer deleted successfully');
        } catch (CustomerNotFoundException $e) {
            // ⚠️ MANDATORY: Log error with context
            $this->logError($e, [
                'controller' => 'CustomerController',
                'action' => 'destroy',
                'customer_id' => $id,
                'user_id' => auth()->id(),
                'error_type' => 'customer_not_found',
            ]);

            if ($request->expectsJson()) {
                return response()->json(['error' => 'Customer not found'], 404);
            }

            return back()->with('error', 'Customer not found');
        } catch (\Exception $e) {
            // ⚠️ MANDATORY: Log error with context
            $this->logError($e, [
                'controller' => 'CustomerController',
                'action' => 'destroy',
                'customer_id' => $id,
                'user_id' => auth()->id(),
            ]);

            if ($request->expectsJson()) {
                return response()->json(['error' => 'Failed to delete customer'], 500);
            }

            return back()->with('error', 'Failed to delete customer');
        }
    }

    /**
     * Get VIP customers
     */
    public function vipCustomers(Request $request)
    {
        // ⚠️ MANDATORY: Log user action
        $this->logActivity('vip_customers_report_accessed', [
            'user_id' => auth()->id(),
            'min_sales' => $request->get('min_sales', 10000),
            'max_debt' => $request->get('max_debt', 1000),
            'ip_address' => $request->ip(),
        ]);

        try {
            $minSales = $request->get('min_sales', 10000);
            $maxDebt = $request->get('max_debt', 1000);
            
            $customers = $this->customerService->getVipCustomers($minSales, $maxDebt);

            if ($request->expectsJson()) {
                return CustomerResource::collection($customers);
            }

            return view('customer::vip', compact('customers'));
        } catch (\Exception $e) {
            // ⚠️ MANDATORY: Log error with context
            $this->logError($e, [
                'controller' => 'CustomerController',
                'action' => 'vipCustomers',
                'user_id' => auth()->id(),
                'min_sales' => $request->get('min_sales', 10000),
                'max_debt' => $request->get('max_debt', 1000),
            ]);

            if ($request->expectsJson()) {
                return response()->json(['error' => 'Failed to fetch VIP customers'], 500);
            }

            return back()->with('error', 'Failed to fetch VIP customers');
        }
    }

    /**
     * Get at-risk customers
     */
    public function atRiskCustomers(Request $request)
    {
        // ⚠️ MANDATORY: Log user action
        $this->logActivity('at_risk_customers_report_accessed', [
            'user_id' => auth()->id(),
            'min_debt' => $request->get('min_debt', 5000),
            'inactive_days' => $request->get('inactive_days', 30),
            'ip_address' => $request->ip(),
        ]);

        try {
            $minDebt = $request->get('min_debt', 5000);
            $inactiveDays = $request->get('inactive_days', 30);
            
            $customers = $this->customerService->getAtRiskCustomers($minDebt, $inactiveDays);

            if ($request->expectsJson()) {
                return CustomerResource::collection($customers);
            }

            return view('customer::at-risk', compact('customers'));
        } catch (\Exception $e) {
            // ⚠️ MANDATORY: Log error with context
            $this->logError($e, [
                'controller' => 'CustomerController',
                'action' => 'atRiskCustomers',
                'user_id' => auth()->id(),
                'min_debt' => $request->get('min_debt', 5000),
                'inactive_days' => $request->get('inactive_days', 30),
            ]);

            if ($request->expectsJson()) {
                return response()->json(['error' => 'Failed to fetch at-risk customers'], 500);
            }

            return back()->with('error', 'Failed to fetch at-risk customers');
        }
    }

    /**
     * Get customers with upcoming birthdays
     */
    public function upcomingBirthdays(Request $request)
    {
        // ⚠️ MANDATORY: Log user action
        $this->logActivity('upcoming_birthdays_report_accessed', [
            'user_id' => auth()->id(),
            'ip_address' => $request->ip(),
        ]);

        try {
            $customers = $this->customerService->getUpcomingBirthdays();

            if ($request->expectsJson()) {
                return CustomerResource::collection($customers);
            }

            return view('customer::birthdays', compact('customers'));
        } catch (\Exception $e) {
            // ⚠️ MANDATORY: Log error with context
            $this->logError($e, [
                'controller' => 'CustomerController',
                'action' => 'upcomingBirthdays',
                'user_id' => auth()->id(),
            ]);

            if ($request->expectsJson()) {
                return response()->json(['error' => 'Failed to fetch birthday customers'], 500);
            }

            return back()->with('error', 'Failed to fetch birthday customers');
        }
    }

    /**
     * Get top customers by sales
     */
    public function topCustomers(Request $request)
    {
        // ⚠️ MANDATORY: Log user action
        $this->logActivity('top_customers_report_accessed', [
            'user_id' => auth()->id(),
            'limit' => $request->get('limit', 10),
            'ip_address' => $request->ip(),
        ]);

        try {
            $limit = $request->get('limit', 10);
            $customers = $this->customerService->getTopCustomers($limit);

            if ($request->expectsJson()) {
                return CustomerResource::collection($customers);
            }

            return view('customer::top', compact('customers'));
        } catch (\Exception $e) {
            // ⚠️ MANDATORY: Log error with context
            $this->logError($e, [
                'controller' => 'CustomerController',
                'action' => 'topCustomers',
                'user_id' => auth()->id(),
                'limit' => $request->get('limit', 10),
            ]);

            if ($request->expectsJson()) {
                return response()->json(['error' => 'Failed to fetch top customers'], 500);
            }

            return back()->with('error', 'Failed to fetch top customers');
        }
    }

    /**
     * Get customers by group with filters
     */
    public function customersByGroup(Request $request, string $group)
    {
        // ⚠️ MANDATORY: Log user action
        $this->logActivity('customers_by_group_report_accessed', [
            'user_id' => auth()->id(),
            'group' => $group,
            'filters' => array_filter($request->only(['gender', 'min_sales', 'max_debt', 'active_days'])),
            'ip_address' => $request->ip(),
        ]);

        try {
            $filters = [
                'gender' => $request->get('gender'),
                'min_sales' => $request->get('min_sales'),
                'max_debt' => $request->get('max_debt'),
                'active_days' => $request->get('active_days'),
            ];
            
            $customers = $this->customerService->getCustomersByGroup($group, $filters);

            if ($request->expectsJson()) {
                return CustomerResource::collection($customers);
            }

            return view('customer::by-group', compact('customers', 'group'));
        } catch (\Exception $e) {
            // ⚠️ MANDATORY: Log error with context
            $this->logError($e, [
                'controller' => 'CustomerController',
                'action' => 'customersByGroup',
                'user_id' => auth()->id(),
                'group' => $group,
                'filters' => $request->only(['gender', 'min_sales', 'max_debt', 'active_days']),
            ]);

            if ($request->expectsJson()) {
                return response()->json(['error' => 'Failed to fetch customers by group'], 500);
            }

            return back()->with('error', 'Failed to fetch customers by group');
        }
    }
}
