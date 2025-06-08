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
     * Extend session lifetime on user activity
     */
    protected function extendSessionLifetime(Request $request): void
    {
        $user = Auth::user();
        $now = now();
        
        // Lấy thời gian hoạt động cuối từ session
        $lastActivity = session('last_activity_time');
        
        // Chỉ gia hạn nếu đã qua 5 phút kể từ lần gia hạn cuối
        // Điều này tránh việc update quá thường xuyên
        if (!$lastActivity || $now->diffInMinutes($lastActivity) >= 5) {
            
            // Cấu hình session lifetime mới (tính bằng phút)
            $extendedLifetime = config('user.authentication.session_lifetime', 43200); // 30 ngày
            
            // Cập nhật session config
            config(['session.lifetime' => $extendedLifetime]);
            
            // Lưu thời gian hoạt động hiện tại
            session(['last_activity_time' => $now]);
            
            // Cập nhật last_login_at để tracking
            if ($user->hasAttribute('last_login_at')) {
                $user->update(['last_login_at' => $now]);
            }
            
            // Log hoạt động để theo dõi
            \Log::info('Session extended for user activity', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'ip' => $request->ip(),
                'route' => $request->route() ? $request->route()->getName() : 'unknown',
                'extended_lifetime_minutes' => $extendedLifetime,
                'timestamp' => $now->toISOString(),
            ]);
        }
    }
}
