<?php

namespace Packages\CashManagement\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Packages\CashManagement\Models\CashCategory;
use Packages\Store\Models\Store;
use Packages\User\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Packages\CashManagement\Models\CashCategory>
 */
class CashCategoryFactory extends Factory
{
    protected $model = CashCategory::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'store_id' => Store::factory(),
            'code' => $this->faker->unique()->regexify('CASH[0-9]{3}'),
            'name' => $this->getVietnameseCategoryName(),
            'description' => $this->faker->optional()->sentence(),
            'type' => $this->faker->randomElement(['income', 'expense', 'transfer']),
            'parent_id' => null,
            'color' => $this->faker->hexColor(),
            'is_active' => true,
            'is_system' => false,
            'sort_order' => $this->faker->numberBetween(1, 100),
            'metadata' => [],
            'created_by' => User::factory(),
        ];
    }

    /**
     * Create category for specific store
     */
    public function forStore($store): static
    {
        return $this->state(fn (array $attributes) => [
            'store_id' => is_object($store) ? $store->id : $store,
        ]);
    }

    /**
     * Create income category
     */
    public function income(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'income',
            'category_name' => $this->getVietnameseIncomeCategory(),
        ]);
    }

    /**
     * Create expense category
     */
    public function expense(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'expense',
            'category_name' => $this->getVietnameseExpenseCategory(),
        ]);
    }

    /**
     * Create transfer category
     */
    public function transfer(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'transfer',
            'category_name' => $this->getVietnameseTransferCategory(),
        ]);
    }

    /**
     * Create active category
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * Get Vietnamese category names
     */
    private function getVietnameseCategoryName(): string
    {
        $categories = [
            'Thu từ bán hàng',
            'Chi phí nhập hàng',
            'Chi phí vận chuyển',
            'Chi phí nhân viên',
            'Chi phí điện nước',
            'Chi phí thuê mặt bằng',
            'Chi phí marketing',
            'Thu khác',
            'Chi khác',
            'Chuyển khoản ngân hàng',
            'Rút tiền mặt',
            'Nộp tiền vào ngân hàng'
        ];
        
        return $this->faker->randomElement($categories);
    }

    /**
     * Get Vietnamese income categories
     */
    private function getVietnameseIncomeCategory(): string
    {
        $categories = [
            'Thu từ bán hàng',
            'Thu từ dịch vụ',
            'Thu lãi ngân hàng',
            'Thu từ đầu tư',
            'Thu khác',
            'Thu từ thanh lý tài sản',
            'Thu từ cho thuê',
            'Thu từ hoa hồng',
            'Thu từ bảo hiểm',
            'Thu từ hoàn thuế'
        ];
        
        return $this->faker->randomElement($categories);
    }

    /**
     * Get Vietnamese expense categories
     */
    private function getVietnameseExpenseCategory(): string
    {
        $categories = [
            'Chi phí nhập hàng',
            'Chi phí vận chuyển',
            'Chi phí nhân viên',
            'Chi phí điện nước',
            'Chi phí thuê mặt bằng',
            'Chi phí marketing',
            'Chi phí bảo trì',
            'Chi phí văn phòng phẩm',
            'Chi phí điện thoại internet',
            'Chi phí bảo hiểm',
            'Chi phí thuế',
            'Chi phí đào tạo',
            'Chi phí y tế',
            'Chi phí ăn uống',
            'Chi khác'
        ];
        
        return $this->faker->randomElement($categories);
    }

    /**
     * Get Vietnamese transfer categories
     */
    private function getVietnameseTransferCategory(): string
    {
        $categories = [
            'Chuyển khoản ngân hàng',
            'Rút tiền mặt',
            'Nộp tiền vào ngân hàng',
            'Chuyển tiền giữa các tài khoản',
            'Chuyển tiền cho nhà cung cấp',
            'Nhận chuyển khoản từ khách hàng',
            'Chuyển tiền lương nhân viên',
            'Chuyển tiền thuế'
        ];
        
        return $this->faker->randomElement($categories);
    }
}