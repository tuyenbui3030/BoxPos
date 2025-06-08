<?php

namespace Packages\SessionManager\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class SessionController extends Controller
{
    /**
     * Get current session information (for debugging purposes only)
     */
    public function sessionInfo(Request $request)
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json([
                'authenticated' => false,
                'message' => 'User not authenticated'
            ]);
        }
        
        return response()->json([
            'authenticated' => true,
            'session_id' => substr(Session::getId(), 0, 8) . '...',
            'user_id' => $user->id,
            'user_email' => $user->email,
            'session_lifetime_minutes' => config('session-manager.session_lifetime', 120),
            'last_activity' => session('user_last_activity'),
            'middleware_extensions' => session('middleware_extension_count', 0),
            'last_extension' => session('last_middleware_extension'),
            'db_update_throttle_seconds' => config('session-manager.db_update_throttle', 600),
            'timestamp' => now()->toISOString(),
        ]);
    }
}
