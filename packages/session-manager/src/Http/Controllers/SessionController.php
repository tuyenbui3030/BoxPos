<?php

namespace Packages\SessionManager\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Packages\SessionManager\Services\SessionService;
use Packages\Log\Traits\Loggable;

class SessionController extends Controller
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
     * Get current session information (for debugging purposes only)
     */
    public function sessionInfo(Request $request)
    {
        // ⚠️ MANDATORY: Log operation performance start
        $startTime = microtime(true);

        try {
            // ⚠️ MANDATORY: Log user activity for controller action
            $this->logActivity('session_info_controller_accessed', [
                'user_id' => auth()->id(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'route' => $request->route()?->getName() ?? 'session.info',
                'action' => 'get_session_info_endpoint',
            ]);

            $sessionInfo = $this->sessionService->getSessionInfo($request);

            // ⚠️ MANDATORY: Log operation performance
            $this->logOperationPerformance('session_info_controller', $startTime, [
                'user_id' => auth()->id(),
                'ip_address' => $request->ip(),
                'authenticated' => $sessionInfo['authenticated'] ?? false,
            ]);

            return response()->json($sessionInfo);

        } catch (\Exception $e) {
            // ⚠️ MANDATORY: Log error with context
            $this->logError($e, [
                'action' => 'session_info_controller',
                'user_id' => auth()->id(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
            
            return response()->json([
                'authenticated' => false,
                'error' => 'Failed to retrieve session information'
            ], 500);
        }
    }

    /**
     * Get session statistics (for monitoring purposes)
     */
    public function sessionStats(Request $request)
    {
        // ⚠️ MANDATORY: Log operation performance start
        $startTime = microtime(true);

        try {
            // ⚠️ MANDATORY: Log user activity for controller action
            $this->logActivity('session_stats_controller_accessed', [
                'user_id' => auth()->id(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'route' => $request->route()?->getName() ?? 'session.stats',
                'action' => 'get_session_stats_endpoint',
            ]);

            $stats = $this->sessionService->getSessionStatistics();

            // ⚠️ MANDATORY: Log operation performance
            $this->logOperationPerformance('session_stats_controller', $startTime, [
                'user_id' => auth()->id(),
                'ip_address' => $request->ip(),
                'total_sessions' => $stats['total_sessions'] ?? 0,
            ]);

            return response()->json($stats);

        } catch (\Exception $e) {
            // ⚠️ MANDATORY: Log error with context
            $this->logError($e, [
                'action' => 'session_stats_controller',
                'user_id' => auth()->id(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
            
            return response()->json([
                'error' => 'Failed to retrieve session statistics'
            ], 500);
        }
    }
}
