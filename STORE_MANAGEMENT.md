# Store Management Guide

## Overview

The system now uses **database-driven store management** instead of static config files. This provides much more flexibility for managing stores/projects dynamically.

## Database Structure

### Tables
- `stores` - Main store information
- `user_stores` - User-store relationships with roles and permissions
- `users.current_store_id` - User's currently selected store

### Store Fields
- `id` - Unique identifier
- `name` - Store display name
- `slug` - URL-friendly identifier
- `description` - Store description
- `email` - Store contact email
- `phone` - Store contact phone
- `address` - Store physical address
- `timezone` - Store timezone (default: UTC)
- `currency` - Store currency (default: USD)
- `language` - Store language (default: en)
- `status` - Store status (active|inactive|suspended)
- `settings` - JSON field for custom settings
- `domain` - Optional custom domain

## Command Line Management

Use the `stores:manage` command for all store operations:

### List All Stores
```bash
./vendor/bin/sail artisan stores:manage list
```

### Create New Store
```bash
# With options
./vendor/bin/sail artisan stores:manage create \
  --name="My New Store" \
  --description="Store description" \
  --email="store@example.com" \
  --phone="+1234567890" \
  --timezone="America/New_York" \
  --currency="USD" \
  --language="en"

# Interactive mode (will prompt for required fields)
./vendor/bin/sail artisan stores:manage create
```

### Update Store
```bash
./vendor/bin/sail artisan stores:manage update \
  --id=1 \
  --name="Updated Store Name" \
  --status="inactive"
```

### Delete Store
```bash
./vendor/bin/sail artisan stores:manage delete --id=1
```

### Assign User to Store
```bash
./vendor/bin/sail artisan stores:manage assign-user \
  --id=1 \
  --user-id=2 \
  --role="manager"
```

Available roles: `admin`, `manager`, `staff`, `viewer`

### Remove User from Store
```bash
./vendor/bin/sail artisan stores:manage remove-user \
  --id=1 \
  --user-id=2
```

## Programmatic Access

### Using ProjectService

The `ProjectService` now reads from database instead of config:

```php
$projectService = app(\App\Services\ProjectService::class);

// Get all projects
$projects = $projectService->getAllProjects();

// Get project by slug
$project = $projectService->getProject('my-store-slug');

// Get project by ID
$project = $projectService->getProjectById(1);

// Get user's accessible projects
$userProjects = $projectService->getUserProjects($userId);

// Get current project for authenticated user
$currentProject = $projectService->getCurrentProject();

// Switch to different project
$project = $projectService->switchProject(2);
```

### Using Store Model Directly

```php
use Packages\Store\Models\Store;

// Get all active stores
$stores = Store::active()->get();

// Create new store
$store = Store::create([
    'name' => 'New Store',
    'slug' => 'new-store',
    'description' => 'Store description',
    'status' => 'active'
]);

// Get store with users
$store = Store::with('users')->find(1);

// Get users for a store
$users = $store->users;

// Get active users for a store
$activeUsers = $store->activeUsers;

// Get store admins
$admins = $store->admins;
```

## Store Icons and Colors

The system automatically assigns icons and colors based on store names:

### Icons
- Dashboard/Admin stores: 📊
- Coffee/Cafe stores: ☕
- Inventory/Warehouse stores: 📦
- Retail/Shop stores: 🏪
- Default: 🏢

### Colors
- Dashboard/Admin stores: #066fd1 (Blue)
- Coffee/Cafe stores: #8b4513 (Brown)
- Inventory/Warehouse stores: #28a745 (Green)
- Default: #6c757d (Gray)

You can override these by setting custom values in the store's `settings` JSON field:

```php
$store->update([
    'settings' => [
        'icon' => '🎯',
        'color' => '#ff6b6b'
    ]
]);
```

## Migration from Config File

The old `config/projects.php` file has been removed. All store data is now managed through the database, providing:

✅ **Dynamic management** - Add/edit/remove stores without code changes
✅ **Better scalability** - No config file size limitations
✅ **User-specific access** - Granular permissions per store
✅ **Audit trail** - Track changes through database
✅ **API-friendly** - Easy to expose via REST/GraphQL APIs

## Best Practices

1. **Use descriptive slugs** - They're used in URLs and should be SEO-friendly
2. **Set proper timezones** - Important for reporting and scheduling
3. **Assign appropriate roles** - Follow principle of least privilege
4. **Regular backups** - Store data is now critical business data
5. **Monitor inactive stores** - Clean up unused stores periodically

## Troubleshooting

### Store not appearing in menu
- Check if store status is 'active'
- Verify user has access via `user_stores` table
- Clear cache: `./vendor/bin/sail artisan cache:clear`

### Permission issues
- Check user role in `user_stores` table
- Verify permissions JSON field
- Ensure `is_active` is true

### Performance issues
- ProjectService uses 5-minute caching
- Consider database indexing for large datasets
- Monitor query performance with Laravel Telescope
