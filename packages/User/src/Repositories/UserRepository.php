<?php

namespace Packages\User\Repositories;

use Packages\User\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * User Repository Class
 * 
 * Handles all data access operations for users.
 * Provides a clean interface for database operations.
 */
class UserRepository
{
    /**
     * User model instance
     *
     * @var User
     */
    protected User $model;

    /**
     * Constructor
     *
     * @param User $model
     */
    public function __construct(User $model)
    {
        $this->model = $model;
    }

    /**
     * Get all users
     *
     * @return Collection
     */
    public function all(): Collection
    {
        return $this->model->all();
    }

    /**
     * Get users with pagination
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->paginate($perPage);
    }

    /**
     * Find user by ID
     *
     * @param int $id
     * @return User|null
     */
    public function findById(int $id): ?User
    {
        return $this->model->find($id);
    }

    /**
     * Find user by email
     *
     * @param string $email
     * @return User|null
     */
    public function findByEmail(string $email): ?User
    {
        return $this->model->where('email', $email)->first();
    }

    /**
     * Create new user
     *
     * @param array $data
     * @return User
     */
    public function create(array $data): User
    {
        return $this->model->create($data);
    }

    /**
     * Update user
     *
     * @param User $user
     * @param array $data
     * @return User
     */
    public function update(User $user, array $data): User
    {
        $user->update($data);
        return $user->fresh();
    }

    /**
     * Delete user
     *
     * @param User $user
     * @return bool
     */
    public function delete(User $user): bool
    {
        return $user->delete();
    }

    /**
     * Search users by criteria
     *
     * @param array $criteria
     * @return Collection
     */
    public function search(array $criteria): Collection
    {
        $query = $this->model->newQuery();

        if (isset($criteria['name'])) {
            $query->where('name', 'like', '%' . $criteria['name'] . '%');
        }

        if (isset($criteria['email'])) {
            $query->where('email', 'like', '%' . $criteria['email'] . '%');
        }

        if (isset($criteria['role'])) {
            $query->where('role', $criteria['role']);
        }

        if (isset($criteria['is_active'])) {
            $query->where('is_active', $criteria['is_active']);
        }

        return $query->get();
    }

    /**
     * Get active users
     *
     * @return Collection
     */
    public function getActive(): Collection
    {
        return $this->model->where('is_active', true)->get();
    }

    /**
     * Get users by role
     *
     * @param string $role
     * @return Collection
     */
    public function getByRole(string $role): Collection
    {
        return $this->model->where('role', $role)->get();
    }

    /**
     * Get users by status
     *
     * @param bool $isActive
     * @return Collection
     */
    public function getByStatus(bool $isActive): Collection
    {
        return $this->model->where('is_active', $isActive)->get();
    }

    /**
     * Get recently registered users
     *
     * @param int $days
     * @return Collection
     */
    public function getRecentlyRegistered(int $days = 7): Collection
    {
        return $this->model->where('created_at', '>=', now()->subDays($days))->get();
    }
}
