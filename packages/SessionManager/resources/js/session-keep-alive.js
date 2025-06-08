/**
 * Session Keep Alive Manager
 * 
 * Automatically sends heartbeat requests to prevent session timeout
 * when user is actively using the application.
 */
class SessionKeepAlive {
    constructor(options = {}) {
        this.options = {
            heartbeatInterval: options.heartbeatInterval || 300000, // 5 phút
            heartbeatUrl: options.heartbeatUrl || '/api/session/heartbeat',
            activityTimeout: options.activityTimeout || 900000, // 15 phút không hoạt động thì dừng
            debug: options.debug || false,
            autoEnable: options.autoEnable !== false,
            ...options
        };
        
        this.lastActivity = Date.now();
        this.heartbeatTimer = null;
        this.activityTimer = null;
        this.isActive = false;
        this.infiniteSessionEnabled = false;
        
        if (this.options.autoEnable) {
            this.init();
        }
    }
    
    init() {
        this.log('Initializing Session Keep Alive');
        this.setupActivityTracking();
        this.startHeartbeat();
    }
    
    setupActivityTracking() {
        // Track user activity
        const activityEvents = ['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart', 'click'];
        
        activityEvents.forEach(event => {
            document.addEventListener(event, () => {
                this.updateActivity();
            }, { passive: true });
        });
        
        // Track page visibility
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                this.pauseHeartbeat();
            } else {
                this.resumeHeartbeat();
            }
        });
    }
    
    updateActivity() {
        const now = Date.now();
        this.lastActivity = now;
        
        if (!this.isActive) {
            this.isActive = true;
            this.log('User became active, resuming heartbeat');
            this.startHeartbeat();
        }
        
        // Reset activity timeout
        if (this.activityTimer) {
            clearTimeout(this.activityTimer);
        }
        
        this.activityTimer = setTimeout(() => {
            this.isActive = false;
            this.log('User inactive, pausing heartbeat');
            this.pauseHeartbeat();
        }, this.options.activityTimeout);
    }
    
    startHeartbeat() {
        if (this.heartbeatTimer) {
            clearInterval(this.heartbeatTimer);
        }
        
        // Send immediate heartbeat
        this.sendHeartbeat();
        
        // Setup recurring heartbeat
        this.heartbeatTimer = setInterval(() => {
            if (this.isActive && !document.hidden) {
                this.sendHeartbeat();
            }
        }, this.options.heartbeatInterval);
        
        this.log('Heartbeat started');
    }
    
    pauseHeartbeat() {
        if (this.heartbeatTimer) {
            clearInterval(this.heartbeatTimer);
            this.heartbeatTimer = null;
        }
        this.log('Heartbeat paused');
    }
    
    resumeHeartbeat() {
        if (!this.heartbeatTimer && this.isActive) {
            this.startHeartbeat();
        }
    }
    
    async sendHeartbeat() {
        try {
            const response = await fetch(this.options.heartbeatUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin',
                body: JSON.stringify({
                    timestamp: Date.now(),
                    last_activity: this.lastActivity,
                    user_agent: navigator.userAgent
                })
            });
            
            if (response.ok) {
                const data = await response.json();
                this.log('Heartbeat sent successfully', data);
                
                // Check if infinite session is enabled
                const infiniteSession = response.headers.get('X-Infinite-Session') === 'true';
                if (infiniteSession !== this.infiniteSessionEnabled) {
                    this.infiniteSessionEnabled = infiniteSession;
                    this.onInfiniteSessionChanged(infiniteSession);
                }
                
                this.onHeartbeatSuccess(data);
            } else if (response.status === 401) {
                this.log('Session expired, redirecting to login');
                this.onSessionExpired();
            } else {
                this.log('Heartbeat failed with status:', response.status);
                this.onHeartbeatError(response);
            }
        } catch (error) {
            this.log('Heartbeat error:', error);
            this.onHeartbeatError(error);
        }
    }
    
    async enableInfiniteSession() {
        try {
            const response = await fetch('/api/session/infinite/enable', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            });
            
            if (response.ok) {
                const data = await response.json();
                this.infiniteSessionEnabled = true;
                this.log('Infinite session enabled');
                this.onInfiniteSessionChanged(true);
                return data;
            }
        } catch (error) {
            this.log('Error enabling infinite session:', error);
        }
    }
    
    async disableInfiniteSession() {
        try {
            const response = await fetch('/api/session/infinite/disable', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            });
            
            if (response.ok) {
                const data = await response.json();
                this.infiniteSessionEnabled = false;
                this.log('Infinite session disabled');
                this.onInfiniteSessionChanged(false);
                return data;
            }
        } catch (error) {
            this.log('Error disabling infinite session:', error);
        }
    }
    
    // Event handlers - can be overridden
    onHeartbeatSuccess(data) {
        // Override this method to handle successful heartbeats
    }
    
    onHeartbeatError(error) {
        // Override this method to handle heartbeat errors
    }
    
    onSessionExpired() {
        // Default: redirect to login
        window.location.href = '/login';
    }
    
    onInfiniteSessionChanged(enabled) {
        // Override this method to handle infinite session state changes
        this.log(`Infinite session ${enabled ? 'enabled' : 'disabled'}`);
    }
    
    destroy() {
        if (this.heartbeatTimer) {
            clearInterval(this.heartbeatTimer);
        }
        if (this.activityTimer) {
            clearTimeout(this.activityTimer);
        }
        this.log('Session Keep Alive destroyed');
    }
    
    log(...args) {
        if (this.options.debug) {
            console.log('[SessionKeepAlive]', ...args);
        }
    }
}

// Auto-initialize nếu user đã login
document.addEventListener('DOMContentLoaded', function() {
    // Check if user is authenticated
    const isAuthenticated = document.querySelector('meta[name="user-authenticated"]')?.content === 'true' ||
                           window.Laravel?.user || 
                           document.body.classList.contains('authenticated');
    
    if (isAuthenticated) {
        // Initialize keep alive system
        window.sessionKeepAlive = new SessionKeepAlive({
            debug: window.Laravel?.app?.debug || false,
            heartbeatInterval: 300000, // 5 phút
            activityTimeout: 900000,   // 15 phút
        });
        
        // Add notification for infinite session
        window.sessionKeepAlive.onInfiniteSessionChanged = function(enabled) {
            if (enabled) {
                // Show notification that infinite session is active
                const notification = document.createElement('div');
                notification.className = 'alert alert-info alert-dismissible fade show position-fixed';
                notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; max-width: 300px;';
                notification.innerHTML = `
                    <div class="d-flex">
                        <div>
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon alert-icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                <circle cx="12" cy="12" r="9"/>
                                <line x1="12" y1="8" x2="12.01" y2="8"/>
                                <polyline points="11,12 12,12 12,16 13,16"/>
                            </svg>
                        </div>
                        <div>
                            <h4 class="alert-title">Session Never Expires!</h4>
                            <div class="text-muted">Bạn sẽ không bao giờ bị logout khi còn hoạt động.</div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                `;
                document.body.appendChild(notification);
                
                // Auto remove after 5 seconds
                setTimeout(() => {
                    if (notification.parentNode) {
                        notification.remove();
                    }
                }, 5000);
            }
        };
        
        console.log('Session Keep Alive initialized - bạn sẽ không bao giờ bị logout khi còn hoạt động!');
    }
});

// Export for module usage
if (typeof module !== 'undefined' && module.exports) {
    module.exports = SessionKeepAlive;
}
