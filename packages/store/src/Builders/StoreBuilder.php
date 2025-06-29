<?php

namespace Packages\Store\Builders;

use Illuminate\Database\Eloquent\Builder;

class StoreBuilder extends Builder
{
    /**
     * Filter stores by status.
     */
    public function byStatus(string $status): self
    {
        return $this->where('status', $status);
    }

    /**
     * Filter active stores.
     */
    public function active(): self
    {
        return $this->byStatus('active');
    }

    /**
     * Filter inactive stores.
     */
    public function inactive(): self
    {
        return $this->byStatus('inactive');
    }

    /**
     * Filter suspended stores.
     */
    public function suspended(): self
    {
        return $this->byStatus('suspended');
    }

    /**
     * Search stores by name, slug, or domain.
     */
    public function search(string $term): self
    {
        return $this->where(function ($query) use ($term) {
            $query->where('name', 'like', "%{$term}%")
                  ->orWhere('slug', 'like', "%{$term}%")
                  ->orWhere('domain', 'like', "%{$term}%")
                  ->orWhere('description', 'like', "%{$term}%");
        });
    }

    /**
     * Filter stores by domain.
     */
    public function byDomain(string $domain): self
    {
        return $this->where('domain', $domain);
    }

    /**
     * Filter stores by slug.
     */
    public function bySlug(string $slug): self
    {
        return $this->where('slug', $slug);
    }

    /**
     * Filter stores that have users.
     */
    public function withUsers(): self
    {
        return $this->has('users');
    }

    /**
     * Filter stores that have active users.
     */
    public function withActiveUsers(): self
    {
        return $this->whereHas('users', function ($query) {
            $query->where('user_stores.is_active', true);
        });
    }

    /**
     * Filter stores by user.
     */
    public function forUser(int $userId): self
    {
        return $this->whereHas('users', function ($query) use ($userId) {
            $query->where('users.id', $userId)
                  ->where('user_stores.is_active', true);
        });
    }

    /**
     * Filter stores where user has specific role.
     */
    public function forUserWithRole(int $userId, string $role): self
    {
        return $this->whereHas('users', function ($query) use ($userId, $role) {
            $query->where('users.id', $userId)
                  ->where('user_stores.role', $role)
                  ->where('user_stores.is_active', true);
        });
    }

    /**
     * Filter stores where user is admin.
     */
    public function forUserAsAdmin(int $userId): self
    {
        return $this->forUserWithRole($userId, 'admin');
    }

    /**
     * Filter stores where user is manager.
     */
    public function forUserAsManager(int $userId): self
    {
        return $this->forUserWithRole($userId, 'manager');
    }

    /**
     * Order by name.
     */
    public function orderByName(string $direction = 'asc'): self
    {
        return $this->orderBy('name', $direction);
    }

    /**
     * Order by created date.
     */
    public function orderByCreated(string $direction = 'desc'): self
    {
        return $this->orderBy('created_at', $direction);
    }

    /**
     * Order by updated date.
     */
    public function orderByUpdated(string $direction = 'desc'): self
    {
        return $this->orderBy('updated_at', $direction);
    }

    /**
     * Apply filters from array.
     */
    public function applyFilters(array $filters): self
    {
        return $this
            ->when(!empty($filters['search']), fn($q) => $q->search($filters['search']))
            ->when(!empty($filters['status']), fn($q) => $q->byStatus($filters['status']))
            ->when(!empty($filters['user_id']), fn($q) => $q->forUser($filters['user_id']))
            ->when(!empty($filters['role']), fn($q) => $q->forUserWithRole($filters['user_id'] ?? 0, $filters['role']))
            ->when(!empty($filters['has_users']), fn($q) => $q->withUsers())
            ->when(!empty($filters['has_active_users']), fn($q) => $q->withActiveUsers());
    }

    /**
     * Get stores for specific business scenarios.
     */
    public function forScenario(string $scenario): self
    {
        return match ($scenario) {
            'user_accessible' => $this->active()->withActiveUsers(),
            'admin_management' => $this->orderByName(),
            'user_dashboard' => $this->active()->orderByName(),
            'store_selection' => $this->active()->orderByName(),
            default => $this,
        };
    }

    /**
     * Include user relationship with pivot data.
     */
    public function withUserPivot(): self
    {
        return $this->with(['users' => function ($query) {
            $query->withPivot(['role', 'permissions', 'is_active', 'joined_at']);
        }]);
    }

    /**
     * Include only active users.
     */
    public function withActiveUsersOnly(): self
    {
        return $this->with(['users' => function ($query) {
            $query->wherePivot('is_active', true)
                  ->withPivot(['role', 'permissions', 'is_active', 'joined_at']);
        }]);
    }

    /**
     * Get stores with user count.
     */
    public function withUserCount(): self
    {
        return $this->withCount(['users', 'activeUsers']);
    }

    /**
     * Filter stores created between dates.
     */
    public function createdBetween($startDate, $endDate): self
    {
        return $this->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Filter stores updated between dates.
     */
    public function updatedBetween($startDate, $endDate): self
    {
        return $this->whereBetween('updated_at', [$startDate, $endDate]);
    }

    /**
     * Filter stores by timezone.
     */
    public function byTimezone(string $timezone): self
    {
        return $this->where('timezone', $timezone);
    }

    /**
     * Filter stores by currency.
     */
    public function byCurrency(string $currency): self
    {
        return $this->where('currency', $currency);
    }

    /**
     * Filter stores by language.
     */
    public function byLanguage(string $language): self
    {
        return $this->where('language', $language);
    }
}
