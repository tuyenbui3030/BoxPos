<?php

namespace Packages\SessionManager\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class ExtendSessionOnActivity
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Chỉ áp dụng cho user đã login
        if (Auth::check()) {
            $this->extendSessionLifetime($request);
        }

        return $next($request);
    }

    /**
     * Extend session lifetime on any user request
     */
    protected function extendSessionLifetime(Request $request): void
    {
        $now = now();
        $user = Auth::user();
        
        // Get session lifetime from config (in minutes)
        $sessionLifetime = config('session-manager.session_lifetime', 120); // default 2 hours
        
        // Update session lifetime
        config(['session.lifetime' => $sessionLifetime]);
        
        // Save activity info for tracking/demo purposes
        session([
            'last_activity_time' => $now,
            'user_last_activity' => $now->timestamp,
            'session_extended_by_middleware' => true,
            'middleware_extension_count' => session('middleware_extension_count', 0) + 1,
            'last_middleware_extension' => $now->toISOString(),
        ]);
        
        // Optional: Update user's last_login_at (throttled to prevent too many DB updates)
        $lastDbUpdate = session('last_db_update', 0);
        $throttleSeconds = config('session-manager.db_update_throttle', 600);
        
        // Only update database according to throttle to avoid too many DB calls
        if ($now->timestamp - $lastDbUpdate > $throttleSeconds) {
            if ($user && $user->hasAttribute('last_login_at')) {
                $user->update(['last_login_at' => $now]);
                session(['last_db_update' => $now->timestamp]);
            }
        }
        
        // Log if debug mode (optional)
        if (config('session-manager.debug.log_extensions', false)) {
            \Log::info('Session extended on user request', [
                'user_id' => $user?->id,
                'ip' => $request->ip(),
                'route' => $request->route()?->getName() ?? 'unknown',
                'session_lifetime_minutes' => $sessionLifetime,
                'timestamp' => $now->toISOString(),
            ]);
        }
    }
}
