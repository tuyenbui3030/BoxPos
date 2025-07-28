<?php

namespace Packages\CashManagement\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Packages\CashManagement\Models\CashAccount;
use Packages\Store\Models\Store;
use Packages\User\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Packages\CashManagement\Models\CashAccount>
 */
class CashAccountFactory extends Factory
{
    protected $model = CashAccount::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $initialBalance = $this->faker->randomFloat(2, 1000000, 50000000);
        
        return [
            'store_id' => Store::factory(),
            'account_code' => $this->faker->unique()->regexify('ACC[0-9]{6}'),
            'account_name' => $this->getVietnameseAccountName(),
            'account_type' => $this->faker->randomElement(['cash', 'bank', 'credit_card', 'e_wallet']),
            'bank_name' => $this->faker->optional()->randomElement($this->getVietnameseBankNames()),
            'account_number' => $this->faker->optional()->numerify('##########'),
            'account_holder' => $this->faker->optional()->name(),
            'branch' => $this->faker->optional()->randomElement($this->getVietnameseBankBranches()),
            'currency' => 'VND',
            'opening_balance' => $initialBalance,
            'current_balance' => $initialBalance,
            'opening_date' => $this->faker->dateTimeBetween('-2 years', 'now'),
            'swift_code' => $this->faker->optional()->regexify('[A-Z]{8}[A-Z0-9]{3}'),
            'credit_limit' => $this->faker->optional()->randomFloat(2, 10000000, 100000000),
            'daily_limit' => $this->faker->optional()->randomFloat(2, 1000000, 50000000),
            'monthly_limit' => $this->faker->optional()->randomFloat(2, 50000000, 500000000),
            'require_approval' => $this->faker->boolean(30),
            'approval_threshold' => $this->faker->optional()->randomFloat(2, 1000000, 10000000),
            'description' => $this->faker->optional()->sentence(),
            'is_active' => true,
            'is_default' => false,
            'allow_negative' => $this->faker->boolean(20),
            'is_reconciled' => $this->faker->boolean(80),
            'created_by' => User::factory(),
        ];
    }

    /**
     * Create account for specific store
     */
    public function forStore($store): static
    {
        return $this->state(fn (array $attributes) => [
            'store_id' => is_object($store) ? $store->id : $store,
        ]);
    }

    /**
     * Create cash account
     */
    public function cash(): static
    {
        return $this->state(fn (array $attributes) => [
            'account_type' => 'cash',
            'account_name' => 'Tiền mặt tại quầy',
            'bank_name' => null,
            'account_number' => null,
            'account_holder' => null,
            'branch' => null,
        ]);
    }

    /**
     * Create bank account
     */
    public function bank(): static
    {
        return $this->state(fn (array $attributes) => [
            'account_type' => 'bank',
            'bank_name' => $this->faker->randomElement($this->getVietnameseBankNames()),
            'account_number' => $this->faker->numerify('##########'),
            'account_holder' => $this->faker->name(),
            'branch' => $this->faker->randomElement($this->getVietnameseBankBranches()),
        ]);
    }

    /**
     * Create e-wallet account
     */
    public function eWallet(): static
    {
        return $this->state(fn (array $attributes) => [
            'account_type' => 'e_wallet',
            'account_name' => $this->faker->randomElement(['Ví MoMo', 'Ví ZaloPay', 'Ví ViettelPay', 'Ví ShopeePay']),
            'account_number' => $this->faker->numerify('##########'),
            'bank_name' => null,
            'branch' => null,
        ]);
    }

    /**
     * Create default account
     */
    public function default(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_default' => true,
        ]);
    }

    /**
     * Create active account
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * Get Vietnamese account names
     */
    private function getVietnameseAccountName(): string
    {
        $names = [
            'Tiền mặt tại quầy',
            'Tài khoản ngân hàng chính',
            'Tài khoản tiết kiệm',
            'Tài khoản thanh toán',
            'Ví điện tử',
            'Quỹ tiền mặt',
            'Tài khoản USD',
            'Tài khoản dự phòng',
            'Quỹ lương nhân viên',
            'Tài khoản thu chi'
        ];
        
        return $this->faker->randomElement($names);
    }

    /**
     * Get Vietnamese bank names
     */
    private function getVietnameseBankNames(): array
    {
        return [
            'Vietcombank',
            'BIDV',
            'VietinBank',
            'Agribank',
            'Techcombank',
            'MB Bank',
            'ACB',
            'VPBank',
            'TPBank',
            'Sacombank',
            'HDBank',
            'OCB',
            'VIB',
            'SHB',
            'Eximbank'
        ];
    }

    /**
     * Get Vietnamese bank branches
     */
    private function getVietnameseBankBranches(): array
    {
        return [
            'Chi nhánh TP.HCM',
            'Chi nhánh Hà Nội',
            'Chi nhánh Đà Nẵng',
            'Chi nhánh Cần Thơ',
            'Chi nhánh Hải Phòng',
            'Chi nhánh Quận 1',
            'Chi nhánh Quận 3',
            'Chi nhánh Quận 5',
            'Chi nhánh Quận 7',
            'Chi nhánh Bình Thạnh',
            'Chi nhánh Tân Bình',
            'Chi nhánh Gò Vấp',
            'Chi nhánh Thủ Đức',
            'Chi nhánh Đồng Nai',
            'Chi nhánh Bình Dương'
        ];
    }
}