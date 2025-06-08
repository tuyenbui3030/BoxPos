# Appearance Package

This package provides comprehensive appearance management functionality for the BoxPos2 application, including theme switching and UI preferences.

## Features

- **Theme Switching**: Light/Dark mode toggle
- **Session Persistence**: Theme preferences saved in session
- **LocalStorage Sync**: Synchronized with browser storage
- **Livewire Integration**: Reactive theme switching without page reload
- **Bootstrap Integration**: Works with Bootstrap's `data-bs-theme` system

## Architecture

The Appearance package follows Laravel best practices:

### Directory Structure

```
packages/Appearance/
├── src/
│   ├── Http/
│   │   └── Middleware/
│   │       └── AppearanceMiddleware.php   # Appearance middleware
│   ├── Livewire/
│   │   └── AppearanceSwitcher.php         # Appearance switcher component
│   ├── Traits/
│   │   └── HasAppearance.php              # Appearance functionality trait
│   └── AppearanceServiceProvider.php      # Service provider
├── config/
│   └── appearance.php                 # Package configuration
├── resources/
│   └── views/
│       └── livewire/
│           └── appearance-switcher.blade.php  # Appearance switcher view
└── composer.json
```

## Installation

The package is automatically loaded via Composer autoload and service provider registration.

### 1. Service Provider

Already registered in `bootstrap/providers.php`:
```php
Packages\Appearance\AppearanceServiceProvider::class,
```

### 2. Middleware Registration

The middleware is automatically registered with aliases `appearance` and `theme` (backward compatibility).

### 3. Livewire Components

Components are auto-registered:
- `appearance-switcher` (new)
- `theme-switcher` (backward compatibility)
- `packages.appearance.livewire.appearance-switcher`

## Usage

### Basic Theme Switching

Use the Livewire component in your layout:

```blade
<!-- Recommended new component -->
<livewire:appearance-switcher />

<!-- Or backward compatible -->
<livewire:theme-switcher />
```

### Using HasAppearance Trait

Add theme functionality to any Livewire component:

```php
<?php

namespace App\Livewire;

use Livewire\Component;
use Packages\Appearance\Traits\HasAppearance;

class YourComponent extends Component
{
    use HasAppearance;
    
    public function someMethod()
    {
        // Get current theme
        $theme = $this->getCurrentTheme();
        
        // Set theme
        $this->setTheme('dark');
        
        // Toggle theme
        $this->toggleTheme();
    }
}
```

### Theme Middleware

Apply appearance middleware to ensure session has default theme:

```php
Route::middleware(['appearance'])->group(function () {
    // Your routes
});

// Or using backward compatible alias
Route::middleware(['theme'])->group(function () {
    // Your routes  
});
```

## Configuration

The package includes a configuration file at `config/appearance.php`:

```php
return [
    'default' => env('APP_THEME', 'light'),
    'themes' => [
        'light' => [
            'name' => 'Light Theme',
            'description' => 'A clean and bright theme',
        ],
        'dark' => [
            'name' => 'Dark Theme',
            'description' => 'A dark theme that is easy on the eyes',
        ],
    ],
    'storage' => env('APPEARANCE_STORAGE', 'session'),
    'cookie' => [
        'name' => 'app_theme',
        'expire' => 60 * 24 * 365, // 1 year
        'path' => '/',
        'domain' => null,
        'secure' => false,
        'httpOnly' => true,
    ],
];
```

## Environment Variables

```env
# Default theme
APP_THEME=light

# Storage method
APPEARANCE_STORAGE=session
```

## CSS Integration

The package works with Bootstrap's theme system:

```css
/* Light theme styles */
[data-bs-theme="light"] .custom-element {
    background: white;
    color: black;
}

/* Dark theme styles */
[data-bs-theme="dark"] .custom-element {
    background: #1a1a1a;
    color: white;
}
```

## JavaScript Integration

The Livewire component includes minimal JavaScript for immediate DOM updates:

```javascript
// Theme is applied immediately to prevent flash
document.documentElement.setAttribute('data-bs-theme', theme);

// Listen for theme changes
Livewire.on('theme-changed', (event) => {
    document.documentElement.setAttribute('data-bs-theme', event.theme);
});
```

## Events

The package dispatches the following Livewire events:

- `theme-changed`: Fired when theme is changed
  - Parameters: `theme` (string) - The new theme name

## Extending

### Adding New Themes

1. Update the `themes` array in `config/appearance.php`
2. Add corresponding CSS rules for `[data-bs-theme="your-theme"]`
3. Update the theme switching logic if needed

### Custom Storage

You can extend the storage mechanism by:

1. Creating a custom storage class
2. Updating the `storage` configuration
3. Implementing the storage interface

## Dependencies

- Laravel Framework ^12.0
- Laravel Livewire ^3.0
- Bootstrap CSS framework (for theme system)

## Migration from Theme Package

This package was renamed from `Theme` to `Appearance` to better reflect its expanded scope for UI preferences. All functionality remains the same.

## License

This package is part of the BoxPos2 application and follows the same license terms.
