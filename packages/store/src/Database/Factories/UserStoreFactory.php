<?php

namespace Packages\Store\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Packages\Store\Models\UserStore;
use Packages\Store\Models\Store;
use Packages\User\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Packages\Store\Models\UserStore>
 */
class UserStoreFactory extends Factory
{
    protected $model = UserStore::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'store_id' => Store::factory(),
            'role' => $this->faker->randomElement(['admin', 'manager', 'staff', 'viewer']),
            'permissions' => $this->getVietnamesePermissions(),
            'is_active' => true,
            'is_default' => false,
            'assigned_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'assigned_by' => User::factory(),
        ];
    }

    /**
     * Create user store for specific user
     */
    public function forUser($user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => is_object($user) ? $user->id : $user,
        ]);
    }

    /**
     * Create user store for specific store
     */
    public function forStore($store): static
    {
        return $this->state(fn (array $attributes) => [
            'store_id' => is_object($store) ? $store->id : $store,
        ]);
    }

    /**
     * Create admin role
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'admin',
            'permissions' => $this->getAdminPermissions(),
        ]);
    }

    /**
     * Create manager role
     */
    public function manager(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'manager',
            'permissions' => $this->getManagerPermissions(),
        ]);
    }

    /**
     * Create staff role
     */
    public function staff(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'staff',
            'permissions' => $this->getStaffPermissions(),
        ]);
    }

    /**
     * Create viewer role
     */
    public function viewer(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'viewer',
            'permissions' => $this->getViewerPermissions(),
        ]);
    }

    /**
     * Create default store assignment
     */
    public function default(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_default' => true,
        ]);
    }

    /**
     * Create active assignment
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * Get Vietnamese permissions
     */
    private function getVietnamesePermissions(): array
    {
        $permissions = [
            'Xem báo cáo bán hàng',
            'Quản lý kho hàng',
            'Tạo đơn hàng',
            'Xử lý thanh toán',
            'Quản lý khách hàng',
            'Xem thống kê',
            'Quản lý nhân viên',
            'Cấu hình hệ thống',
            'Xuất báo cáo',
            'Quản lý khuyến mãi'
        ];
        
        return $this->faker->randomElements($permissions, $this->faker->numberBetween(3, 8));
    }

    /**
     * Get admin permissions
     */
    private function getAdminPermissions(): array
    {
        return [
            'Toàn quyền quản trị',
            'Quản lý người dùng',
            'Cấu hình hệ thống',
            'Xem tất cả báo cáo',
            'Quản lý cửa hàng',
            'Quản lý nhân viên',
            'Quản lý kho hàng',
            'Quản lý bán hàng',
            'Quản lý tài chính',
            'Quản lý khuyến mãi',
            'Xuất nhập dữ liệu',
            'Sao lưu phục hồi'
        ];
    }

    /**
     * Get manager permissions
     */
    private function getManagerPermissions(): array
    {
        return [
            'Quản lý bán hàng',
            'Quản lý kho hàng',
            'Quản lý khách hàng',
            'Xem báo cáo',
            'Quản lý nhân viên',
            'Xử lý đơn hàng',
            'Quản lý khuyến mãi',
            'Xuất báo cáo',
            'Quản lý tồn kho',
            'Phê duyệt giao dịch'
        ];
    }

    /**
     * Get staff permissions
     */
    private function getStaffPermissions(): array
    {
        return [
            'Tạo đơn hàng',
            'Xử lý thanh toán',
            'Quản lý khách hàng',
            'Xem kho hàng',
            'Cập nhật tồn kho',
            'In hóa đơn',
            'Xem báo cáo cơ bản',
            'Chăm sóc khách hàng'
        ];
    }

    /**
     * Get viewer permissions
     */
    private function getViewerPermissions(): array
    {
        return [
            'Xem đơn hàng',
            'Xem khách hàng',
            'Xem kho hàng',
            'Xem báo cáo cơ bản',
            'Xem thống kê'
        ];
    }
}