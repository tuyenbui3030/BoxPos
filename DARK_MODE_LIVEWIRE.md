# Dark Mode với Livewire - BoxPos

## Tổng quan

Chúng ta đã chuyển đổi thành công từ theme switching bằng JavaScript vanilla sang sử dụng Livewire component. Việc này mang lại nhiều lợi ích:

1. **Đồng bộ hóa với session**: Theme được lưu trong PHP session, đồng bộ giữa server và client
2. **Tích hợp tốt với Livewire**: Có thể điều khiển theme từ bất kỳ Livewire component nào
3. **Persistence tốt hơn**: Kết hợp localStorage và session để duy trì theme preference
4. **Extensible**: Dễ dàng mở rộng với các tính năng khác

## Các file đã tạo/sửa đổi

### 1. Livewire ThemeSwitcher Component
- **File**: `app/Livewire/ThemeSwitcher.php`
- **View**: `resources/views/livewire/theme-switcher.blade.php`
- **Chức năng**: Quản lý theme switching với Livewire

### 2. HasTheme Trait
- **File**: `app/Traits/HasTheme.php`
- **Chức năng**: Trait để thêm theme functionality vào bất kỳ Livewire component nào

### 3. Theme Middleware
- **File**: `app/Http/Middleware/ThemeMiddleware.php`
- **Chức năng**: Đảm bảo session có theme default

### 4. Layout Updates
- `resources/views/layouts/app.blade.php`
- `resources/views/layouts/app-vertical.blade.php`
- `resources/views/components/layouts/guest.blade.php`

## Cách sử dụng

### 1. Sử dụng ThemeSwitcher Component

Trong layout:
```php
<livewire:theme-switcher />
```

### 2. Thêm theme functionality vào Livewire component khác

```php
<?php

namespace App\Livewire;

use Livewire\Component;
use App\Traits\HasTheme;

class YourComponent extends Component
{
    use HasTheme;
    
    public function someMethod()
    {
        // Lấy theme hiện tại
        $currentTheme = $this->getCurrentTheme();
        
        // Đặt theme
        $this->setTheme('dark');
        
        // Toggle theme
        $this->toggleTheme();
    }
}
```

### 3. Truy cập theme trong view

```blade
<!-- Theme hiện tại có sẵn trong $currentTheme -->
<div class="theme-indicator">
    Current theme: {{ $currentTheme }}
</div>
```

## Tính năng

### 1. Auto-sync với localStorage
- Theme được sync giữa server session và browser localStorage
- Ngăn chặn flash of incorrect theme

### 2. System theme detection
- Tự động detect system dark/light mode preference
- Chỉ áp dụng khi user chưa có preference

### 3. Session persistence
- Theme được lưu trong PHP session
- Duy trì qua các request

### 4. Event system
- Dispatch `theme-changed` event khi theme thay đổi
- Các component khác có thể listen và react

## Cấu trúc CSS

Theme switching sử dụng Bootstrap 5 theme system với `data-bs-theme` attribute:

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

## Testing

Để test theme switching:

1. Mở ứng dụng
2. Click theme toggle button ở header
3. Refresh trang - theme should persist
4. Check localStorage - should contain 'theme-preference'
5. Check session - should contain theme value

## Migration Notes

- JavaScript theme switcher cũ đã được comment out
- Tất cả layout đã được cập nhật để sử dụng Livewire component
- Theme middleware được thêm vào web group
- View composer share theme với tất cả views

## Troubleshooting

### Theme không persist sau refresh
- Kiểm tra session config
- Kiểm tra localStorage có được set không

### Theme toggle button không hoạt động
- Kiểm tra Livewire scripts có được load không
- Check browser console for errors

### CSS không apply đúng
- Kiểm tra `data-bs-theme` attribute trong HTML
- Verify CSS selectors cho dark theme
