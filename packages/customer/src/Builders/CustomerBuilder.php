<?php

namespace Packages\Customer\Builders;

use Illuminate\Database\Eloquent\Builder;

class CustomerBuilder extends Builder
{
    /**
     * Filter customers by store
     */
    public function forStore(int $storeId): self
    {
        return $this->where('store_id', $storeId);
    }

    /**
     * Filter customers for multiple stores
     */
    public function forStores(array $storeIds): self
    {
        return $this->whereIn('store_id', $storeIds);
    }

    /**
     * Search customers by code, name, or phone number
     */
    public function search(string $search): self
    {
        return $this->where(function ($query) use ($search) {
            $query->where('customer_code', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhere('phone_number', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
        });
    }

    /**
     * Filter customers with debt
     */
    public function withDebt(): self
    {
        return $this->where('current_debt', '>', 0);
    }

    /**
     * Filter customers without debt
     */
    public function withoutDebt(): self
    {
        return $this->where('current_debt', '<=', 0);
    }

    /**
     * Filter by customer group
     */
    public function byGroup(string $group): self
    {
        return $this->where('customer_group', $group);
    }

    /**
     * Filter by customer type
     */
    public function byType(string $type): self
    {
        return $this->where('customer_type', $type);
    }

    /**
     * Filter by gender
     */
    public function byGender(string $gender): self
    {
        return $this->where('gender', $gender);
    }

    /**
     * Filter by creation date range
     */
    public function createdBetween(string $startDate, string $endDate): self
    {
        return $this->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Filter by birthday range
     */
    public function birthdayBetween(string $startDate, string $endDate): self
    {
        return $this->whereBetween('birthday', [$startDate, $endDate]);
    }

    /**
     * Filter by last transaction date range
     */
    public function lastTransactionBetween(string $startDate, string $endDate): self
    {
        return $this->whereBetween('last_transaction_at', [$startDate, $endDate]);
    }

    /**
     * Filter by sales amount range
     */
    public function salesBetween(float $minSales, float $maxSales): self
    {
        return $this->whereBetween('total_sales', [$minSales, $maxSales]);
    }

    /**
     * Filter customers with sales above amount
     */
    public function salesAbove(float $amount): self
    {
        return $this->where('total_sales', '>', $amount);
    }

    /**
     * Filter customers with sales below amount
     */
    public function salesBelow(float $amount): self
    {
        return $this->where('total_sales', '<', $amount);
    }

    /**
     * Filter customers with debt above amount
     */
    public function debtAbove(float $amount): self
    {
        return $this->where('current_debt', '>', $amount);
    }

    /**
     * Filter customers with debt below amount
     */
    public function debtBelow(float $amount): self
    {
        return $this->where('current_debt', '<', $amount);
    }

    /**
     * Filter customers who had transactions in the last N days
     */
    public function activeInLastDays(int $days): self
    {
        return $this->where('last_transaction_at', '>=', now()->subDays($days));
    }

    /**
     * Filter customers who haven't had transactions in the last N days
     */
    public function inactiveInLastDays(int $days): self
    {
        return $this->where(function ($query) use ($days) {
            $query->where('last_transaction_at', '<', now()->subDays($days))
                  ->orWhereNull('last_transaction_at');
        });
    }

    /**
     * Filter customers with birthday this month
     */
    public function birthdayThisMonth(): self
    {
        return $this->whereMonth('birthday', now()->month);
    }

    /**
     * Filter customers with birthday this week
     */
    public function birthdayThisWeek(): self
    {
        $startOfWeek = now()->startOfWeek();
        $endOfWeek = now()->endOfWeek();

        return $this->whereBetween('birthday', [$startOfWeek, $endOfWeek]);
    }

    /**
     * Order by total sales (highest first)
     */
    public function orderByHighestSales(): self
    {
        return $this->orderBy('total_sales', 'desc');
    }

    /**
     * Order by total sales (lowest first)
     */
    public function orderByLowestSales(): self
    {
        return $this->orderBy('total_sales', 'asc');
    }

    /**
     * Order by debt amount (highest first)
     */
    public function orderByHighestDebt(): self
    {
        return $this->orderBy('current_debt', 'desc');
    }

    /**
     * Order by last transaction date (most recent first)
     */
    public function orderByRecentTransaction(): self
    {
        return $this->orderBy('last_transaction_at', 'desc');
    }

    /**
     * Order by customer name
     */
    public function orderByName(string $direction = 'asc'): self
    {
        return $this->orderBy('customer_name', $direction);
    }

    /**
     * Include creator relationship
     */
    public function withCreator(): self
    {
        return $this->with('creator');
    }

    /**
     * Get top customers by sales
     */
    public function topCustomers(int $limit = 10): self
    {
        return $this->orderByHighestSales()->limit($limit);
    }

    /**
     * Get VIP customers (high sales and low debt)
     */
    public function vipCustomers(float $minSales = 10000, float $maxDebt = 1000): self
    {
        return $this->salesAbove($minSales)->debtBelow($maxDebt);
    }

    /**
     * Get at-risk customers (high debt, low recent activity)
     */
    public function atRiskCustomers(float $minDebt = 5000, int $inactiveDays = 30): self
    {
        return $this->debtAbove($minDebt)->inactiveInLastDays($inactiveDays);
    }

    /**
     * Apply multiple filters from criteria array
     *
     * @param array $criteria
     * @return self
     */
    public function applyCriteria(array $criteria): self
    {
        return $this
            ->when(!empty($criteria['search']), fn($q) => $q->search($criteria['search']))
            ->when(!empty($criteria['type']), fn($q) => $q->byType($criteria['type']))
            ->when(!empty($criteria['group']), fn($q) => $q->byGroup($criteria['group']))
            ->when(!empty($criteria['gender']), fn($q) => $q->byGender($criteria['gender']))
            ->when(!empty($criteria['has_debt']), fn($q) => $q->withDebt())
            ->when(!empty($criteria['min_sales']), fn($q) => $q->salesAbove($criteria['min_sales']))
            ->when(!empty($criteria['max_debt']), fn($q) => $q->debtBelow($criteria['max_debt']))
            ->when(!empty($criteria['active_days']), fn($q) => $q->activeInLastDays($criteria['active_days']));
    }

    /**
     * Apply dynamic filters from array
     *
     * @param array $filters
     * @return self
     */
    public function applyFilters(array $filters): self
    {
        foreach ($filters as $filter => $value) {
            if (empty($value)) continue;

            match ($filter) {
                'search' => $this->search($value),
                'type' => $this->byType($value),
                'group' => $this->byGroup($value),
                'gender' => $this->byGender($value),
                'has_debt' => $value ? $this->withDebt() : $this,
                'min_sales' => $this->salesAbove($value),
                'max_debt' => $this->debtBelow($value),
                'active_days' => $this->activeInLastDays($value),
                'sales_range' => is_array($value) && count($value) === 2
                    ? $this->salesBetween($value[0], $value[1])
                    : $this,
                'debt_range' => is_array($value) && count($value) === 2
                    ? $this->where('current_debt', '>=', $value[0])->where('current_debt', '<=', $value[1])
                    : $this,
                default => $this,
            };
        }

        return $this;
    }

    /**
     * Create a complex query for dashboard analytics
     *
     * @param array $options
     * @return self
     */
    public function forDashboard(array $options = []): self
    {
        $query = $this->withCreator();

        // Apply default analytics filters
        if ($options['include_inactive'] ?? false) {
            // Include all customers
        } else {
            $query->activeInLastDays($options['active_days'] ?? 90);
        }

        if ($options['vip_only'] ?? false) {
            $query->vipCustomers(
                $options['min_sales'] ?? 10000,
                $options['max_debt'] ?? 1000
            );
        }

        if ($options['at_risk_only'] ?? false) {
            $query->atRiskCustomers(
                $options['min_debt'] ?? 5000,
                $options['inactive_days'] ?? 30
            );
        }

        return $query;
    }

    /**
     * Quick filters for common business scenarios
     *
     * @param string $scenario
     * @return self
     */
    public function forScenario(string $scenario): self
    {
        return match ($scenario) {
            'marketing_campaign' => $this->activeInLastDays(60)->salesAbove(5000)->withCreator(),
            'debt_collection' => $this->withDebt()->orderByHighestDebt(),
            'birthday_promotion' => $this->birthdayThisMonth()->orderByName(),
            'loyalty_program' => $this->salesAbove(20000)->activeInLastDays(30),
            'win_back_campaign' => $this->inactiveInLastDays(90)->salesAbove(10000),
            'new_customer_welcome' => $this->where('created_at', '>=', now()->subDays(7)),
            default => $this,
        };
    }
}
