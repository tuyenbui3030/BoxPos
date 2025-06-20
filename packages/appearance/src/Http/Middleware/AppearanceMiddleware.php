<?php

namespace Packages\Appearance\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AppearanceMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Set default theme if not already set in session
        if (!session()->has('theme')) {
            session(['theme' => 'light']);
        }
        
        return $next($request);
    }
}
