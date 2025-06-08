# Session Manager Package

Quản lý session thông minh cho Laravel với tính năng "never logout" khi user còn hoạt động.

## Tính năng

- ✅ **Infinite Session**: User không bao giờ bị logout khi còn hoạt động
- ✅ **Smart Heartbeat**: Tự động gửi request duy trì session
- ✅ **Activity Tracking**: Theo dõi hoạt động của user
- ✅ **Session Security**: Regenerate session ID định kỳ
- ✅ **Configurable**: Cấu hình linh hoạt
- ✅ **Debug Mode**: Debug và monitoring
- ✅ **Cleanup Command**: Dọn dẹp session cũ

## Cài đặt

### 1. Đăng ký Package trong composer.json

```json
{
    "repositories": [
        {
            "type": "path",
            "url": "./packages/SessionManager"
        }
    ],
    "require": {
        "packages/session-manager": "*"
    }
}
```

### 2. Install package

```bash
sail composer install
```

### 3. Publish config và assets

```bash
sail artisan vendor:publish --tag=session-manager-config
sail artisan vendor:publish --tag=session-manager-assets
```

### 4. Đăng ký Service Provider

Thêm vào `config/app.php`:

```php
'providers' => [
    // ...
    Packages\SessionManager\SessionManagerServiceProvider::class,
],
```

### 5. Thêm middleware

Trong `bootstrap/app.php`:

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->web(append: [
        \Packages\SessionManager\Http\Middleware\KeepAliveSession::class,
    ]);
})
```

### 6. Include JavaScript

Trong layout chính (ví dụ: `layouts/app.blade.php`):

```html
<meta name="user-authenticated" content="{{ auth()->check() ? 'true' : 'false' }}">

<!-- Before closing body tag -->
<script src="{{ asset('js/session-manager/session-keep-alive.js') }}"></script>
```

## Cấu hình

File config: `config/session-manager.php`

```php
return [
    'keep_alive' => [
        'enabled' => true,
        'heartbeat_interval' => 300, // 5 minutes
        'activity_timeout' => 900,   // 15 minutes
        'infinite_session' => true,
    ],
    
    'security' => [
        'regenerate_interval' => 1800, // 30 minutes
    ],
    
    'debug' => [
        'enabled' => false,
        'log_activity' => false,
    ],
];
```

## Environment Variables

Thêm vào `.env`:

```env
SESSION_KEEP_ALIVE_ENABLED=true
SESSION_HEARTBEAT_INTERVAL=300
SESSION_ACTIVITY_TIMEOUT=900
SESSION_INFINITE_ENABLED=true
SESSION_DEBUG_ENABLED=false
```

## API Endpoints

### Heartbeat (Duy trì session)
```
POST /api/session/heartbeat
```

### Session Info
```
GET /api/session/info
```

### Enable/Disable Infinite Session
```
POST /api/session/infinite/enable
POST /api/session/infinite/disable
```

## Sử dụng JavaScript

### Auto-initialization
Package tự động khởi tạo khi detect user đã login.

### Manual control
```javascript
// Access global instance
const keepAlive = window.sessionKeepAlive;

// Enable infinite session
await keepAlive.enableInfiniteSession();

// Disable infinite session
await keepAlive.disableInfiniteSession();

// Destroy
keepAlive.destroy();
```

### Custom instance
```javascript
const customKeepAlive = new SessionKeepAlive({
    heartbeatInterval: 600000, // 10 minutes
    debug: true,
    onInfiniteSessionChanged: (enabled) => {
        console.log('Infinite session:', enabled);
    }
});
```

## Commands

### Cleanup expired sessions
```bash
# Clean sessions older than 30 days
sail artisan session:cleanup

# Custom retention period
sail artisan session:cleanup --days=7

# Dry run (preview only)
sail artisan session:cleanup --dry-run
```

### Schedule cleanup (trong app/Console/Kernel.php)
```php
$schedule->command('session:cleanup')->daily();
```

## Middleware

### KeepAliveSession
Middleware chính để duy trì session:
- Theo dõi hoạt động user
- Gia hạn session tự động
- Thêm headers cho JavaScript

### ExtendSessionOnActivity
Middleware đơn giản chỉ gia hạn session:
- Ít tính năng hơn
- Performance tốt hơn

## Service Class

### SessionManagerService
```php
use Packages\SessionManager\Services\SessionManagerService;

$sessionManager = app(SessionManagerService::class);

// Get session info
$info = $sessionManager->getSessionInfo();

// Handle heartbeat
$result = $sessionManager->handleHeartbeat($request);

// Enable/disable infinite session
$sessionManager->enableInfiniteSession();
$sessionManager->disableInfiniteSession();
```

## Debug & Monitoring

### Check session status
```
GET /debug/session/info
```

### Logs
Khi debug mode bật, logs sẽ được ghi vào `storage/logs/laravel.log`:
- Session extensions
- Heartbeat requests  
- Activity tracking

## Best Practices

1. **Environment-specific**: Chỉ bật debug mode trong development
2. **Performance**: Heartbeat interval không nên quá ngắn (< 60s)
3. **Security**: Định kỳ cleanup sessions cũ
4. **Monitoring**: Theo dõi logs để detect issues

## Troubleshooting

### Session vẫn bị expire
- Kiểm tra `SESSION_LIFETIME` trong `.env`
- Đảm bảo middleware được load
- Kiểm tra JavaScript có chạy không

### Heartbeat không gửi
- Kiểm tra CSRF token
- Kiểm tra network requests trong browser
- Bật debug mode để xem logs

### Performance issues
- Tăng heartbeat interval
- Giảm activity timeout
- Tắt debug logging trong production

## License

MIT License
