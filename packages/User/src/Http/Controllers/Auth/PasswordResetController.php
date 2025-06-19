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

class PasswordResetController extends Controller
{
    use Loggable; // ⚠️ MANDATORY: Use Loggable trait

    public function __construct()
    {
        // Apply logging middleware to password reset actions
        $this->middleware('log.requests')->only(['create', 'store']);
    }
{
    /**
     * Display the password reset link request view.
     */
    public function create(): View
    {
        // ⚠️ MANDATORY: Log user action
        $this->logActivity('password_reset_form_accessed', [
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'referrer' => request()->headers->get('referer'),
        ]);

        return view('auth.forgot-password');
    }

    /**
     * Handle an incoming password reset link request.
     */
    public function store(Request $request): RedirectResponse
    {
        // ⚠️ MANDATORY: Log user action
        $this->logActivity('password_reset_link_requested', [
            'email' => $request->input('email'),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        $request->validate([
            'email' => ['required', 'email'],
        ]);

        try {
            // We will send the password reset link to this user. Once we have attempted
            // to send the link, we will examine the response then see the message we
            // need to show to the user. Finally, we'll send out a proper response.
            $status = Password::sendResetLink(
                $request->only('email')
            );

            if ($status == Password::RESET_LINK_SENT) {
                // ⚠️ MANDATORY: Log successful password reset link sent
                $this->logActivity('password_reset_link_sent', [
                    'email' => $request->input('email'),
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);

                return back()->with('status', __($status));
            } else {
                // ⚠️ MANDATORY: Log failed password reset link request
                $this->logActivity('password_reset_link_failed', [
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
                'controller' => 'PasswordResetController',
                'action' => 'store',
                'email' => $request->input('email'),
                'ip_address' => $request->ip(),
            ]);

            return back()->withInput($request->only('email'))
                ->with('error', 'Failed to send password reset link. Please try again.');
        }
    }
}
