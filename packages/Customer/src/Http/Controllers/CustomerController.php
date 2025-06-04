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
            $customers = $this->customerService->getAllCustomers([
                'per_page' => $request->get('per_page', 15),
                'search' => $request->get('search'),
                'status' => $request->get('status'),
            ]);

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
}
