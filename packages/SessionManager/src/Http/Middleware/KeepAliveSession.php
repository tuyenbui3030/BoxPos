<?php

namespace Packages\SessionManager\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class KeepAliveSession
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
            $this->refreshSessionActivity($request);
        }

        $response = $next($request);

        // Thêm header để JavaScript có thể track hoạt động
        if (Auth::check()) {
            $response->headers->set('X-Session-Extended', 'true');
            $response->headers->set('X-Session-Lifetime', config('session.lifetime', 43200));
        }

        return $response;
    }

    /**
     * Refresh session activity and prevent timeout
     */
    protected function refreshSessionActivity(Request $request): void
    {
        $user = Auth::user();
        $now = now();
        
        // Lưu timestamp hoạt động cuối
        session(['user_last_activity' => $now->timestamp]);
        session(['user_activity_ip' => $request->ip()]);
        
        // Regenerate session ID để bảo mật (mỗi 30 phút)
        $lastRegenerate = session('last_session_regenerate', 0);
        if ($now->timestamp - $lastRegenerate > 1800) { // 30 phút
            $request->session()->regenerate();
            session(['last_session_regenerate' => $now->timestamp]);
        }
        
        // Cập nhật thời gian login cuối (throttle: chỉ update mỗi 10 phút)
        $lastUpdate = session('last_db_update', 0);
        if ($now->timestamp - $lastUpdate > 600) { // 10 phút
            if ($user->hasAttribute('last_login_at')) {
                $user->update(['last_login_at' => $now]);
            }
            session(['last_db_update' => $now->timestamp]);
        }
    }
}
