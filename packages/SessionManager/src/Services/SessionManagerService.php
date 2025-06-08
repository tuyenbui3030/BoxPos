<?php

namespace Packages\SessionManager\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class SessionManagerService
{
    /**
     * Get current session information
     */
    public function getSessionInfo(): array
    {
        $user = Auth::user();
        
        return [
            'session_id' => Session::getId(),
            'session_lifetime' => config('session.lifetime'),
            'user_id' => $user?->id,
            'user_email' => $user?->email,
            'last_activity' => session('user_last_activity'),
            'infinite_session_enabled' => $this->isInfiniteSessionEnabled(),
            'keep_alive_enabled' => config('session-manager.keep_alive.enabled', true),
            'heartbeat_interval' => config('session-manager.keep_alive.heartbeat_interval', 300),
            'activity_timeout' => config('session-manager.keep_alive.activity_timeout', 900),
            'session_extended_count' => session('session_extended_count', 0),
            'last_extension_time' => session('last_extension_time'),
        ];
    }

    /**
     * Extend session on user activity
     */
    public function extendSessionOnActivity(Request $request): bool
    {
        if (!Auth::check()) {
            return false;
        }

        $user = Auth::user();
        $now = now();
        
        // Check if we should extend (throttle extensions)
        $lastExtension = session('last_extension_time');
        $threshold = config('session-manager.keep_alive.extend_threshold', 300);
        
        if ($lastExtension && $now->diffInSeconds($lastExtension) < $threshold) {
            return false; // Too soon to extend again
        }

        // Extend session
        $this->performSessionExtension($request, $user, $now);
        
        return true;
    }

    /**
     * Handle heartbeat request
     */
    public function handleHeartbeat(Request $request): array
    {
        if (!Auth::check()) {
            throw new \Exception('Unauthorized');
        }

        $user = Auth::user();
        $this->refreshSessionActivity($request);
        
        // Update last login timestamp (throttled)
        $this->updateLastLogin($user);

        return [
            'status' => 'ok',
            'timestamp' => now()->toISOString(),
            'user_id' => $user->id,
            'session_extended' => true,
            'infinite_session' => $this->isInfiniteSessionEnabled(),
            'next_heartbeat' => now()->addSeconds(
                config('session-manager.keep_alive.heartbeat_interval', 300)
            )->toISOString(),
            'message' => 'Session refreshed successfully'
        ];
    }

    /**
     * Check if infinite session is enabled for current user
     */
    public function isInfiniteSessionEnabled(): bool
    {
        return config('session-manager.keep_alive.infinite_session', true) && 
               Auth::check();
    }

    /**
     * Enable infinite session for current user
     */
    public function enableInfiniteSession(): void
    {
        if (Auth::check()) {
            session(['infinite_session_enabled' => true]);
            session(['infinite_session_started_at' => now()]);
            
            if (config('session-manager.debug.log_activity', false)) {
                Log::info('Infinite session enabled for user', [
                    'user_id' => Auth::id(),
                    'timestamp' => now()->toISOString(),
                ]);
            }
        }
    }

    /**
     * Disable infinite session for current user
     */
    public function disableInfiniteSession(): void
    {
        session()->forget(['infinite_session_enabled', 'infinite_session_started_at']);
        
        if (config('session-manager.debug.log_activity', false)) {
            Log::info('Infinite session disabled for user', [
                'user_id' => Auth::id(),
                'timestamp' => now()->toISOString(),
            ]);
        }
    }

    /**
     * Perform session extension
     */
    protected function performSessionExtension(Request $request, $user, $now): void
    {
        // Update session activity
        session([
            'user_last_activity' => $now->timestamp,
            'last_extension_time' => $now,
            'session_extended_count' => session('session_extended_count', 0) + 1,
        ]);

        // Regenerate session ID for security (throttled)
        $this->regenerateSessionIfNeeded($request);

        if (config('session-manager.debug.log_extensions', false)) {
            Log::info('Session extended for user activity', [
                'user_id' => $user->id,
                'ip' => $request->ip(),
                'route' => $request->route()?->getName() ?? 'unknown',
                'timestamp' => $now->toISOString(),
            ]);
        }
    }

    /**
     * Refresh session activity
     */
    protected function refreshSessionActivity(Request $request): void
    {
        $now = now();
        
        session([
            'user_last_activity' => $now->timestamp,
            'user_activity_ip' => $request->ip(),
            'heartbeat_count' => session('heartbeat_count', 0) + 1,
            'last_heartbeat' => $now,
        ]);
    }

    /**
     * Update user's last login timestamp (throttled)
     */
    protected function updateLastLogin($user): void
    {
        $lastUpdate = session('last_db_update', 0);
        $now = now();
        
        // Only update database every 10 minutes
        if ($now->timestamp - $lastUpdate > 600) {
            if ($user->hasAttribute('last_login_at')) {
                $user->update(['last_login_at' => $now]);
            }
            session(['last_db_update' => $now->timestamp]);
        }
    }

    /**
     * Regenerate session ID if needed for security
     */
    protected function regenerateSessionIfNeeded(Request $request): void
    {
        $lastRegenerate = session('last_session_regenerate', 0);
        $interval = config('session-manager.security.regenerate_interval', 1800);
        $now = now();
        
        if ($now->timestamp - $lastRegenerate > $interval) {
            $request->session()->regenerate();
            session(['last_session_regenerate' => $now->timestamp]);
        }
    }
}
