<?php

namespace Packages\User\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Packages\User\Services\UserService;
use Packages\User\Http\Requests\RegisterUserRequest;
use Packages\User\Http\Requests\LoginUserRequest;
use Packages\User\Http\Requests\UpdateUserRequest;
use Packages\User\Http\Requests\ChangePasswordRequest;
use Packages\User\Http\Resources\UserResource;
use Packages\User\Exceptions\UserNotFoundException;
use Packages\User\Exceptions\InvalidCredentialsException;
use Packages\Log\Traits\Loggable;

class UserController extends Controller
{
    use Loggable; // ⚠️ MANDATORY: Use Loggable trait

    protected UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
        
        // Apply logging middleware to specific actions
        $this->middleware('log.requests')->only(['register', 'login', 'updateProfile', 'changePassword']);
        $this->middleware('log.sql')->only(['register', 'updateProfile']);
    }

    /**
     * Show the user profile.
     */
    public function profile(Request $request)
    {
        // ⚠️ MANDATORY: Log user action
        $this->logActivity('user_profile_accessed', [
            'user_id' => auth()->id(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'expects_json' => $request->expectsJson(),
        ]);

        try {
            $user = Auth::user();

            if ($request->expectsJson()) {
                return new UserResource($user);
            }

            return view('user::profile', compact('user'));
        } catch (\Exception $e) {
            // ⚠️ MANDATORY: Log error with context
            $this->logError($e, [
                'controller' => 'UserController',
                'action' => 'profile',
                'user_id' => auth()->id(),
            ]);

            if ($request->expectsJson()) {
                return response()->json(['error' => 'Failed to load profile'], 500);
            }

            return redirect()->route('dashboard')
                ->with('error', 'Failed to load profile');
        }
    }

    /**
     * Show the registration form.
     */
    public function showRegistrationForm()
    {
        // ⚠️ MANDATORY: Log user action
        $this->logActivity('registration_form_accessed', [
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'referrer' => request()->headers->get('referer'),
        ]);

        return view('user::auth.register');
    }

    /**
     * Handle user registration.
     */
    public function register(RegisterUserRequest $request)
    {
        // ⚠️ MANDATORY: Log user action
        $this->logActivity('user_registration_form_submitted', [
            'email' => $request->input('email'),
            'name' => $request->input('name'),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'expects_json' => $request->expectsJson(),
        ]);

        try {
            $user = $this->userService->register($request->validated());

            if ($request->expectsJson()) {
                return new UserResource($user);
            }

            // Auto-login after registration if configured
            if (config('user.registration.auto_login_after_registration', true)) {
                Auth::login($user);
                return redirect()->route('dashboard')
                    ->with('success', 'Registration successful! Welcome!');
            }

            return redirect()->route('login')
                ->with('success', 'Registration successful! Please login.');
        } catch (\Exception $e) {
            // ⚠️ MANDATORY: Log error with context
            $this->logError($e, [
                'controller' => 'UserController',
                'action' => 'register',
                'email' => $request->input('email'),
                'ip_address' => $request->ip(),
            ]);

            if ($request->expectsJson()) {
                return response()->json(['error' => 'Registration failed'], 500);
            }

            return back()->withInput()
                ->with('error', 'Registration failed. Please try again.');
        }
    }

    /**
     * Show the login form.
     */
    public function showLoginForm()
    {
        // ⚠️ MANDATORY: Log user action
        $this->logActivity('login_form_accessed', [
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'referrer' => request()->headers->get('referer'),
        ]);

        return view('user::auth.login');
    }

    /**
     * Handle user login.
     */
    public function login(LoginUserRequest $request)
    {
        // ⚠️ MANDATORY: Log user action
        $this->logActivity('user_login_form_submitted', [
            'email' => $request->input('email'),
            'remember' => $request->boolean('remember'),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'expects_json' => $request->expectsJson(),
        ]);

        try {
            $user = $this->userService->authenticate(
                $request->input('email'),
                $request->input('password')
            );

            Auth::login($user, $request->boolean('remember'));

            // ⚠️ MANDATORY: Log successful controller action
            $this->logActivity('user_login_controller_success', [
                'user_id' => $user->id,
                'email' => $user->email,
                'remember' => $request->boolean('remember'),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            if ($request->expectsJson()) {
                return new UserResource($user);
            }

            $intended = $request->session()->pull('url.intended', route('dashboard'));
            return redirect($intended)
                ->with('success', 'Welcome back!');
        } catch (InvalidCredentialsException $e) {
            // ⚠️ MANDATORY: Log error with context
            $this->logError($e, [
                'controller' => 'UserController',
                'action' => 'login',
                'email' => $request->input('email'),
                'ip_address' => $request->ip(),
                'error_type' => 'invalid_credentials',
            ]);

            if ($request->expectsJson()) {
                return response()->json(['error' => 'Invalid credentials'], 401);
            }

            return back()->withInput($request->only('email'))
                ->with('error', 'Invalid email or password.');
        } catch (\Exception $e) {
            // ⚠️ MANDATORY: Log error with context
            $this->logError($e, [
                'controller' => 'UserController',
                'action' => 'login',
                'email' => $request->input('email'),
                'ip_address' => $request->ip(),
            ]);

            if ($request->expectsJson()) {
                return response()->json(['error' => 'Login failed'], 500);
            }

            return back()->withInput($request->only('email'))
                ->with('error', 'Login failed. Please try again.');
        }
    }

    /**
     * Handle user logout.
     */
    public function logout(Request $request)
    {
        $user = Auth::user();
        
        // ⚠️ MANDATORY: Log user action
        $this->logActivity('user_logout_requested', [
            'user_id' => $user?->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'expects_json' => $request->expectsJson(),
        ]);

        try {
            Auth::logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            // ⚠️ MANDATORY: Log successful logout
            $this->logActivity('user_logout_completed', [
                'user_id' => $user?->id,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            if ($request->expectsJson()) {
                return response()->json(['message' => 'Logged out successfully']);
            }

            return redirect()->route('login')
                ->with('success', 'You have been logged out successfully.');
        } catch (\Exception $e) {
            // ⚠️ MANDATORY: Log error with context
            $this->logError($e, [
                'controller' => 'UserController',
                'action' => 'logout',
                'user_id' => $user?->id,
                'ip_address' => $request->ip(),
            ]);

            if ($request->expectsJson()) {
                return response()->json(['error' => 'Logout failed'], 500);
            }

            return redirect()->route('dashboard')
                ->with('error', 'Logout failed. Please try again.');
        }
    }

    /**
     * Update user profile.
     */
    public function updateProfile(UpdateUserRequest $request)
    {
        // ⚠️ MANDATORY: Log user action
        $this->logActivity('user_profile_update_form_submitted', [
            'user_id' => auth()->id(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'updated_fields' => array_keys($request->validated()),
            'expects_json' => $request->expectsJson(),
        ]);

        try {
            $user = $this->userService->updateUser(Auth::id(), $request->validated());

            if ($request->expectsJson()) {
                return new UserResource($user);
            }

            return back()->with('success', 'Profile updated successfully');
        } catch (UserNotFoundException $e) {
            // ⚠️ MANDATORY: Log error with context
            $this->logError($e, [
                'controller' => 'UserController',
                'action' => 'updateProfile',
                'user_id' => auth()->id(),
                'error_type' => 'user_not_found',
            ]);

            if ($request->expectsJson()) {
                return response()->json(['error' => 'User not found'], 404);
            }

            return back()->with('error', 'User not found');
        } catch (\Exception $e) {
            // ⚠️ MANDATORY: Log error with context
            $this->logError($e, [
                'controller' => 'UserController',
                'action' => 'updateProfile',
                'user_id' => auth()->id(),
            ]);

            if ($request->expectsJson()) {
                return response()->json(['error' => 'Failed to update profile'], 500);
            }

            return back()->withInput()
                ->with('error', 'Failed to update profile');
        }
    }

    /**
     * Change user password.
     */
    public function changePassword(ChangePasswordRequest $request)
    {
        // ⚠️ MANDATORY: Log user action
        $this->logActivity('user_password_change_form_submitted', [
            'user_id' => auth()->id(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'expects_json' => $request->expectsJson(),
        ]);

        try {
            $this->userService->changePassword(
                Auth::id(),
                $request->input('current_password'),
                $request->input('password')
            );

            // ⚠️ MANDATORY: Log successful controller action
            $this->logActivity('user_password_change_controller_success', [
                'user_id' => auth()->id(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            if ($request->expectsJson()) {
                return response()->json(['message' => 'Password changed successfully']);
            }

            return back()->with('success', 'Password changed successfully');
        } catch (InvalidCredentialsException $e) {
            // ⚠️ MANDATORY: Log error with context
            $this->logError($e, [
                'controller' => 'UserController',
                'action' => 'changePassword',
                'user_id' => auth()->id(),
                'ip_address' => $request->ip(),
                'error_type' => 'invalid_current_password',
            ]);

            if ($request->expectsJson()) {
                return response()->json(['error' => 'Current password is incorrect'], 422);
            }

            return back()->with('error', 'Current password is incorrect');
        } catch (\Exception $e) {
            // ⚠️ MANDATORY: Log error with context
            $this->logError($e, [
                'controller' => 'UserController',
                'action' => 'changePassword',
                'user_id' => auth()->id(),
                'ip_address' => $request->ip(),
            ]);

            if ($request->expectsJson()) {
                return response()->json(['error' => 'Failed to change password'], 500);
            }

            return back()->with('error', 'Failed to change password');
        }
    }
}
