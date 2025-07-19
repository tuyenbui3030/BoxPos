<?php

namespace Packages\MaterialSuppliers\Builders;

use Illuminate\Database\Eloquent\Builder;

class MaterialSupplierBuilder extends Builder
{
    /**
     * Filter by active suppliers.
     */
    public function active(): self
    {
        return $this->where('is_active', true);
    }

    /**
     * Filter by inactive suppliers.
     */
    public function inactive(): self
    {
        return $this->where('is_active', false);
    }

    /**
     * Filter by preferred suppliers.
     */
    public function preferred(): self
    {
        return $this->where('is_preferred', true);
    }

    /**
     * Filter by supplier type.
     */
    public function byType(string $type): self
    {
        return $this->where('supplier_type', $type);
    }

    /**
     * Filter by multiple types.
     */
    public function byTypes(array $types): self
    {
        return $this->whereIn('supplier_type', $types);
    }

    /**
     * Filter by payment terms.
     */
    public function byPaymentTerms(string $terms): self
    {
        return $this->where('payment_terms', $terms);
    }

    /**
     * Filter by rating range.
     */
    public function byRating(float $minRating, ?float $maxRating = null): self
    {
        $query = $this->where('rating', '>=', $minRating);
        
        if ($maxRating !== null) {
            $query = $query->where('rating', '<=', $maxRating);
        }
        
        return $query;
    }

    /**
     * Search suppliers by multiple fields.
     */
    public function search(string $search): self
    {
        return $this->where(function ($query) use ($search) {
            $query->where('supplier_code', 'like', "%{$search}%")
                  ->orWhere('company_name', 'like', "%{$search}%")
                  ->orWhere('contact_person', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
        });
    }

    /**
     * Order by company name.
     */
    public function orderByCompanyName(string $direction = 'asc'): self
    {
        return $this->orderBy('company_name', $direction);
    }

    /**
     * Order by rating.
     */
    public function orderByRating(string $direction = 'desc'): self
    {
        return $this->orderBy('rating', $direction);
    }

    /**
     * Order by creation date.
     */
    public function orderByNewest(): self
    {
        return $this->orderBy('created_at', 'desc');
    }

    /**
     * Apply multiple criteria filters.
     */
    public function applyCriteria(array $criteria): self
    {
        return $this
            ->when(!empty($criteria['search']), fn($q) => $q->search($criteria['search']))
            ->when(isset($criteria['is_active']), fn($q) => $criteria['is_active'] ? $q->active() : $q->inactive())
            ->when(isset($criteria['is_preferred']), fn($q) => $criteria['is_preferred'] ? $q->preferred() : $q->where('is_preferred', false))
            ->when(!empty($criteria['type']), fn($q) => $q->byType($criteria['type']))
            ->when(!empty($criteria['types']), fn($q) => $q->byTypes($criteria['types']))
            ->when(!empty($criteria['payment_terms']), fn($q) => $q->byPaymentTerms($criteria['payment_terms']))
            ->when(!empty($criteria['min_rating']), fn($q) => $q->byRating($criteria['min_rating'], $criteria['max_rating'] ?? null));
    }

    /**
     * Get suppliers for dropdown/select options.
     */
    public function forDropdown(): self
    {
        return $this->active()
                    ->select('id', 'supplier_code', 'company_name', 'contact_person')
                    ->orderByCompanyName();
    }

    /**
     * Get preferred suppliers.
     */
    public function preferredSuppliers(): self
    {
        return $this->active()->preferred();
    }

    /**
     * Get suppliers with good rating.
     */
    public function goodRating(float $threshold = 4.0): self
    {
        return $this->active()->byRating($threshold);
    }

    /**
     * Get manufacturers.
     */
    public function manufacturers(): self
    {
        return $this->active()->byType('manufacturer');
    }

    /**
     * Get distributors.
     */
    public function distributors(): self
    {
        return $this->active()->byType('distributor');
    }

    /**
     * Include contact information.
     */
    public function withContacts(): self
    {
        return $this->with(['contacts' => function ($query) {
            $query->where('is_active', true)->orderBy('is_primary', 'desc');
        }]);
    }

    /**
     * Include primary contact only.
     */
    public function withPrimaryContact(): self
    {
        return $this->with(['contacts' => function ($query) {
            $query->where('is_primary', true)->where('is_active', true);
        }]);
    }
}
