<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class LocalizationMiddleware
{
    /**
     * Handle an incoming request - Đơn giản hóa cho /vi và /en
     */
    public function handle(Request $request, Closure $next): Response
    {
        $path = $request->path();
        $locale = 'en'; // Default



        // Kiểm tra URL có bắt đầu bằng /vi hoặc /en không
        if (str_starts_with($path, 'vi/') || $path === 'vi') {
            $locale = 'vi';
        } elseif (str_starts_with($path, 'en/') || $path === 'en') {
            $locale = 'en';
        } else {
            // Nếu không có locale trong URL, lấy từ session
            $sessionLocale = Session::get('app_locale', 'en');
            $locale = in_array($sessionLocale, ['vi', 'en']) ? $sessionLocale : 'en';

            // Redirect đến URL có locale nếu không phải API hoặc asset
            if (!str_starts_with($path, 'api/') &&
                !str_starts_with($path, 'livewire/') &&
                !str_contains($path, '.')) {

                if ($path === '/') {
                    return redirect("/$locale");
                } else {
                    return redirect("/$locale/$path");
                }
            }
        }

        // Set application locale và lưu vào session
        App::setLocale($locale);
        Session::put('app_locale', $locale);



        return $next($request);
    }
}
