<?php

namespace Packages\User\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Packages\User\Exceptions\UserNotFoundException;
use Packages\User\Repositories\UserRepository;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserAccess
{
    protected UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $targetUserId = $request->route('user') ?? $request->route('id');
        $currentUser = Auth::user();

        if (!$currentUser) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // If no target user ID, allow access (for index routes, etc.)
        if (!$targetUserId) {
            return $next($request);
        }

        try {
            $targetUser = $this->userRepository->findById($targetUserId);
            
            // Check if the authenticated user can access this user record
            // Users can access their own records, admins can access any
            if ($targetUser->id !== $currentUser->id && !$currentUser->hasRole('admin')) {
                return response()->json(['error' => 'Forbidden'], 403);
            }

            // Add target user to request for easy access in controller
            $request->merge(['target_user' => $targetUser]);
            
        } catch (UserNotFoundException $e) {
            return response()->json(['error' => 'User not found'], 404);
        }

        return $next($request);
    }
}
