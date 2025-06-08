<?php

namespace Packages\User\Livewire;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Rule;
use App\Services\DeviceDetectionService;

class Login extends Component
{
    #[Rule('required|email')]
    public $email = '';

    #[Rule('required|min:6')]
    public $password = '';

    public $remember = false;

    public function login()
    {
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
            
            // Update basic login tracking
            $user->update([
                'last_login_at' => now(),
                'last_login_ip' => request()->ip(),
            ]);
            
            // Record device information if remember me is enabled
            if ($this->remember) {
                $deviceService = new DeviceDetectionService(request());
                $device = $deviceService->detectAndRecordDevice($user, $user->remember_token);
                
                // Activate infinite session for remember me users
                $sessionManager = new \App\Services\SessionManagerService();
                $sessionManager->setInfiniteSessionForActiveUser();
                
                session()->flash('message', "Infinite session activated! Bạn sẽ không bao giờ bị logout trên thiết bị này ({$device->device_name}).");
            }
            
            // Clear any previous failed login attempts
            session()->forget(['login_attempts', 'login_lockout_time']);
            
            return redirect()->intended(route('dashboard'));
        }

        // Handle failed login attempts
        $this->handleFailedLogin();
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
        return view('livewire.login')
            ->layout('layouts.guest');
    }
}
