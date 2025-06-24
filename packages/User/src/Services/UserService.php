<?php

namespace Packages\User\Services;

use Packages\User\Models\User;
use Packages\User\Repositories\UserRepository;
use Packages\User\Events\UserRegistered;
use Packages\User\Events\UserLoggedIn;
use Packages\User\Events\UserPasswordChanged;
use Packages\User\Exceptions\UserNotFoundException;
use Packages\User\Exceptions\InvalidCredentialsException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

/**
 * User Service Class
 * 
 * Handles all business logic related to user operations.
 * Acts as a layer between controllers and data access.
 */
class UserService
{
    /**
     * User repository instance
     *
     * @var UserRepository
     */
    protected UserRepository $userRepository;

    /**
     * Constructor
     *
     * @param UserRepository $userRepository
     */
    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Get all users with pagination
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getAllUsers(int $perPage = 15): LengthAwarePaginator
    {
        return $this->userRepository->paginate($perPage);
    }

    /**
     * Get user by ID
     *
     * @param int $id
     * @return User
     * @throws UserNotFoundException
     */
    public function getUserById(int $id): User
    {
        $user = $this->userRepository->findById($id);
        
        if (!$user) {
            throw new UserNotFoundException("User with ID {$id} not found");
        }

        return $user;
    }

    /**
     * Create a new user
     *
     * @param array $data
     * @return User
     */
    public function createUser(array $data): User
    {
        // Hash password if provided
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $user = $this->userRepository->create($data);
        
        // Dispatch user registered event
        event(new UserRegistered($user));
        
        return $user;
    }

    /**
     * Update existing user
     *
     * @param int $id
     * @param array $data
     * @return User
     * @throws UserNotFoundException
     */
    public function updateUser(int $id, array $data): User
    {
        $user = $this->getUserById($id);
        
        // Hash password if provided
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $updatedUser = $this->userRepository->update($user, $data);
        
        return $updatedUser;
    }

    /**
     * Delete user
     *
     * @param int $id
     * @return bool
     * @throws UserNotFoundException
     */
    public function deleteUser(int $id): bool
    {
        $user = $this->getUserById($id);
        return $this->userRepository->delete($user);
    }

    /**
     * Authenticate user with credentials
     *
     * @param array $credentials
     * @return User
     * @throws InvalidCredentialsException
     */
    public function authenticate(array $credentials): User
    {
        $user = $this->userRepository->findByEmail($credentials['email']);
        
        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            throw new InvalidCredentialsException('Invalid credentials provided');
        }

        // Dispatch user logged in event
        event(new UserLoggedIn($user));
        
        return $user;
    }

    /**
     * Change user password
     *
     * @param int $userId
     * @param string $currentPassword
     * @param string $newPassword
     * @return bool
     * @throws UserNotFoundException
     * @throws InvalidCredentialsException
     */
    public function changePassword(int $userId, string $currentPassword, string $newPassword): bool
    {
        $user = $this->getUserById($userId);
        
        if (!Hash::check($currentPassword, $user->password)) {
            throw new InvalidCredentialsException('Current password is incorrect');
        }

        $result = $this->userRepository->update($user, [
            'password' => Hash::make($newPassword)
        ]);

        if ($result) {
            event(new UserPasswordChanged($user));
        }

        return (bool) $result;
    }

    /**
     * Search users by criteria
     *
     * @param array $criteria
     * @return Collection
     */
    public function searchUsers(array $criteria): Collection
    {
        return $this->userRepository->search($criteria);
    }

    /**
     * Get active users
     *
     * @return Collection
     */
    public function getActiveUsers(): Collection
    {
        return $this->userRepository->getActive();
    }

    /**
     * Get users by role
     *
     * @param string $role
     * @return Collection
     */
    public function getUsersByRole(string $role): Collection
    {
        return $this->userRepository->getByRole($role);
    }

    /**
     * Activate user account
     *
     * @param int $userId
     * @return bool
     * @throws UserNotFoundException
     */
    public function activateUser(int $userId): bool
    {
        $user = $this->getUserById($userId);
        return (bool) $this->userRepository->update($user, ['is_active' => true]);
    }

    /**
     * Deactivate user account
     *
     * @param int $userId
     * @return bool
     * @throws UserNotFoundException
     */
    public function deactivateUser(int $userId): bool
    {
        $user = $this->getUserById($userId);
        return (bool) $this->userRepository->update($user, ['is_active' => false]);
    }
}
