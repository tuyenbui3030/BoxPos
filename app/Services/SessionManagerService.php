<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SessionManagerService
{
    /**
     * Extend session lifetime for active users
     */
    public function extendSessionForActiveUser(): bool
    {
        if (!Auth::check()) {
            return false;
        }
        
        $user = Auth::user();
        $now = Carbon::now();
        
        // Get current session configuration
        $currentLifetime = config('session.lifetime', 43200); // 30 days in minutes
        $extendedLifetime = config('user.authentication.session_lifetime', 43200);
        
        // Check if user should get extended session
        if ($this->shouldExtendSession($user, $now)) {
            // Update session configuration temporarily
            Session::put('extended_session', true);
            Session::put('session_extended_at', $now->timestamp);
            
            // Log the extension
            Log::info('Session extended for user', [
                'user_id' => $user->id,
                'extended_lifetime_minutes' => $extendedLifetime,
                'timestamp' => $now->toISOString()
            ]);
            
            return true;
        }
        
        return false;
    }
    
    /**
     * Check if session should be extended
     */
    protected function shouldExtendSession($user, Carbon $now): bool
    {
        // Check if session was recently extended (avoid too frequent extensions)
        $lastExtension = Session::get('session_extended_at', 0);
        if ($now->timestamp - $lastExtension < 300) { // 5 minutes cooldown
            return false;
        }
        
        // Check user activity
        $lastActivity = Session::get('user_last_activity', 0);
        if ($now->timestamp - $lastActivity > 1800) { // 30 minutes inactive
            return false;
        }
        
        return true;
    }
    
    /**
     * Set session to never expire for active users
     */
    public function setInfiniteSessionForActiveUser(): bool
    {
        if (!Auth::check()) {
            return false;
        }
        
        $user = Auth::user();
        
        // Mark session as infinite
        Session::put('infinite_session', true);
        Session::put('infinite_session_started_at', now()->timestamp);
        Session::put('user_id', $user->id);
        
        // Set a very long lifetime (1 year in minutes)
        $infiniteLifetime = 525600; // 1 year
        config(['session.lifetime' => $infiniteLifetime]);
        
        Log::info('Infinite session activated for user', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'timestamp' => now()->toISOString()
        ]);
        
        return true;
    }
    
    /**
     * Check if current session is infinite
     */
    public function hasInfiniteSession(): bool
    {
        return Session::get('infinite_session', false) && Auth::check();
    }
    
    /**
     * Refresh session activity timestamp
     */
    public function refreshActivity(): void
    {
        Session::put('user_last_activity', now()->timestamp);
        Session::put('last_page_load', now()->timestamp);
    }
    
    /**
     * Get session info for debugging
     */
    public function getSessionInfo(): array
    {
        if (!Auth::check()) {
            return ['authenticated' => false];
        }
        
        return [
            'authenticated' => true,
            'user_id' => Auth::id(),
            'session_id' => Session::getId(),
            'infinite_session' => $this->hasInfiniteSession(),
            'extended_session' => Session::get('extended_session', false),
            'last_activity' => Session::get('user_last_activity', 0),
            'session_lifetime' => config('session.lifetime'),
            'session_extended_at' => Session::get('session_extended_at', 0),
            'infinite_session_started_at' => Session::get('infinite_session_started_at', 0),
        ];
    }
}
