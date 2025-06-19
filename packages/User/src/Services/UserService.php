<?php

namespace Packages\User\Services;

use Packages\User\Models\User;
use Packages\User\Repositories\UserRepository;
use Packages\User\Events\UserRegistered;
use Packages\User\Events\UserLoggedIn;
use Packages\User\Events\UserPasswordChanged;
use Packages\User\Exceptions\UserNotFoundException;
use Packages\User\Exceptions\InvalidCredentialsException;
use Packages\Log\Traits\Loggable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * User Service Class
 * 
 * Handles all business logic related to user operations.
 * Acts as a layer between controllers and data access.
 */
class UserService
{
    use Loggable; // ⚠️ MANDATORY: Use Loggable trait

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
        // ⚠️ MANDATORY: Log business operation
        $this->logActivity('users_list_viewed', [
            'user_id' => auth()->id(),
            'per_page' => $perPage,
            'action' => 'get_all_users',
        ]);

        try {
            return $this->userRepository->paginate($perPage);
        } catch (\Exception $e) {
            // ⚠️ MANDATORY: Log errors with context
            $this->logError($e, [
                'action' => 'get_all_users',
                'user_id' => auth()->id(),
                'per_page' => $perPage,
            ]);
            
            throw $e;
        }
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
        // ⚠️ MANDATORY: Log business operation
        $this->logActivity('user_viewed', [
            'user_id' => auth()->id(),
            'target_user_id' => $id,
            'action' => 'get_user_by_id',
        ]);

        try {
            $user = $this->userRepository->findById($id);
            
            if (!$user) {
                throw new UserNotFoundException("User with ID {$id} not found");
            }

            return $user;
        } catch (UserNotFoundException $e) {
            // ⚠️ MANDATORY: Log business exception
            $this->logError($e, [
                'action' => 'get_user_by_id',
                'user_id' => auth()->id(),
                'target_user_id' => $id,
                'error_type' => 'user_not_found',
            ]);
            
            throw $e;
        } catch (\Exception $e) {
            // ⚠️ MANDATORY: Log errors with context
            $this->logError($e, [
                'action' => 'get_user_by_id',
                'user_id' => auth()->id(),
                'target_user_id' => $id,
            ]);
            
            throw $e;
        }
    }

    /**
     * Create a new user
     *
     * @param array $data
     * @return User
     */
    public function createUser(array $data): User
    {
        $startTime = microtime(true);

        // ⚠️ MANDATORY: Log operation start
        $this->logActivity('user_creation_started', [
            'user_id' => auth()->id(),
            'data_keys' => array_keys($data),
            'action' => 'create_user',
        ]);

        DB::beginTransaction();

        try {
            // Hash password if provided
            if (isset($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            }

            $user = $this->userRepository->create($data);
            
            // ⚠️ MANDATORY: Log successful operation
            $this->logActivity('user_created', [
                'target_user_id' => $user->id,
                'user_email' => $user->email ?? null,
                'user_name' => $user->name ?? null,
                'user_id' => auth()->id(),
                'action' => 'create_user',
            ]);

            // ⚠️ MANDATORY: Log model event
            $this->logModelEvent('created', $user, [
                'created_fields' => array_keys(array_diff_key($data, array_flip(['password']))),
                'user_id' => auth()->id(),
            ]);

            // ⚠️ MANDATORY: Log operation performance
            $this->logOperationPerformance('user_creation', $startTime, [
                'target_user_id' => $user->id,
                'user_id' => auth()->id(),
            ]);
            
            // Dispatch user registered event
            event(new UserRegistered($user));
            
            DB::commit();
            return $user;
        } catch (\Exception $e) {
            DB::rollback();
            
            // ⚠️ MANDATORY: Log error with context
            $this->logError($e, [
                'action' => 'create_user',
                'user_id' => auth()->id(),
                'data' => array_diff_key($data, array_flip(['password'])), // Exclude sensitive data
            ]);
            
            throw $e;
        }
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
        $startTime = microtime(true);

        // ⚠️ MANDATORY: Log operation start
        $this->logActivity('user_update_started', [
            'user_id' => auth()->id(),
            'target_user_id' => $id,
            'data_keys' => array_keys($data),
            'action' => 'update_user',
        ]);

        DB::beginTransaction();

        try {
            $user = $this->getUserById($id);
            $originalData = $user->toArray();
            
            // Hash password if provided
            if (isset($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            }

            $updatedUser = $this->userRepository->update($user, $data);
            
            // ⚠️ MANDATORY: Log successful operation
            $this->logActivity('user_updated', [
                'target_user_id' => $updatedUser->id,
                'user_email' => $updatedUser->email ?? null,
                'user_name' => $updatedUser->name ?? null,
                'user_id' => auth()->id(),
                'updated_fields' => array_keys($data),
                'action' => 'update_user',
            ]);

            // ⚠️ MANDATORY: Log model event with changes
            $this->logModelEvent('updated', $updatedUser, [
                'updated_fields' => array_keys($data),
                'original_data' => array_intersect_key($originalData, array_diff_key($data, array_flip(['password']))),
                'new_data' => array_intersect_key($updatedUser->toArray(), array_diff_key($data, array_flip(['password']))),
                'user_id' => auth()->id(),
            ]);

            // ⚠️ MANDATORY: Log operation performance
            $this->logOperationPerformance('user_update', $startTime, [
                'target_user_id' => $updatedUser->id,
                'user_id' => auth()->id(),
            ]);
            
            DB::commit();
            return $updatedUser;
        } catch (\Exception $e) {
            DB::rollback();
            
            // ⚠️ MANDATORY: Log error with context
            $this->logError($e, [
                'action' => 'update_user',
                'user_id' => auth()->id(),
                'target_user_id' => $id,
                'data' => array_diff_key($data, array_flip(['password'])), // Exclude sensitive data
            ]);
            
            throw $e;
        }
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
        $startTime = microtime(true);

        // ⚠️ MANDATORY: Log operation start
        $this->logActivity('user_deletion_started', [
            'user_id' => auth()->id(),
            'target_user_id' => $id,
            'action' => 'delete_user',
        ]);

        DB::beginTransaction();

        try {
            $user = $this->getUserById($id);
            $userData = $user->toArray();
            
            $result = $this->userRepository->delete($user);
            
            if ($result) {
                // ⚠️ MANDATORY: Log successful operation
                $this->logActivity('user_deleted', [
                    'target_user_id' => $id,
                    'user_email' => $userData['email'] ?? null,
                    'user_name' => $userData['name'] ?? null,
                    'user_id' => auth()->id(),
                    'action' => 'delete_user',
                ]);

                // ⚠️ MANDATORY: Log model event
                $this->logModelEvent('deleted', $user, [
                    'deleted_data' => array_diff_key($userData, array_flip(['password', 'remember_token'])),
                    'user_id' => auth()->id(),
                ]);

                // ⚠️ MANDATORY: Log operation performance
                $this->logOperationPerformance('user_deletion', $startTime, [
                    'target_user_id' => $id,
                    'user_id' => auth()->id(),
                ]);
            }
            
            DB::commit();
            return $result;
        } catch (\Exception $e) {
            DB::rollback();
            
            // ⚠️ MANDATORY: Log error with context
            $this->logError($e, [
                'action' => 'delete_user',
                'user_id' => auth()->id(),
                'target_user_id' => $id,
            ]);
            
            throw $e;
        }
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
        // ⚠️ MANDATORY: Log authentication attempt
        $this->logActivity('user_authentication_attempted', [
            'email' => $credentials['email'],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'action' => 'authenticate',
        ]);

        try {
            $user = $this->userRepository->findByEmail($credentials['email']);
            
            if (!$user || !Hash::check($credentials['password'], $user->password)) {
                // ⚠️ MANDATORY: Log failed authentication
                $this->logActivity('user_authentication_failed', [
                    'email' => $credentials['email'],
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                    'reason' => $user ? 'invalid_password' : 'user_not_found',
                ]);
                
                throw new InvalidCredentialsException('Invalid credentials provided');
            }

            // ⚠️ MANDATORY: Log successful authentication
            $this->logActivity('user_authenticated', [
                'target_user_id' => $user->id,
                'user_email' => $user->email,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'action' => 'authenticate',
            ]);

            // Dispatch user logged in event
            event(new UserLoggedIn($user));
            
            return $user;
        } catch (InvalidCredentialsException $e) {
            // ⚠️ MANDATORY: Log authentication error
            $this->logError($e, [
                'action' => 'authenticate',
                'email' => $credentials['email'],
                'ip_address' => request()->ip(),
            ]);
            
            throw $e;
        } catch (\Exception $e) {
            // ⚠️ MANDATORY: Log error with context
            $this->logError($e, [
                'action' => 'authenticate',
                'email' => $credentials['email'],
                'ip_address' => request()->ip(),
            ]);
            
            throw $e;
        }
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
        $startTime = microtime(true);

        // ⚠️ MANDATORY: Log password change attempt
        $this->logActivity('user_password_change_attempted', [
            'user_id' => auth()->id(),
            'target_user_id' => $userId,
            'ip_address' => request()->ip(),
            'action' => 'change_password',
        ]);

        DB::beginTransaction();

        try {
            $user = $this->getUserById($userId);
            
            if (!Hash::check($currentPassword, $user->password)) {
                // ⚠️ MANDATORY: Log failed password verification
                $this->logActivity('user_password_change_failed', [
                    'user_id' => auth()->id(),
                    'target_user_id' => $userId,
                    'reason' => 'invalid_current_password',
                    'ip_address' => request()->ip(),
                ]);
                
                throw new InvalidCredentialsException('Current password is incorrect');
            }

            $result = $this->userRepository->update($user, [
                'password' => Hash::make($newPassword)
            ]);

            if ($result) {
                // ⚠️ MANDATORY: Log successful password change
                $this->logActivity('user_password_changed', [
                    'user_id' => auth()->id(),
                    'target_user_id' => $userId,
                    'user_email' => $user->email,
                    'ip_address' => request()->ip(),
                    'action' => 'change_password',
                ]);

                // ⚠️ MANDATORY: Log model event
                $this->logModelEvent('updated', $user, [
                    'updated_fields' => ['password'],
                    'user_id' => auth()->id(),
                    'change_type' => 'password_change',
                ]);

                // ⚠️ MANDATORY: Log operation performance
                $this->logOperationPerformance('password_change', $startTime, [
                    'target_user_id' => $userId,
                    'user_id' => auth()->id(),
                ]);

                event(new UserPasswordChanged($user));
            }

            DB::commit();
            return (bool) $result;
        } catch (\Exception $e) {
            DB::rollback();
            
            // ⚠️ MANDATORY: Log error with context
            $this->logError($e, [
                'action' => 'change_password',
                'user_id' => auth()->id(),
                'target_user_id' => $userId,
                'ip_address' => request()->ip(),
            ]);
            
            throw $e;
        }
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
        // ⚠️ MANDATORY: Log business operation
        $this->logActivity('users_by_role_retrieved', [
            'user_id' => auth()->id(),
            'role' => $role,
            'action' => 'get_users_by_role',
        ]);

        try {
            $users = $this->userRepository->getByRole($role);

            // ⚠️ MANDATORY: Log results
            $this->logActivity('users_by_role_found', [
                'user_id' => auth()->id(),
                'role' => $role,
                'count' => $users->count(),
            ]);

            return $users;
        } catch (\Exception $e) {
            // ⚠️ MANDATORY: Log error with context
            $this->logError($e, [
                'action' => 'get_users_by_role',
                'user_id' => auth()->id(),
                'role' => $role,
            ]);
            
            throw $e;
        }
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
        // ⚠️ MANDATORY: Log business operation
        $this->logActivity('user_activation_attempted', [
            'user_id' => auth()->id(),
            'target_user_id' => $userId,
            'action' => 'activate_user',
        ]);

        try {
            $user = $this->getUserById($userId);
            $result = (bool) $this->userRepository->update($user, ['is_active' => true]);

            if ($result) {
                // ⚠️ MANDATORY: Log successful operation
                $this->logActivity('user_activated', [
                    'user_id' => auth()->id(),
                    'target_user_id' => $userId,
                    'user_email' => $user->email,
                    'action' => 'activate_user',
                ]);
            }

            return $result;
        } catch (\Exception $e) {
            // ⚠️ MANDATORY: Log error with context
            $this->logError($e, [
                'action' => 'activate_user',
                'user_id' => auth()->id(),
                'target_user_id' => $userId,
            ]);
            
            throw $e;
        }
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
        // ⚠️ MANDATORY: Log business operation
        $this->logActivity('user_deactivation_attempted', [
            'user_id' => auth()->id(),
            'target_user_id' => $userId,
            'action' => 'deactivate_user',
        ]);

        try {
            $user = $this->getUserById($userId);
            $result = (bool) $this->userRepository->update($user, ['is_active' => false]);

            if ($result) {
                // ⚠️ MANDATORY: Log successful operation
                $this->logActivity('user_deactivated', [
                    'user_id' => auth()->id(),
                    'target_user_id' => $userId,
                    'user_email' => $user->email,
                    'action' => 'deactivate_user',
                ]);
            }

            return $result;
        } catch (\Exception $e) {
            // ⚠️ MANDATORY: Log error with context
            $this->logError($e, [
                'action' => 'deactivate_user',
                'user_id' => auth()->id(),
                'target_user_id' => $userId,
            ]);
            
            throw $e;
        }
    }

    /**
     * Register a new user (public registration)
     *
     * @param array $data
     * @return User
     */
    public function register(array $data): User
    {
        $startTime = microtime(true);

        // ⚠️ MANDATORY: Log registration attempt
        $this->logActivity('user_registration_started', [
            'email' => $data['email'] ?? 'unknown',
            'name' => $data['name'] ?? 'unknown',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'action' => 'register',
        ]);

        DB::beginTransaction();

        try {
            // Hash password if provided
            if (isset($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            }

            // Set default values for registration
            $data['is_active'] = $data['is_active'] ?? true;
            $data['role'] = $data['role'] ?? config('user.default_role', 'user');

            $user = $this->userRepository->create($data);
            
            // ⚠️ MANDATORY: Log successful registration
            $this->logActivity('user_registered', [
                'target_user_id' => $user->id,
                'user_email' => $user->email ?? null,
                'user_name' => $user->name ?? null,
                'user_role' => $user->role ?? null,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'action' => 'register',
            ]);

            // ⚠️ MANDATORY: Log model event
            $this->logModelEvent('created', $user, [
                'created_fields' => array_keys(array_diff_key($data, array_flip(['password']))),
                'registration_type' => 'public',
                'ip_address' => request()->ip(),
            ]);

            // ⚠️ MANDATORY: Log operation performance
            $this->logOperationPerformance('user_registration', $startTime, [
                'target_user_id' => $user->id,
                'ip_address' => request()->ip(),
            ]);
            
            // Dispatch user registered event
            event(new UserRegistered($user));
            
            DB::commit();
            return $user;
        } catch (\Exception $e) {
            DB::rollback();
            
            // ⚠️ MANDATORY: Log error with context
            $this->logError($e, [
                'action' => 'register',
                'email' => $data['email'] ?? 'unknown',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'data' => array_diff_key($data, array_flip(['password'])), // Exclude sensitive data
            ]);
            
            throw $e;
        }
    }
}
