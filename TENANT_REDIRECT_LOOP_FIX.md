# Khắc phục lỗi Redirect Loop khi Login

## Vấn đề

Khi login vào hệ thống, người dùng gặp lỗi redirect loop với thông báo:
```
Trang này hiện không hoạt động
localhost đã chuyển hướng bạn quá nhiều lần.
ERR_TOO_MANY_REDIRECTS
```

## Nguyên nhân

Vấn đề xảy ra do:

1. **TenantContext Middleware**: Middleware này kiểm tra xem user đã login có store nào không
2. **Thiếu User-Store Relationships**: Khi user không có store nào được gán, middleware sẽ redirect đến `/store-selection`
3. **Redirect Loop**: Trang `/store-selection` cũng chạy qua middleware và lại redirect về chính nó, tạo thành vòng lặp vô tận

## Luồng hoạt động gây lỗi

```
User Login → TenantContext Middleware → getCurrentStore() = null 
→ Redirect to /store-selection → TenantContext Middleware → getCurrentStore() = null 
→ Redirect to /store-selection → ... (vòng lặp vô tận)
```

## Giải pháp

### 1. Kiểm tra dữ liệu hiện tại

```bash
# Kiểm tra users và stores
docker exec boxpos-laravel.test-1 php artisan tinker --execute="
use Packages\Store\Models\Store;
use Packages\User\Models\User;
echo 'Total users: ' . User::count() . PHP_EOL;
echo 'Total stores: ' . Store::count() . PHP_EOL;
echo 'User-store relationships: ' . DB::table('user_stores')->count() . PHP_EOL;
"
```

### 2. Sử dụng Command để khắc phục

```bash
# Xem preview những gì sẽ được thay đổi
docker exec boxpos-laravel.test-1 php artisan tenant:fix-user-stores --dry-run

# Thực hiện khắc phục
docker exec boxpos-laravel.test-1 php artisan tenant:fix-user-stores

# Khắc phục cho user cụ thể
docker exec boxpos-laravel.test-1 php artisan tenant:fix-user-stores --user=1
```

### 3. Sử dụng Seeder

```bash
# Chạy seeder để tạo relationships
docker exec boxpos-laravel.test-1 php artisan db:seed --class=UserStoreSeeder
```

### 4. Khắc phục thủ công qua Tinker

```bash
docker exec boxpos-laravel.test-1 php artisan tinker --execute="
use Packages\Store\Models\Store;
use Packages\User\Models\User;

// Lấy user và store đầu tiên
\$user = User::first();
\$store = Store::first();

if (\$user && \$store) {
    // Tạo relationship
    \$user->stores()->attach(\$store->id, [
        'role' => 'admin',
        'permissions' => json_encode(['manage_settings', 'manage_customers', 'manage_products', 'view_dashboard']),
        'is_active' => true,
        'joined_at' => now(),
    ]);
    
    // Set current store
    \$user->update(['current_store_id' => \$store->id]);
    
    echo 'Fixed user: ' . \$user->name . PHP_EOL;
}
"
```

## Cấu trúc Middleware

### TenantContext Middleware

File: `packages/tenant/src/Middleware/TenantContext.php`

Middleware này:
- Chỉ áp dụng cho authenticated users
- Kiểm tra user có store hiện tại không
- Redirect đến `/store-selection` nếu không có store
- Loại trừ một số routes không cần store (store.selection, store.switch, logout, profile.*, api.*)

### Routes được loại trừ

```php
$excludedRoutes = [
    'store.selection',
    'store.switch', 
    'logout',
    'profile.*',
    'api.*',
];
```

## Phòng ngừa trong tương lai

### 1. Tự động gán store khi tạo user mới

Thêm vào User Observer hoặc Event Listener:

```php
// Trong UserObserver
public function created(User $user)
{
    $defaultStore = Store::where('status', Store::STATUS_ACTIVE)->first();
    
    if ($defaultStore) {
        $user->stores()->attach($defaultStore->id, [
            'role' => 'staff',
            'permissions' => json_encode(['view_dashboard']),
            'is_active' => true,
            'joined_at' => now(),
        ]);
        
        $user->update(['current_store_id' => $defaultStore->id]);
    }
}
```

### 2. Validation khi xóa store

Đảm bảo không xóa store cuối cùng của user.

### 3. Health Check Command

Tạo command để kiểm tra định kỳ:

```bash
php artisan tenant:health-check
```

## Kiểm tra sau khi khắc phục

1. **Login thành công**: User có thể login không bị redirect loop
2. **Store selection hoạt động**: Trang `/store-selection` hiển thị đúng
3. **Dashboard accessible**: User có thể truy cập dashboard sau khi login

## Vấn đề Store Selection Menu

Nếu menu store selection không hiển thị tất cả stores, có thể do:

### Nguyên nhân
- User chỉ có quyền truy cập 1 store thay vì tất cả stores
- StoreSelection component chỉ hiển thị stores mà user có quyền truy cập

### Giải pháp
```bash
# Gán user vào tất cả stores có sẵn
docker exec boxpos-laravel.test-1 php artisan tenant:fix-user-stores --all-stores

# Kiểm tra quyền truy cập của user
docker exec boxpos-laravel.test-1 php artisan tenant:check-user-access --user=1

# Test StoreSelection component
docker exec boxpos-laravel.test-1 php artisan store:test-selection --user=1
```

### Debug Routes
- `/debug-store-selection` - API test stores data
- `/debug-store-component` - Test StoreSelection component trực tiếp
- `/debug-store-switch/{storeId}` - Test store switching API

## Vấn đề Store Switching Fail

Nếu chức năng switch store không hoạt động:

### Nguyên nhân thường gặp
1. **Route không tồn tại**: Route `dashboard` không có, chỉ có `locale.dashboard`
2. **Method thiếu**: View gọi methods không tồn tại trong component
3. **Permission issues**: User không có quyền switch store
4. **JavaScript errors**: Livewire component không load đúng

### Giải pháp
```bash
# Chẩn đoán toàn diện
docker exec boxpos-laravel.test-1 php artisan store:diagnose-switching --user=1

# Test store switching end-to-end
docker exec boxpos-laravel.test-1 php artisan store:test-switching --user=1

# Test StoreSelection component
docker exec boxpos-laravel.test-1 php artisan store:test-selection --user=1
```

### Các lỗi đã khắc phục
1. **Route redirect**: Sửa từ `route('dashboard')` thành `route('locale.dashboard', ['locale' => app()->getLocale()])`
2. **Missing methods**: Thêm `canManageStore()`, `manageStore()`, `canCreateStores()`, `createNewStore()`
3. **Method name mismatch**: Sửa `getUserRole()` thành `getUserRoleInStore()` trong view

## Files liên quan

- `packages/tenant/src/Middleware/TenantContext.php` - Middleware chính
- `packages/tenant/src/Services/TenantService.php` - Service xử lý tenant logic
- `packages/tenant/src/Console/Commands/FixUserStoreRelationships.php` - Command khắc phục
- `packages/tenant/src/Console/Commands/CheckUserStoreAccess.php` - Command kiểm tra access (chỉ debug mode)
- `packages/store/src/Console/Commands/TestStoreSelection.php` - Command test component (chỉ debug mode)
- `packages/store/src/Console/Commands/TestStoreSwitching.php` - Command test store switching (chỉ debug mode)
- `packages/store/src/Console/Commands/DiagnoseStoreSwitching.php` - Command chẩn đoán issues (chỉ debug mode)
- `packages/store/src/Livewire/StoreSelection.php` - StoreSelection component
- `packages/store/resources/views/livewire/store-selection.blade.php` - StoreSelection view
- `database/seeders/UserStoreSeeder.php` - Seeder tạo relationships
- `packages/store/routes/web.php` - Route definitions
- `routes/debug.php` - Debug routes
