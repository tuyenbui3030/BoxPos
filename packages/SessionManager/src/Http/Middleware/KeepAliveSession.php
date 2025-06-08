<?php

namespace Packages\SessionManager\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Packages\SessionManager\Services\SessionManagerService;
use Symfony\Component\HttpFoundation\Response;

class KeepAliveSession
{
    protected SessionManagerService $sessionManager;

    public function __construct(SessionManagerService $sessionManager)
    {
        $this->sessionManager = $sessionManager;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only apply to authenticated users with keep alive enabled
        if (Auth::check() && config('session-manager.keep_alive.enabled', true)) {
            $this->sessionManager->extendSessionOnActivity($request);
        }

        $response = $next($request);

        // Add headers for JavaScript integration
        if (Auth::check()) {
            $response->headers->set('X-Session-Extended', 'true');
            $response->headers->set('X-Session-Lifetime', config('session.lifetime', 43200));
            $response->headers->set('X-Infinite-Session', $this->sessionManager->isInfiniteSessionEnabled() ? 'true' : 'false');
        }

        return $response;
    }
}
