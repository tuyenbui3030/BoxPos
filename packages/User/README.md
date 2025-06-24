# User Package

This package provides comprehensive user management and authentication functionality for the BoxPos2 application.

## Architecture

The User package follows Laravel best practices and clean architecture principles with a focus on security and authentication:

### Directory Structure

```
packages/User/
├── src/
│   ├── Services/           # Business logic layer
│   ├── Repositories/       # Data access layer
│   ├── Http/
│   │   ├── Controllers/    # HTTP request handling
│   │   ├── Requests/       # Form validation
│   │   ├── Resources/      # API response formatting
│   │   └── Middleware/     # Request middleware
│   ├── Events/            # Domain events
│   ├── Listeners/         # Event handlers
│   ├── Jobs/              # Background jobs
│   ├── Exceptions/        # Custom exceptions
│   ├── Tests/             # Unit and feature tests
│   ├── Models/            # Eloquent models
│   ├── Livewire/          # Livewire components
│   └── Database/          # Migrations
├── config/                # Package configuration
├── resources/
│   └── views/             # Blade templates
└── routes/                # Package routes
```

## Components

### Services
- **UserService**: Main business logic for user operations
  - Registration and authentication
  - Profile management
  - Password operations
  - Event dispatching

### Repositories
- **UserRepository**: Data access abstraction
  - User queries
  - Search and filtering
  - Relationship management

### Form Requests
- **RegisterUserRequest**: User registration validation
- **LoginUserRequest**: Login validation
- **UpdateUserRequest**: Profile update validation
- **ChangePasswordRequest**: Password change validation

### API Resources
- **UserResource**: API response formatting for single users
- **UserCollection**: API response formatting for user collections

### Events & Listeners
- **UserRegistered** → SendWelcomeEmail
- **UserLoggedIn** → LogUserLogin
- **UserPasswordChanged** → NotifyPasswordChange

### Jobs
- **SendWelcomeEmailJob**: Welcome email after registration
- **SendPasswordChangeNotificationJob**: Password change notifications
- **ExportUsersJob**: Background user data export

### Middleware
- **EnsureUserAccess**: Access control for user operations
- **LogUserAuthentication**: Logs authentication attempts
- **AuthenticationRateLimiter**: Rate limiting for auth endpoints
- **ValidateCurrentPassword**: Validates current password for changes

### Exceptions
- **UserNotFoundException**: When user doesn't exist
- **InvalidCredentialsException**: For authentication failures
- **UserValidationException**: For business rule violations

## Usage Examples

### User Registration

```php
use Packages\User\Services\UserService;

// Inject service via dependency injection
public function __construct(private UserService $userService) {}

// Register a new user
$user = $this->userService->register([
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'password' => 'secure-password'
]);
```

### Authentication

```php
// Authenticate user
$result = $this->userService->authenticate([
    'email' => 'john@example.com',
    'password' => 'user-password'
]);

if ($result['success']) {
    $user = $result['user'];
    $token = $result['token']; // if using API tokens
}
```

### Profile Management

```php
// Update user profile
$user = $this->userService->updateProfile($userId, [
    'name' => 'Jane Doe',
    'email' => 'jane@example.com'
]);

// Change password
$this->userService->changePassword($userId, [
    'current_password' => 'old-password',
    'password' => 'new-password',
    'password_confirmation' => 'new-password'
]);
```

### Using Repository

```php
use Packages\User\Repositories\UserRepository;

// Inject repository
public function __construct(private UserRepository $userRepository) {}

// Find by email
$user = $this->userRepository->findByEmail('john@example.com');

// Check if email exists
$exists = $this->userRepository->emailExists('test@example.com');

// Get users with search
$users = $this->userRepository->search('john', $perPage = 15);
```

## Configuration

The package includes a comprehensive configuration file at `config/user.php`:

```php
return [
    'registration' => [
        'enabled' => true,
        'email_verification' => true,
        'welcome_email' => true,
    ],
    'authentication' => [
        'rate_limiting' => [
            'max_attempts' => 5,
            'decay_minutes' => 1,
        ],
        'session_timeout' => 120, // minutes
        'remember_me' => true,
    ],
    'password' => [
        'min_length' => 8,
        'require_uppercase' => true,
        'require_numbers' => true,
        'require_symbols' => false,
    ],
    'security' => [
        'activity_logging' => true,
        'failed_login_alerts' => true,
        'password_change_notifications' => true,
    ],
    // ... more options
];
```

## Events

The package dispatches several events:

- `UserRegistered`: When a new user registers
- `UserLoggedIn`: When user successfully logs in
- `UserPasswordChanged`: When password is updated

## Authentication Features

### Rate Limiting
- Prevents brute force attacks
- Configurable attempt limits
- IP and email-based limiting

### Security Logging
- All authentication attempts logged
- Failed login monitoring
- Security alerts

### Password Security
- Configurable complexity requirements
- Current password validation
- Change notifications

## Testing

Run package tests:

```bash
php artisan test packages/User/src/Tests
```

### Test Coverage
- **Unit Tests**: Service and repository logic
- **Feature Tests**: Authentication flows and API endpoints

## Middleware Usage

Apply middleware to routes:

```php
// Authentication rate limiting
Route::middleware(['auth.rate.limit:5,1'])->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
});

// User access control
Route::middleware(['user.access'])->group(function () {
    Route::apiResource('users', UserController::class);
});

// Password validation
Route::middleware(['validate.current.password'])->group(function () {
    Route::put('/password', [UserController::class, 'changePassword']);
});
```

## API Resources

Format API responses consistently:

```php
use Packages\User\Http\Resources\UserResource;
use Packages\User\Http\Resources\UserCollection;

// Single user
return UserResource::make($user);

// User collection
return new UserCollection($users);
```

## Views

The package includes authentication views:
- `auth/login.blade.php`: Login form
- `auth/register.blade.php`: Registration form
- `profile.blade.php`: User profile management

## Livewire Components

Interactive authentication components:
- `Login`: Login form component
- `Register`: Registration form component

## Background Jobs

### Welcome Email
```php
use Packages\User\Jobs\SendWelcomeEmailJob;

// Automatically dispatched on registration
// or manually dispatch
SendWelcomeEmailJob::dispatch($user);
```

### Password Change Notification
```php
use Packages\User\Jobs\SendPasswordChangeNotificationJob;

// Dispatched when password changes
SendPasswordChangeNotificationJob::dispatch($user);
```

## Error Handling

Custom exceptions for better error handling:

```php
try {
    $user = $userService->authenticate($credentials);
} catch (InvalidCredentialsException $e) {
    return response()->json(['error' => 'Invalid credentials'], 401);
} catch (UserNotFoundException $e) {
    return response()->json(['error' => 'User not found'], 404);
} catch (UserValidationException $e) {
    return response()->json(['error' => $e->getMessage()], 422);
}
```

## Security Best Practices

### Password Hashing
- Uses Laravel's secure password hashing
- Automatic rehashing on login if needed

### Session Management
- Secure session configuration
- Configurable timeout
- Remember me functionality

### CSRF Protection
- All forms include CSRF tokens
- API endpoints properly protected

### Rate Limiting
- Login attempt limiting
- Account lockout prevention
- IP-based restrictions

## Email Verification

Built-in email verification:
- Verification emails sent on registration
- Resend verification functionality
- Route protection for unverified users

## Database Considerations

The package maintains backward compatibility:
- Model aliases for existing code
- Migration compatibility
- Relationship preservation

## Dependencies

- Laravel Framework ^12.0
- Laravel Livewire ^3.6
- Standard Laravel authentication components

## License

This package is part of the BoxPos2 application and follows the same license terms.
