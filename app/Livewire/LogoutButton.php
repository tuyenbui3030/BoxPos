<?php

namespace App\Livewire;

use Livewire\Component;
use Packages\Log\Traits\Loggable;
use Packages\Appearance\Traits\HasConfirmationModal;

class LogoutButton extends Component
{
    use Loggable; // ⚠️ MANDATORY: Use Loggable trait
    use HasConfirmationModal; // Use confirmation modal trait

    public $buttonText = 'Logout';
    public $buttonClass = 'dropdown-item';
    public $showIcon = true;

    protected $listeners = ['logout'];

    public function confirmLogout()
    {
        // ⚠️ MANDATORY: Log user action
        $this->logActivity('logout_confirmation_shown', [
            'user_id' => auth()->user()?->id,
            'ip_address' => request()->ip(),
            'component' => 'LogoutButton',
        ]);

        $this->confirmation()
            ->title('Confirm Logout')
            ->message('Are you sure you want to logout? You will need to login again to access your account.')
            ->logout()
            ->action('logout')
            ->cancelText('Cancel')
            ->show();
    }

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

        // Add a small delay for better UX (show "Processing..." for a moment)
        sleep(1); // 1 second delay để user thấy loading state

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
