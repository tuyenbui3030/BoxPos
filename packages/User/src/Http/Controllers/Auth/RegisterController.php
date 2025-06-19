<?php

namespace Packages\User\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Packages\User\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Validator;
use Packages\Log\Traits\Loggable;

class RegisterController extends Controller
{
    use Loggable; // ⚠️ MANDATORY: Use Loggable trait

    public function __construct()
    {
        // Apply logging middleware to auth actions
        $this->middleware('log.requests')->only(['showRegistrationForm', 'register']);
        $this->middleware('log.sql')->only(['register']);
    }
{
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

        return view('auth.register');
    }
    
    /**
     * Handle an incoming registration request.
     */
    public function register(Request $request)
    {
        // ⚠️ MANDATORY: Log user action
        $this->logActivity('registration_form_submitted', [
            'email' => $request->input('email'),
            'name' => $request->input('name'),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
        
        if ($validator->fails()) {
            // ⚠️ MANDATORY: Log validation failure
            $this->logActivity('registration_validation_failed', [
                'email' => $request->input('email'),
                'validation_errors' => $validator->errors()->toArray(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return redirect()->route('register')
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);
            
            // ⚠️ MANDATORY: Log successful registration
            $this->logActivity('user_registered_successfully', [
                'target_user_id' => $user->id,
                'user_email' => $user->email,
                'user_name' => $user->name,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            // ⚠️ MANDATORY: Log model event
            $this->logModelEvent('created', $user, [
                'registration_source' => 'auth_controller',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
            
            event(new Registered($user));
            
            Auth::login($user);

            // ⚠️ MANDATORY: Log authentication after registration
            $this->logActivity('user_authenticated_after_registration', [
                'target_user_id' => $user->id,
                'user_email' => $user->email,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
            
            return redirect()->route('dashboard');
        } catch (\Exception $e) {
            // ⚠️ MANDATORY: Log error with context
            $this->logError($e, [
                'controller' => 'RegisterController',
                'action' => 'register',
                'email' => $request->input('email'),
                'ip_address' => $request->ip(),
            ]);

            return redirect()->route('register')
                ->withInput($request->only('name', 'email'))
                ->with('error', 'Registration failed. Please try again.');
        }
    }
}
