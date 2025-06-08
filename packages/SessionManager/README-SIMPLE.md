# Session Manager - Extremely Simple

## Concept

**NO JavaScript needed, NO heartbeat needed, NO notifications needed!**

Just **1 middleware** that automatically extends sessions on any user request.

## How it works

1. User uses the app → generates HTTP requests (click links, submit forms, AJAX, etc.)
2. Middleware `ExtendSessionOnActivity` intercepts all requests
3. Automatically extends session lifetime
4. Done! 🎉

## Installation

### 1. Register Middleware

```php
// app/Http/Kernel.php
protected $middlewareGroups = [
    'web' => [
        // ...existing middleware...
        \Packages\SessionManager\Http\Middleware\ExtendSessionOnActivity::class,
    ],
];
```

### 2. Configure Session Lifetime

```php
// .env
SESSION_LIFETIME_MINUTES=120  # 2 hours
SESSION_DB_THROTTLE_SECONDS=600  # 10 minutes (avoid DB spam)

# Or customize as needed:
SESSION_LIFETIME_MINUTES=1440   # 1 day  
SESSION_LIFETIME_MINUTES=10080  # 7 days
SESSION_LIFETIME_MINUTES=43200  # 30 days
```

## Ví dụ sử dụng

### Scenario 1: User làm việc 8 tiếng

```
08:00 - User login
08:30 - User click vào dashboard → session extend thêm 2 giờ
10:45 - User submit form → session extend thêm 2 giờ  
12:30 - User load report page → session extend thêm 2 giờ
14:15 - User AJAX request → session extend thêm 2 giờ
...
16:00 - User vẫn login, không bị logout!
```

### Scenario 2: User rời máy

```
14:00 - User login và làm việc
15:30 - User cuối cùng thực hiện request
17:30 - Session expire (sau 2 giờ không activity)
17:31 - User quay lại → bị redirect to login
```

## File cấu trúc

```
packages/SessionManager/
├── src/Http/Middleware/ExtendSessionOnActivity.php  ← Core logic
├── config/session-manager.php                       ← Config đơn giản
├── routes/web.php                                    ← Demo routes  
└── resources/views/simple-demo.blade.php            ← Demo page
```

## Core Code

### Middleware (~ 30 dòng code!)

```php
class ExtendSessionOnActivity
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $sessionLifetime = config('session-manager.session_lifetime', 120);
            config(['session.lifetime' => $sessionLifetime]);
            
            session([
                'last_activity_time' => now(),
                'user_last_activity' => now()->toISOString(),
            ]);
        }
        
        return $next($request);
    }
}
```

### Config (~ 10 dòng code!)

```php
return [
    'session_lifetime' => env('SESSION_LIFETIME_MINUTES', 120),
    'db_update_throttle' => env('SESSION_DB_UPDATE_THROTTLE', 600),
];
```

## Demo

Truy cập `/session-manager/demo` để test:

- **Reload page** → session extended
- **Submit form** → session extended  
- **AJAX request** → session extended
- **Bất kỳ action nào** → session extended

## Ưu điểm

✅ **Cực kỳ đơn giản** - chỉ 1 middleware  
✅ **Không cần JavaScript** - pure backend  
✅ **Không cần heartbeat** - leverage natural requests  
✅ **Hoạt động với mọi request** - GET, POST, AJAX, API calls  
✅ **Flexible** - config lifetime theo nhu cầu  
✅ **Performance tốt** - minimal overhead  

## Use Cases

- **Admin panel** - admin làm việc cả ngày không bị logout
- **POS system** - cashier sử dụng liên tục  
- **CRM system** - sales team làm việc lâu
- **Any web app** - user experience tốt hơn

## Kết luận

Đây chính xác là approach mà bạn muốn:

> **"Chỉ đơn giản là cần 1 middleware khi user sử dụng web thì chắc chắn sẽ có request, thì lúc này sẽ update session"**

✨ Simple. Effective. No bullsh*t! ✨
