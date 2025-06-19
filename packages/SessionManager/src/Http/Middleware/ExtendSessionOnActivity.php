<?php

namespace Packages\SessionManager\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Packages\SessionManager\Services\SessionService;
use Packages\Log\Traits\Loggable;
use Symfony\Component\HttpFoundation\Response;

class ExtendSessionOnActivity
{
    use Loggable; // ⚠️ MANDATORY: Use Loggable trait

    /**
     * Session service instance
     *
     * @var SessionService
     */
    protected SessionService $sessionService;

    /**
     * Constructor
     *
     * @param SessionService $sessionService
     */
    public function __construct(SessionService $sessionService)
    {
        $this->sessionService = $sessionService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // ⚠️ MANDATORY: Log operation performance start
        $startTime = microtime(true);

        try {
            // Only apply to authenticated users
            if (Auth::check()) {
                $success = $this->sessionService->extendSessionLifetime($request);
                
                if (!$success) {
                    // ⚠️ MANDATORY: Log user activity for failed session extension
                    $this->logActivity('session_extension_failed', [
                        'user_id' => Auth::id(),
                        'ip_address' => $request->ip(),
                        'user_agent' => $request->userAgent(),
                        'route' => $request->route()?->getName() ?? 'unknown',
                        'action' => 'session_extension_middleware',
                    ]);
                }
            }

            $response = $next($request);

            // ⚠️ MANDATORY: Log operation performance
            $this->logOperationPerformance('session_extension_middleware', $startTime, [
                'user_id' => Auth::id(),
                'ip_address' => $request->ip(),
                'route' => $request->route()?->getName() ?? 'unknown',
                'authenticated' => Auth::check(),
            ]);

            return $response;

        } catch (\Exception $e) {
            // ⚠️ MANDATORY: Log error with context
            $this->logError($e, [
                'action' => 'session_extension_middleware',
                'user_id' => Auth::id(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'route' => $request->route()?->getName() ?? 'unknown',
            ]);
            
            // Continue with request even if session extension fails
            return $next($request);
        }
    }
}
