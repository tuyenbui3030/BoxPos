<?php

namespace App\Livewire;

use Livewire\Component;
use Packages\Log\Traits\Loggable;

class LogoutButton extends Component
{
    use Loggable; // ⚠️ MANDATORY: Use Loggable trait
    
    public $buttonText = 'Logout';
    public $buttonClass = 'dropdown-item';
    public $showIcon = true;

    public function logout()
    {
        $user = auth()->user();
        
        // ⚠️ MANDATORY: Log user action
        $this->logActivity('user_logout_requested', [
            'user_id' => $user?->id,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'component' => 'LogoutButton',
        ]);

        try {
            auth()->logout();
            request()->session()->invalidate();
            request()->session()->regenerateToken();

            // ⚠️ MANDATORY: Log successful logout
            $this->logActivity('user_logout_completed', [
                'user_id' => $user?->id,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'component' => 'LogoutButton',
            ]);
            
            // Use navigate for SPA-like experience, fallback to standard redirect
            return $this->redirect(route('login'), navigate: true);
        } catch (\Exception $e) {
            // ⚠️ MANDATORY: Log error with context
            $this->logError($e, [
                'component' => 'LogoutButton',
                'action' => 'logout',
                'user_id' => $user?->id,
                'ip_address' => request()->ip(),
            ]);

            // Fallback to standard redirect if navigate fails
            return redirect()->route('login');
        }
    }

    public function render()
    {
        return view('livewire.logout-button');
    }
}
