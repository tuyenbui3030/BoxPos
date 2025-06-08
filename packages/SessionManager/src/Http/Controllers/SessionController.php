<?php

namespace Packages\SessionManager\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Packages\SessionManager\Services\SessionManagerService;

class SessionController extends Controller
{
    protected SessionManagerService $sessionManager;

    public function __construct(SessionManagerService $sessionManager)
    {
        $this->sessionManager = $sessionManager;
    }

    /**
     * Handle heartbeat request to keep session alive
     */
    public function heartbeat(Request $request)
    {
        try {
            $data = $this->sessionManager->handleHeartbeat($request);
            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 401);
        }
    }

    /**
     * Get current session information
     */
    public function sessionInfo(Request $request)
    {
        return response()->json($this->sessionManager->getSessionInfo());
    }

    /**
     * Enable infinite session for current user
     */
    public function enableInfiniteSession(Request $request)
    {
        $this->sessionManager->enableInfiniteSession();
        
        return response()->json([
            'status' => 'success',
            'message' => 'Infinite session enabled',
            'infinite_session' => true
        ]);
    }

    /**
     * Disable infinite session for current user
     */
    public function disableInfiniteSession(Request $request)
    {
        $this->sessionManager->disableInfiniteSession();
        
        return response()->json([
            'status' => 'success',
            'message' => 'Infinite session disabled',
            'infinite_session' => false
        ]);
    }
}
