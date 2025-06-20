<?php

namespace Packages\User\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Packages\Log\Traits\Loggable;

class NewPasswordController extends Controller
{
    use Loggable; // ⚠️ MANDATORY: Use Loggable trait

    public function __construct()
    {
        // Apply logging middleware to password reset actions
        $this->middleware('log.requests')->only(['create', 'store']);
    }
{
    /**
     * Display the password reset view.
     */
    public function create(Request $request): View
    {
        // ⚠️ MANDATORY: Log user action
        $this->logActivity('password_reset_form_accessed', [
            'token' => $request->route('token') ? 'present' : 'missing',
            'email' => $request->input('email'),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'referrer' => $request->headers->get('referer'),
        ]);

        return view('auth.reset-password', ['request' => $request]);
    }

    /**
     * Handle an incoming new password request.
     */
    public function store(Request $request): RedirectResponse
    {
        // ⚠️ MANDATORY: Log user action
        $this->logActivity('password_reset_form_submitted', [
            'email' => $request->input('email'),
            'token' => $request->input('token') ? 'present' : 'missing',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'min:8', 'confirmed'],
        ]);

        try {
            // Here we will attempt to reset the user's password. If it is successful we
            // will update the password on an actual user model and persist it to the
            // database. Otherwise we will parse the error and return the response.
            $status = Password::reset(
                $request->only('email', 'password', 'password_confirmation', 'token'),
                function ($user) use ($request) {
                    // ⚠️ MANDATORY: Log password reset for specific user
                    $this->logActivity('user_password_reset_via_token', [
                        'target_user_id' => $user->id,
                        'user_email' => $user->email,
                        'ip_address' => $request->ip(),
                        'user_agent' => $request->userAgent(),
                    ]);

                    // ⚠️ MANDATORY: Log model event
                    $this->logModelEvent('updated', $user, [
                        'updated_fields' => ['password', 'remember_token'],
                        'change_type' => 'password_reset_via_token',
                        'ip_address' => $request->ip(),
                    ]);

                    $user->forceFill([
                        'password' => Hash::make($request->password),
                        'remember_token' => Str::random(60),
                    ])->save();

                    event(new PasswordReset($user));
                }
            );

            if ($status == Password::PASSWORD_RESET) {
                // ⚠️ MANDATORY: Log successful password reset
                $this->logActivity('password_reset_completed', [
                    'email' => $request->input('email'),
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);

                return redirect()->route('login')->with('status', __($status));
            } else {
                // ⚠️ MANDATORY: Log failed password reset
                $this->logActivity('password_reset_failed', [
                    'email' => $request->input('email'),
                    'failure_reason' => $status,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);

                return back()->withInput($request->only('email'))
                        ->withErrors(['email' => __($status)]);
            }
        } catch (\Exception $e) {
            // ⚠️ MANDATORY: Log error with context
            $this->logError($e, [
                'controller' => 'NewPasswordController',
                'action' => 'store',
                'email' => $request->input('email'),
                'ip_address' => $request->ip(),
            ]);

            return back()->withInput($request->only('email'))
                ->with('error', 'Password reset failed. Please try again.');
        }
    }
}
