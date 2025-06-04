<?php

namespace Packages\Customer\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Packages\Customer\Exceptions\CustomerNotFoundException;
use Packages\Customer\Repositories\CustomerRepository;
use Symfony\Component\HttpFoundation\Response;

class EnsureCustomerOwnership
{
    protected CustomerRepository $customerRepository;

    public function __construct(CustomerRepository $customerRepository)
    {
        $this->customerRepository = $customerRepository;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $customerId = $request->route('customer') ?? $request->route('id');
        $user = Auth::user();

        if (!$customerId || !$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        try {
            $customer = $this->customerRepository->findById($customerId);
            
            // Check if the authenticated user owns this customer record
            // Adjust this logic based on your application's ownership model
            if ($customer->user_id !== $user->id && !$user->hasRole('admin')) {
                return response()->json(['error' => 'Forbidden'], 403);
            }

            // Add customer to request for easy access in controller
            $request->merge(['customer_model' => $customer]);
            
        } catch (CustomerNotFoundException $e) {
            return response()->json(['error' => 'Customer not found'], 404);
        }

        return $next($request);
    }
}
