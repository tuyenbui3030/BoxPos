# Session Manager Package

Simple session management for Laravel that automatically extends session lifetime on user activity.

## Features

- ✅ **Automatic Session Extension**: Sessions are automatically extended on any HTTP request
- ✅ **Zero JavaScript Required**: Pure server-side solution
- ✅ **Configurable Session Lifetime**: Set session duration (1-3 hours, 1-3 days, etc.)
- ✅ **Database Update Throttling**: Optimize performance with configurable update intervals
- ✅ **Cleanup Command**: Remove expired sessions

## Installation

### 1. Register Package in composer.json

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

### 3. Publish config

```bash
sail artisan vendor:publish --tag=session-manager-config
```

### 4. Register Service Provider

Add to `config/app.php`:

```php
'providers' => [
    // ...
    Packages\SessionManager\SessionManagerServiceProvider::class,
],
```

### 5. Register Middleware

Add to `app/Http/Kernel.php`:

```php
protected $middlewareGroups = [
    'web' => [
        // ...existing middleware...
        \Packages\SessionManager\Http\Middleware\ExtendSessionOnActivity::class,
    ],
];
```

## Configuration

File config: `config/session-manager.php`

```php
return [
    // Session lifetime in minutes (default: 2 hours)
    'session_lifetime' => 120,
    
    // Database update throttle in seconds (default: 10 minutes)
    // Prevents excessive database writes from frequent requests
    'db_update_throttle' => 600,
];
```

## How It Works

1. **Automatic Extension**: When an authenticated user makes any HTTP request, the middleware automatically extends their session lifetime
2. **Zero JavaScript**: No client-side code required - everything happens server-side
3. **Performance Optimized**: Database updates are throttled to prevent excessive writes
4. **Simple Configuration**: Just two settings to configure

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

### Schedule cleanup (in app/Console/Kernel.php)
```php
$schedule->command('session:cleanup')->daily();
```

## Example Usage

Once installed and configured, the system works automatically:

1. User logs in normally
2. As they use the application (any HTTP request), their session is automatically extended
3. Session stays active as long as they continue using the application
4. Sessions expire only after the configured period of inactivity

## Configuration Examples

```php
// Short sessions (1 hour, update every 5 minutes)
'session_lifetime' => 60,
'db_update_throttle' => 300,

// Medium sessions (3 hours, update every 10 minutes)  
'session_lifetime' => 180,
'db_update_throttle' => 600,

// Long sessions (1 day, update every 30 minutes)
'session_lifetime' => 1440,
'db_update_throttle' => 1800,
```

## Benefits

- **Simple**: No complex JavaScript or heartbeat systems
- **Efficient**: Minimal overhead on each request
- **Reliable**: Works with any HTTP request, not just specific AJAX calls
- **Maintainable**: Clean, minimal codebase
- **Flexible**: Configurable session lengths for different use cases

## License

MIT License
