<?php

namespace Packages\User\Livewire;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Rule;
use Packages\User\Services\DeviceDetectionService;
use Packages\SessionManager\Services\SessionService;

class Login extends Component
{
    #[Rule('required|email')]
    public $email = '';

    #[Rule('required|min:6')]
    public $password = '';

    public $remember = false;

    public function mount()
    {
        \Log::info('Login component mounted');

        // Redirect if already authenticated
        if (Auth::check()) {
            \Log::info('User already authenticated, redirecting to dashboard');
            return redirect()->route('dashboard');
        }
    }

    public function login()
    {
        \Log::info('Livewire login method called', [
            'email' => $this->email,
            'remember' => $this->remember
        ]);

        // Check if user is locked out
        $lockoutTime = session()->get('login_lockout_time');
        if ($lockoutTime && now()->lt($lockoutTime)) {
            $remainingMinutes = now()->diffInMinutes($lockoutTime);
            $this->addError('email', "Account temporarily locked. Try again in {$remainingMinutes} minutes.");
            return;
        }

        $this->validate();

        // Attempt authentication with remember me functionality
        if (Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            session()->regenerate();

            $user = Auth::user();

            \Log::info('User authenticated successfully in Livewire', [
                'user_id' => $user->id,
                'user_email' => $user->email
            ]);

            // Update basic login tracking
            $user->update([
                'last_login_at' => now(),
                'last_login_ip' => request()->ip(),
            ]);

            // Record device information if remember me is enabled
            if ($this->remember) {
                $deviceService = app(DeviceDetectionService::class);
                $device = $deviceService->detectAndRecordDevice($user, $user->remember_token);

                // Activate infinite session for remember me users
                $sessionManager = app(SessionService::class);
                $sessionManager->setInfiniteSessionForActiveUser();

                session()->flash('message', "Infinite session activated! Bạn sẽ không bao giờ bị logout trên thiết bị này ({$device->device_name}).");
            }

            // Clear any previous failed login attempts
            session()->forget(['login_attempts', 'login_lockout_time']);

            \Log::info('Redirecting to dashboard', [
                'dashboard_route' => route('dashboard')
            ]);

            // Redirect to dashboard using standard redirect (no navigate)
            $this->redirect(route('dashboard'));

        } else {
            // Handle failed login attempts
            $this->handleFailedLogin();
        }
    }
    
    private function handleFailedLogin()
    {
        $attempts = session()->get('login_attempts', 0) + 1;
        session()->put('login_attempts', $attempts);
        
        if ($attempts >= 5) {
            session()->put('login_lockout_time', now()->addMinutes(15));
            $this->addError('email', 'Too many failed attempts. Please try again in 15 minutes.');
            return;
        }
        
        $remaining = 5 - $attempts;
        $this->addError('email', "Invalid credentials. You have {$remaining} attempts remaining.");
    }

    #[Title('Login')]
    public function render()
    {
        return view('user::livewire.login')
            ->layout('layouts.guest');
    }
}
