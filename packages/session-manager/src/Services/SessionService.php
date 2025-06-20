<?php

namespace Packages\SessionManager\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Http\Request;
use Packages\Log\Traits\Loggable;
use Carbon\Carbon;

/**
 * Session Service Class
 * 
 * Handles all business logic related to session management operations.
 * Acts as a layer between controllers/middleware and session management.
 */
class SessionService
{
    use Loggable; // ⚠️ MANDATORY: Use Loggable trait

    /**
     * Extend session lifetime for authenticated user
     *
     * @param Request $request
     * @return bool
     */
    public function extendSessionLifetime(Request $request): bool
    {
        // ⚠️ MANDATORY: Log operation performance start
        $startTime = microtime(true);

        try {
            $user = Auth::user();
            
            if (!$user) {
                return false;
            }

            $now = now();
            $sessionLifetime = config('session-manager.session_lifetime', 120);
            
            // ⚠️ MANDATORY: Log user activity
            $this->logActivity('session_extended', [
                'target_user_id' => $user->id,
                'user_email' => $user->email ?? null,
                'user_name' => $user->name ?? null,
                'session_id' => substr(Session::getId(), 0, 8) . '...',
                'session_lifetime_minutes' => $sessionLifetime,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'route' => $request->route()?->getName() ?? 'unknown',
                'action' => 'extend_session',
            ]);

            // Update session lifetime configuration
            config(['session.lifetime' => $sessionLifetime]);
            
            // Save activity information in session
            session([
                'last_activity_time' => $now,
                'user_last_activity' => $now->timestamp,
                'session_extended_by_middleware' => true,
                'middleware_extension_count' => session('middleware_extension_count', 0) + 1,
                'last_middleware_extension' => $now->toISOString(),
            ]);

            // Update user's last_login_at with throttling
            if ($this->shouldUpdateUserLoginTime($user)) {
                $this->updateUserLoginTime($user, $now);
            }

            // ⚠️ MANDATORY: Log operation performance
            $this->logOperationPerformance('session_extension', $startTime, [
                'target_user_id' => $user->id,
                'session_lifetime_minutes' => $sessionLifetime,
                'ip_address' => $request->ip(),
            ]);

            return true;

        } catch (\Exception $e) {
            // ⚠️ MANDATORY: Log error with context
            $this->logError($e, [
                'action' => 'extend_session_lifetime',
                'user_id' => Auth::id(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'route' => $request->route()?->getName() ?? 'unknown',
            ]);
            
            return false;
        }
    }

    /**
     * Get current session information
     *
     * @param Request $request
     * @return array
     */
    public function getSessionInfo(Request $request): array
    {
        // ⚠️ MANDATORY: Log operation performance start
        $startTime = microtime(true);

        try {
            $user = Auth::user();
            
            if (!$user) {
                return [
                    'authenticated' => false,
                    'message' => 'User not authenticated'
                ];
            }

            // ⚠️ MANDATORY: Log user activity
            $this->logActivity('session_info_requested', [
                'target_user_id' => $user->id,
                'user_email' => $user->email ?? null,
                'session_id' => substr(Session::getId(), 0, 8) . '...',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'action' => 'get_session_info',
            ]);

            $sessionInfo = [
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
            ];

            // ⚠️ MANDATORY: Log operation performance
            $this->logOperationPerformance('get_session_info', $startTime, [
                'target_user_id' => $user->id,
                'ip_address' => $request->ip(),
            ]);

            return $sessionInfo;

        } catch (\Exception $e) {
            // ⚠️ MANDATORY: Log error with context
            $this->logError($e, [
                'action' => 'get_session_info',
                'user_id' => Auth::id(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
            
            return [
                'authenticated' => false,
                'error' => 'Failed to retrieve session information'
            ];
        }
    }

    /**
     * Clean up expired sessions
     *
     * @param int $retentionDays
     * @param bool $dryRun
     * @return array
     */
    public function cleanupExpiredSessions(int $retentionDays = 30, bool $dryRun = false): array
    {
        // ⚠️ MANDATORY: Log operation performance start
        $startTime = microtime(true);

        try {
            $cutoffDate = Carbon::now()->subDays($retentionDays);
            
            // ⚠️ MANDATORY: Log user activity
            $this->logActivity('session_cleanup_started', [
                'retention_days' => $retentionDays,
                'cutoff_date' => $cutoffDate->toISOString(),
                'dry_run' => $dryRun,
                'action' => 'cleanup_sessions',
            ]);

            // Get count of sessions to be deleted
            $sessionsQuery = DB::table('sessions')
                ->where('last_activity', '<', $cutoffDate->timestamp);
            
            $sessionCount = $sessionsQuery->count();

            if ($dryRun) {
                $result = [
                    'dry_run' => true,
                    'sessions_to_delete' => $sessionCount,
                    'cutoff_date' => $cutoffDate->toISOString(),
                    'retention_days' => $retentionDays,
                ];

                // ⚠️ MANDATORY: Log user activity
                $this->logActivity('session_cleanup_dry_run', [
                    'sessions_to_delete' => $sessionCount,
                    'retention_days' => $retentionDays,
                    'cutoff_date' => $cutoffDate->toISOString(),
                    'action' => 'cleanup_sessions_dry_run',
                ]);

            } else {
                // Perform actual cleanup
                $deletedCount = $sessionsQuery->delete();

                $result = [
                    'dry_run' => false,
                    'sessions_deleted' => $deletedCount,
                    'cutoff_date' => $cutoffDate->toISOString(),
                    'retention_days' => $retentionDays,
                ];

                // ⚠️ MANDATORY: Log user activity
                $this->logActivity('session_cleanup_completed', [
                    'sessions_deleted' => $deletedCount,
                    'retention_days' => $retentionDays,
                    'cutoff_date' => $cutoffDate->toISOString(),
                    'action' => 'cleanup_sessions_completed',
                ]);
            }

            // ⚠️ MANDATORY: Log operation performance
            $this->logOperationPerformance('session_cleanup', $startTime, [
                'sessions_affected' => $dryRun ? $sessionCount : ($result['sessions_deleted'] ?? 0),
                'retention_days' => $retentionDays,
                'dry_run' => $dryRun,
            ]);

            return $result;

        } catch (\Exception $e) {
            // ⚠️ MANDATORY: Log error with context
            $this->logError($e, [
                'action' => 'cleanup_expired_sessions',
                'retention_days' => $retentionDays,
                'dry_run' => $dryRun,
                'cutoff_date' => isset($cutoffDate) ? $cutoffDate->toISOString() : null,
            ]);
            
            throw $e;
        }
    }

    /**
     * Set infinite session for the currently authenticated user
     * This sets a very long session lifetime (1 year) for "remember me" functionality
     *
     * @return bool
     */
    public function setInfiniteSessionForActiveUser(): bool
    {
        // ⚠️ MANDATORY: Log operation performance start
        $startTime = microtime(true);

        try {
            $user = Auth::user();
            
            if (!$user) {
                return false;
            }

            $now = now();
            $infiniteSessionMinutes = 525600; // 1 year in minutes
            
            // ⚠️ MANDATORY: Log user activity
            $this->logActivity('infinite_session_set', [
                'target_user_id' => $user->id,
                'user_email' => $user->email ?? null,
                'user_name' => $user->name ?? null,
                'session_id' => substr(Session::getId(), 0, 8) . '...',
                'session_lifetime_minutes' => $infiniteSessionMinutes,
                'action' => 'set_infinite_session',
            ]);

            // Update session lifetime configuration for this session
            config(['session.lifetime' => $infiniteSessionMinutes]);
            
            // Save infinite session information
            session([
                'last_activity_time' => $now,
                'user_last_activity' => $now->timestamp,
                'infinite_session_enabled' => true,
                'infinite_session_set_at' => $now->toISOString(),
                'session_lifetime_minutes' => $infiniteSessionMinutes,
            ]);

            // Update user's last_login_at
            $this->updateUserLoginTime($user, $now);

            // ⚠️ MANDATORY: Log operation performance
            $this->logOperationPerformance('set_infinite_session', $startTime, [
                'target_user_id' => $user->id,
                'session_lifetime_minutes' => $infiniteSessionMinutes,
            ]);

            return true;

        } catch (\Exception $e) {
            // ⚠️ MANDATORY: Log error with context
            $this->logError($e, [
                'action' => 'set_infinite_session',
                'user_id' => Auth::id(),
            ]);
            
            return false;
        }
    }

    /**
     * Check if user's login time should be updated (throttling)
     *
     * @param mixed $user
     * @return bool
     */
    protected function shouldUpdateUserLoginTime($user): bool
    {
        if (!$user->hasAttribute('last_login_at')) {
            return false;
        }

        $lastDbUpdate = session('last_db_update', 0);
        $throttleSeconds = config('session-manager.db_update_throttle', 600);
        
        return (now()->timestamp - $lastDbUpdate) > $throttleSeconds;
    }

    /**
     * Update user's last login time
     *
     * @param mixed $user
     * @param Carbon $now
     * @return void
     */
    protected function updateUserLoginTime($user, Carbon $now): void
    {
        try {
            $user->update(['last_login_at' => $now]);
            session(['last_db_update' => $now->timestamp]);

            // ⚠️ MANDATORY: Log user activity
            $this->logActivity('user_login_time_updated', [
                'target_user_id' => $user->id,
                'user_email' => $user->email ?? null,
                'last_login_at' => $now->toISOString(),
                'action' => 'update_login_time',
            ]);

        } catch (\Exception $e) {
            // ⚠️ MANDATORY: Log error with context
            $this->logError($e, [
                'action' => 'update_user_login_time',
                'user_id' => $user->id ?? null,
                'timestamp' => $now->toISOString(),
            ]);
        }
    }

    /**
     * Get session statistics for monitoring
     *
     * @return array
     */
    public function getSessionStatistics(): array
    {
        // ⚠️ MANDATORY: Log operation performance start
        $startTime = microtime(true);

        try {
            // ⚠️ MANDATORY: Log user activity
            $this->logActivity('session_statistics_requested', [
                'action' => 'get_session_statistics',
            ]);

            $now = now()->timestamp;
            $stats = [
                'total_sessions' => DB::table('sessions')->count(),
                'active_sessions_1h' => DB::table('sessions')
                    ->where('last_activity', '>', $now - 3600)->count(),
                'active_sessions_24h' => DB::table('sessions')
                    ->where('last_activity', '>', $now - 86400)->count(),
                'authenticated_sessions' => DB::table('sessions')
                    ->whereNotNull('user_id')->count(),
                'guest_sessions' => DB::table('sessions')
                    ->whereNull('user_id')->count(),
                'timestamp' => now()->toISOString(),
            ];

            // ⚠️ MANDATORY: Log operation performance
            $this->logOperationPerformance('get_session_statistics', $startTime, [
                'total_sessions' => $stats['total_sessions'],
                'active_sessions_1h' => $stats['active_sessions_1h'],
            ]);

            return $stats;

        } catch (\Exception $e) {
            // ⚠️ MANDATORY: Log error with context
            $this->logError($e, [
                'action' => 'get_session_statistics',
            ]);
            
            throw $e;
        }
    }
}
