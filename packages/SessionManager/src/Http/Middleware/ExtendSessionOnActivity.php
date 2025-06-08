<?php

namespace Packages\SessionManager\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Packages\SessionManager\Services\SessionManagerService;
use Symfony\Component\HttpFoundation\Response;

class ExtendSessionOnActivity
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
        // Extend session for authenticated users
        if (Auth::check() && config('session-manager.keep_alive.enabled', true)) {
            $this->sessionManager->extendSessionOnActivity($request);
        }

        return $next($request);
    }
}
