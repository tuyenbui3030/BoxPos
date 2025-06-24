<?php

namespace Packages\User\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

class ValidateCurrentPassword
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        $currentPassword = $request->input('current_password');

        // Only validate current password for password change requests
        if ($request->has('password') && $request->has('current_password')) {
            if (!$user || !Hash::check($currentPassword, $user->password)) {
                return response()->json([
                    'error' => 'Invalid current password',
                    'message' => 'The provided current password is incorrect.',
                ], 422);
            }
        }

        return $next($request);
    }
}
