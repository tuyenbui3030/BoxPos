<?php

// Example usage in User package - LoginController.php
namespace Packages\User\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Packages\User\Http\Requests\LoginRequest;
use Packages\Log\Services\LogService;
use Packages\Log\Traits\Loggable;

class LoginController extends Controller
{
    use Loggable;

    protected LogService $logService;

    public function __construct(LogService $logService)
    {
        $this->logService = $logService;
        
        // Apply logging middleware
        $this->middleware('log.requests');
        $this->middleware('log.performance');
    }

    /**
     * Show the login form
     */
    public function showLoginForm()
    {
        // Log page access
        $this->logActivity('login_page_accessed', [
            'referrer' => request()->headers->get('referer'),
            'user_agent' => request()->userAgent(),
            'ip_address' => request()->ip(),
        ]);

        return view('auth.login');
    }

    /**
     * Handle login attempt
     */
    public function login(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');
        $remember = $request->boolean('remember');

        // Log login attempt
        $this->logActivity('login_attempted', [
            'email' => $credentials['email'],
            'remember_me' => $remember,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        try {
            if (Auth::attempt($credentials, $remember)) {
                $user = Auth::user();
                
                // Regenerate session for security
                $request->session()->regenerate();

                // Log successful login
                $this->logService->logUserActivity('user_login_success', $user->id, [
                    'login_method' => 'web',
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'remember_me' => $remember,
                    'last_login' => $user->last_login_at,
                ]);

                // Update user's last login
                $user->update([
                    'last_login_at' => now(),
                    'last_login_ip' => $request->ip(),
                ]);

                // Log session creation
                $this->logEvent('user_session_created', [
                    'user_id' => $user->id,
                    'session_id' => session()->getId(),
                    'session_lifetime' => config('session.lifetime'),
                ]);

                return redirect()->intended('/dashboard');
            }

            // Log failed login attempt
            $this->logActivity('login_failed', [
                'email' => $credentials['email'],
                'reason' => 'invalid_credentials',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return back()->withErrors([
                'email' => 'The provided credentials do not match our records.',
            ])->onlyInput('email');

        } catch (\Exception $e) {
            // Log login error
            $this->logError($e, [
                'action' => 'user_login',
                'email' => $credentials['email'],
                'ip_address' => $request->ip(),
            ]);

            return back()->withErrors([
                'email' => 'An error occurred during login. Please try again.',
            ])->onlyInput('email');
        }
    }

    /**
     * Handle logout
     */
    public function logout(Request $request)
    {
        $user = Auth::user();
        $sessionId = session()->getId();

        if ($user) {
            // Log logout activity
            $this->logService->logUserActivity('user_logout', $user->id, [
                'logout_method' => 'web',
                'session_duration_minutes' => $this->calculateSessionDuration($user),
                'ip_address' => $request->ip(),
            ]);

            // Log session destruction
            $this->logEvent('user_session_destroyed', [
                'user_id' => $user->id,
                'session_id' => $sessionId,
                'logout_type' => 'manual',
            ]);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    /**
     * Calculate session duration in minutes
     */
    protected function calculateSessionDuration($user): int
    {
        if (!$user->last_login_at) {
            return 0;
        }

        return $user->last_login_at->diffInMinutes(now());
    }
}

// Example usage in User package - UserService.php
namespace Packages\User\Services;

use Packages\User\Models\User;
use Packages\User\Repositories\UserRepository;
use Packages\Log\Traits\LogsQueries;
use Packages\Log\Traits\Loggable;
use Illuminate\Support\Facades\Hash;

class UserService
{
    use LogsQueries, Loggable;

    protected UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Create a new user with comprehensive logging
     */
    public function createUser(array $data): User
    {
        return $this->withQueryLoggingTransaction('user_creation', function () use ($data) {
            // Validate user data
            $this->validateUserCreation($data);

            // Create user
            $user = $this->userRepository->create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'email_verified_at' => $data['email_verified'] ?? false ? now() : null,
                'role' => $data['role'] ?? 'user',
                'status' => $data['status'] ?? 'active',
                'created_by' => auth()->id(),
            ]);

            // Create user profile
            $user->profile()->create([
                'first_name' => $data['first_name'] ?? null,
                'last_name' => $data['last_name'] ?? null,
                'phone' => $data['phone'] ?? null,
                'address' => $data['address'] ?? null,
                'city' => $data['city'] ?? null,
                'country' => $data['country'] ?? null,
                'timezone' => $data['timezone'] ?? config('app.timezone'),
            ]);

            // Assign permissions based on role
            $permissions = $this->getDefaultPermissionsForRole($data['role'] ?? 'user');
            $user->permissions()->attach($permissions);

            // Log user creation
            $this->logEvent('user_account_created', [
                'user_id' => $user->id,
                'email' => $user->email,
                'role' => $user->role,
                'permissions_assigned' => count($permissions),
                'created_by' => auth()->id(),
            ]);

            // Log security event
            $this->logEvent('security_user_account_created', [
                'new_user_id' => $user->id,
                'new_user_email' => $user->email,
                'new_user_role' => $user->role,
                'creator_id' => auth()->id(),
                'creator_ip' => request()->ip(),
            ]);

            return $user->fresh();
        });
    }

    /**
     * Update user password with security logging
     */
    public function updatePassword(User $user, string $newPassword, ?string $currentPassword = null): bool
    {
        try {
            // Verify current password if provided
            if ($currentPassword && !Hash::check($currentPassword, $user->password)) {
                $this->logEvent('security_password_change_failed', [
                    'user_id' => $user->id,
                    'reason' => 'invalid_current_password',
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);
                
                throw new \InvalidArgumentException('Current password is incorrect');
            }

            // Update password
            $user->update([
                'password' => Hash::make($newPassword),
                'password_changed_at' => now(),
            ]);

            // Log password change
            $this->logEvent('security_password_changed', [
                'user_id' => $user->id,
                'changed_by' => auth()->id(),
                'self_change' => auth()->id() === $user->id,
                'ip_address' => request()->ip(),
                'timestamp' => now()->toISOString(),
            ]);

            // Log security event
            $this->logEvent('user_password_updated', [
                'user_id' => $user->id,
                'updated_by' => auth()->id(),
            ]);

            return true;

        } catch (\Exception $e) {
            $this->logError($e, [
                'action' => 'password_update',
                'user_id' => $user->id,
                'updated_by' => auth()->id(),
            ]);
            throw $e;
        }
    }

    /**
     * Deactivate user with audit logging
     */
    public function deactivateUser(User $user, string $reason = null): bool
    {
        return $this->withQueryLogging('user_deactivation', function () use ($user, $reason) {
            $originalStatus = $user->status;

            // Update user status
            $user->update([
                'status' => 'inactive',
                'deactivated_at' => now(),
                'deactivated_by' => auth()->id(),
                'deactivation_reason' => $reason,
            ]);

            // Revoke all active sessions
            $this->revokeUserSessions($user);

            // Log deactivation
            $this->logEvent('user_account_deactivated', [
                'user_id' => $user->id,
                'email' => $user->email,
                'previous_status' => $originalStatus,
                'reason' => $reason,
                'deactivated_by' => auth()->id(),
                'sessions_revoked' => true,
            ]);

            // Log security event
            $this->logEvent('security_user_deactivated', [
                'deactivated_user_id' => $user->id,
                'deactivated_user_email' => $user->email,
                'deactivator_id' => auth()->id(),
                'reason' => $reason,
                'ip_address' => request()->ip(),
            ]);

            return true;
        });
    }

    /**
     * Get user activity with performance monitoring
     */
    public function getUserActivity(User $user, array $filters = []): Collection
    {
        return $this->withQueryLimit(20, function () use ($user, $filters) {
            // This query might be expensive
            $activities = $user->activities()
                ->when($filters['date_from'] ?? null, function ($q, $date) {
                    $q->where('created_at', '>=', $date);
                })
                ->when($filters['date_to'] ?? null, function ($q, $date) {
                    $q->where('created_at', '<=', $date);
                })
                ->when($filters['action'] ?? null, function ($q, $action) {
                    $q->where('action', 'like', "%{$action}%");
                })
                ->with(['activityType', 'relatedModel'])
                ->orderBy('created_at', 'desc')
                ->limit(1000)
                ->get();

            // Log activity query
            $this->logEvent('user_activity_retrieved', [
                'user_id' => $user->id,
                'filters_applied' => array_keys(array_filter($filters)),
                'activities_found' => $activities->count(),
                'query_performance' => $this->getQueryStats(),
            ]);

            return $activities;
        });
    }

    /**
     * Validate user creation data
     */
    protected function validateUserCreation(array $data): void
    {
        if ($this->userRepository->emailExists($data['email'])) {
            $this->logEvent('user_creation_validation_failed', [
                'email' => $data['email'],
                'reason' => 'email_already_exists',
            ]);
            throw new \InvalidArgumentException('Email already exists');
        }
    }

    /**
     * Get default permissions for role
     */
    protected function getDefaultPermissionsForRole(string $role): array
    {
        return match ($role) {
            'admin' => [1, 2, 3, 4, 5], // All permissions
            'manager' => [2, 3, 4], // Most permissions
            'staff' => [3, 4], // Limited permissions
            'user' => [4], // Basic permissions
            default => [],
        };
    }

    /**
     * Revoke all user sessions
     */
    protected function revokeUserSessions(User $user): void
    {
        // Implementation would depend on session storage
        // This is a placeholder for session revocation logic
        $this->logEvent('user_sessions_revoked', [
            'user_id' => $user->id,
            'revoked_by' => auth()->id(),
        ]);
    }
}
