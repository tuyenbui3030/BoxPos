<?php

namespace Packages\Customer\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Packages\Customer\Services\CustomerService;
use Packages\Customer\Http\Requests\StoreCustomerRequest;
use Packages\Customer\Http\Requests\UpdateCustomerRequest;
use Packages\Customer\Http\Resources\CustomerResource;
use Packages\Customer\Exceptions\CustomerNotFoundException;

class CustomerController extends Controller
{
    protected CustomerService $customerService;

    public function __construct(CustomerService $customerService)
    {
        $this->customerService = $customerService;
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

            if ($request->expectsJson()) {
                return CustomerResource::collection($customers);
            }

            return view('customer::index', compact('customers'));
        } catch (\Exception $e) {
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
        try {
            $customer = $this->customerService->getCustomerById($id);

            if ($request->expectsJson()) {
                return new CustomerResource($customer);
            }

            return view('customer::show', compact('customer'));
        } catch (CustomerNotFoundException $e) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Customer not found'], 404);
            }

            return redirect()->route('customers.index')
                ->with('error', 'Customer not found');
        }
    }

    /**
     * Show the form for editing the specified customer.
     */
    public function edit($id)
    {
        try {
            $customer = $this->customerService->getCustomerById($id);
            return view('customer::edit', compact('customer'));
        } catch (CustomerNotFoundException $e) {
            return redirect()->route('customers.index')
                ->with('error', 'Customer not found');
        }
    }

    /**
     * Update the specified customer.
     */
    public function update(UpdateCustomerRequest $request, $id)
    {
        try {
            $customer = $this->customerService->updateCustomer($id, $request->validated());

            if ($request->expectsJson()) {
                return new CustomerResource($customer);
            }

            return redirect()->route('customers.show', $customer)
                ->with('success', 'Customer updated successfully');
        } catch (CustomerNotFoundException $e) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Customer not found'], 404);
            }

            return back()->with('error', 'Customer not found');
        } catch (\Exception $e) {
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
    public function destroy(Request $request, $id)
    {
        try {
            $this->customerService->deleteCustomer($id);

            if ($request->expectsJson()) {
                return response()->json(['message' => 'Customer deleted successfully']);
            }

            return redirect()->route('customers.index')
                ->with('success', 'Customer deleted successfully');
        } catch (CustomerNotFoundException $e) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Customer not found'], 404);
            }

            return back()->with('error', 'Customer not found');
        } catch (\Exception $e) {
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
        try {
            $minSales = $request->get('min_sales', 10000);
            $maxDebt = $request->get('max_debt', 1000);
            
            $customers = $this->customerService->getVipCustomers($minSales, $maxDebt);

            if ($request->expectsJson()) {
                return CustomerResource::collection($customers);
            }

            return view('customer::vip', compact('customers'));
        } catch (\Exception $e) {
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
        try {
            $minDebt = $request->get('min_debt', 5000);
            $inactiveDays = $request->get('inactive_days', 30);
            
            $customers = $this->customerService->getAtRiskCustomers($minDebt, $inactiveDays);

            if ($request->expectsJson()) {
                return CustomerResource::collection($customers);
            }

            return view('customer::at-risk', compact('customers'));
        } catch (\Exception $e) {
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
        try {
            $customers = $this->customerService->getUpcomingBirthdays();

            if ($request->expectsJson()) {
                return CustomerResource::collection($customers);
            }

            return view('customer::birthdays', compact('customers'));
        } catch (\Exception $e) {
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
        try {
            $limit = $request->get('limit', 10);
            $customers = $this->customerService->getTopCustomers($limit);

            if ($request->expectsJson()) {
                return CustomerResource::collection($customers);
            }

            return view('customer::top', compact('customers'));
        } catch (\Exception $e) {
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
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Failed to fetch customers by group'], 500);
            }

            return back()->with('error', 'Failed to fetch customers by group');
        }
    }
}
