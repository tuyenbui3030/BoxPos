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

class UserController extends Controller
{
    protected UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Show the user profile.
     */
    public function profile(Request $request)
    {
        $user = Auth::user();

        if ($request->expectsJson()) {
            return new UserResource($user);
        }

        return view('user::profile', compact('user'));
    }

    /**
     * Show the registration form.
     */
    public function showRegistrationForm()
    {
        return view('user::auth.register');
    }

    /**
     * Handle user registration.
     */
    public function register(RegisterUserRequest $request)
    {
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
        return view('user::auth.login');
    }

    /**
     * Handle user login.
     */
    public function login(LoginUserRequest $request)
    {
        try {
            $user = $this->userService->authenticate(
                $request->input('email'),
                $request->input('password')
            );

            Auth::login($user, $request->boolean('remember'));

            if ($request->expectsJson()) {
                return new UserResource($user);
            }

            $intended = $request->session()->pull('url.intended', route('dashboard'));
            return redirect($intended)
                ->with('success', 'Welcome back!');
        } catch (InvalidCredentialsException $e) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Invalid credentials'], 401);
            }

            return back()->withInput($request->only('email'))
                ->with('error', 'Invalid email or password.');
        } catch (\Exception $e) {
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
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Logged out successfully']);
        }

        return redirect()->route('login')
            ->with('success', 'You have been logged out successfully.');
    }

    /**
     * Update user profile.
     */
    public function updateProfile(UpdateUserRequest $request)
    {
        try {
            $user = $this->userService->updateProfile(Auth::id(), $request->validated());

            if ($request->expectsJson()) {
                return new UserResource($user);
            }

            return back()->with('success', 'Profile updated successfully');
        } catch (UserNotFoundException $e) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'User not found'], 404);
            }

            return back()->with('error', 'User not found');
        } catch (\Exception $e) {
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
        try {
            $this->userService->changePassword(
                Auth::user(),
                $request->input('current_password'),
                $request->input('password')
            );

            if ($request->expectsJson()) {
                return response()->json(['message' => 'Password changed successfully']);
            }

            return back()->with('success', 'Password changed successfully');
        } catch (InvalidCredentialsException $e) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Current password is incorrect'], 422);
            }

            return back()->with('error', 'Current password is incorrect');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Failed to change password'], 500);
            }

            return back()->with('error', 'Failed to change password');
        }
    }
}
