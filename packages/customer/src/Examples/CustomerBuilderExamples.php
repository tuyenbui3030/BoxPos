<?php

namespace Packages\Customer\Examples;

use Packages\Customer\Models\Customer;
use Packages\Customer\Services\CustomerService;

/**
 * Examples of using Customer Builder Pattern
 */
class CustomerBuilderExamples
{
    private CustomerService $customerService;

    public function __construct(CustomerService $customerService)
    {
        $this->customerService = $customerService;
    }

    /**
     * Example 1: Basic search with multiple conditions
     */
    public function basicSearchExample()
    {
        // Old way with scopes
        $customers = Customer::search('John')
            ->byType('premium')
            ->withDebt()
            ->get();

        // New way with Builder Pattern
        $customers = Customer::query()
            ->search('John')
            ->byType('premium')
            ->withDebt()
            ->orderByName()
            ->get();

        return $customers;
    }

    /**
     * Example 2: Complex filtering for dashboard
     */
    public function dashboardStatsExample()
    {
        // VIP customers
        $vipCustomers = Customer::query()
            ->salesAbove(50000)
            ->debtBelow(5000)
            ->activeInLastDays(30)
            ->topCustomers(20)
            ->get();

        // At-risk customers
        $atRiskCustomers = Customer::query()
            ->debtAbove(10000)
            ->inactiveInLastDays(60)
            ->orderByHighestDebt()
            ->get();

        // Birthday notifications
        $birthdayCustomers = Customer::query()
            ->birthdayThisWeek()
            ->orderByName()
            ->get();

        return [
            'vip' => $vipCustomers,
            'at_risk' => $atRiskCustomers,
            'birthdays' => $birthdayCustomers,
        ];
    }

    /**
     * Example 3: Advanced filtering with method chaining
     */
    public function advancedFilteringExample()
    {
        // Get premium female customers with high sales and low debt from last 3 months
        $targetCustomers = Customer::query()
            ->byType('premium')
            ->byGender('female')
            ->salesBetween(20000, 100000)
            ->debtBelow(2000)
            ->createdBetween(
                now()->subMonths(3)->toDateString(),
                now()->toDateString()
            )
            ->activeInLastDays(15)
            ->withCreator()
            ->orderByHighestSales()
            ->get();

        return $targetCustomers;
    }

    /**
     * Example 4: Using service methods
     */
    public function serviceMethodsExample()
    {
        // Get VIP customers
        $vipCustomers = $this->customerService->getVipCustomers(30000, 1000);

        // Get customers with upcoming birthdays
        $birthdayCustomers = $this->customerService->getUpcomingBirthdays();

        // Get top 15 customers
        $topCustomers = $this->customerService->getTopCustomers(15);

        // Get customers by group with filters
        $premiumFemaleCustomers = $this->customerService->getCustomersByGroup('premium', [
            'gender' => 'female',
            'min_sales' => 25000,
            'max_debt' => 3000,
            'active_days' => 45
        ]);

        return [
            'vip' => $vipCustomers,
            'birthdays' => $birthdayCustomers,
            'top' => $topCustomers,
            'premium_female' => $premiumFemaleCustomers,
        ];
    }

    /**
     * Example 5: Combining multiple conditions dynamically
     */
    public function dynamicFilteringExample(array $filters)
    {
        $query = Customer::query();

        // Apply filters conditionally
        if (!empty($filters['search'])) {
            $query->search($filters['search']);
        }

        if (!empty($filters['type'])) {
            $query->byType($filters['type']);
        }

        if (!empty($filters['group'])) {
            $query->byGroup($filters['group']);
        }

        if (!empty($filters['gender'])) {
            $query->byGender($filters['gender']);
        }

        if (!empty($filters['has_debt'])) {
            $query->withDebt();
        }

        if (!empty($filters['min_sales'])) {
            $query->salesAbove($filters['min_sales']);
        }

        if (!empty($filters['active_days'])) {
            $query->activeInLastDays($filters['active_days']);
        }

        // Apply sorting
        $sortBy = $filters['sort_by'] ?? 'name';
        match ($sortBy) {
            'sales' => $query->orderByHighestSales(),
            'debt' => $query->orderByHighestDebt(),
            'recent' => $query->orderByRecentTransaction(),
            default => $query->orderByName(),
        };

        return $query->get();
    }
}
