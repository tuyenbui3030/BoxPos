<?php

namespace Packages\CashManagement\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Packages\CashManagement\Models\CashTransaction;
use Packages\CashManagement\Models\CashAccount;
use Packages\CashManagement\Models\CashCategory;
use Packages\Store\Models\Store;
use Packages\User\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Packages\CashManagement\Models\CashTransaction>
 */
class CashTransactionFactory extends Factory
{
    protected $model = CashTransaction::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $amount = $this->faker->randomFloat(2, 50000, 10000000);
        $type = $this->faker->randomElement(['income', 'expense', 'transfer']);
        
        return [
            'store_id' => Store::factory(),
            'account_id' => \Packages\CashManagement\Database\Factories\CashAccountFactory::new(),
            'category_id' => \Packages\CashManagement\Database\Factories\CashCategoryFactory::new(),
            'transaction_number' => $this->faker->unique()->regexify('TXN[0-9]{8}'),
            'transaction_date' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'type' => $type,
            'amount' => $amount,
            'currency' => 'VND',
            'exchange_rate' => 1.0,
            'base_amount' => $amount,
            'related_document_type' => $this->faker->optional()->randomElement(['invoice', 'purchase_order', 'salary', 'expense']),
            'related_document_id' => $this->faker->optional()->numberBetween(1, 1000),
            'related_document_number' => $this->faker->optional()->regexify('DOC[0-9]{6}'),
            'description' => $this->getVietnameseTransactionDescription(),
            'payment_method' => $this->faker->randomElement(['cash', 'bank_transfer', 'credit_card', 'e_wallet']),
            'payment_reference' => $this->faker->optional()->regexify('PAY[0-9]{6}'),
            'payer_payee' => $this->faker->optional()->name(),
            'payer_payee_details' => $this->faker->optional()->sentence(),
            'status' => $this->faker->randomElement(['pending', 'completed', 'cancelled', 'failed']),
            'is_reconciled' => $this->faker->boolean(80),
            'reconciled_date' => $this->faker->optional()->dateTimeBetween('-1 month', 'now'),
            'reconciled_by' => $this->faker->optional()->numberBetween(1, 10),
            'requires_approval' => $this->faker->boolean(30),
            'is_approved' => $this->faker->boolean(90),
            'approved_by' => $this->faker->optional()->numberBetween(1, 10),
            'approved_at' => $this->faker->optional()->dateTimeBetween('-1 month', 'now'),
            'approval_notes' => $this->faker->optional()->sentence(),
            'notes' => $this->faker->optional()->sentence(),
            'attachments' => [],
            'metadata' => [],
            'created_by' => User::factory(),
        ];
    }

    /**
     * Create transaction for specific store
     */
    public function forStore($store): static
    {
        return $this->state(fn (array $attributes) => [
            'store_id' => is_object($store) ? $store->id : $store,
        ]);
    }

    /**
     * Create transaction for specific account
     */
    public function forAccount($account): static
    {
        return $this->state(fn (array $attributes) => [
            'account_id' => is_object($account) ? $account->id : $account,
        ]);
    }

    /**
     * Create transaction for specific category
     */
    public function forCategory($category): static
    {
        return $this->state(fn (array $attributes) => [
            'category_id' => is_object($category) ? $category->id : $category,
        ]);
    }

    /**
     * Create income transaction
     */
    public function income(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'income',
            'description' => $this->getVietnameseIncomeDescription(),
        ]);
    }

    /**
     * Create expense transaction
     */
    public function expense(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'expense',
            'description' => $this->getVietnameseExpenseDescription(),
        ]);
    }

    /**
     * Create transfer transaction
     */
    public function transfer(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'transfer',
            'description' => $this->getVietnameseTransferDescription(),
            'transfer_to_account_id' => \Packages\CashManagement\Database\Factories\CashAccountFactory::new(),
        ]);
    }

    /**
     * Create reconciled transaction
     */
    public function reconciled(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_reconciled' => true,
            'reconciled_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
        ]);
    }

    /**
     * Create cash payment transaction
     */
    public function cash(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_method' => 'cash',
        ]);
    }

    /**
     * Create bank transfer transaction
     */
    public function bankTransfer(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_method' => 'bank_transfer',
        ]);
    }

    /**
     * Get Vietnamese transaction descriptions
     */
    private function getVietnameseTransactionDescription(): string
    {
        $descriptions = [
            'Thu tiền bán hàng',
            'Chi phí nhập hàng',
            'Chi phí vận chuyển',
            'Chi lương nhân viên',
            'Chi phí điện nước',
            'Chi phí thuê mặt bằng',
            'Thu từ khách hàng',
            'Chi cho nhà cung cấp',
            'Chuyển khoản ngân hàng',
            'Rút tiền mặt',
            'Nộp tiền vào ngân hàng',
            'Chi phí marketing',
            'Chi phí văn phòng phẩm',
            'Thu khác',
            'Chi khác'
        ];
        
        return $this->faker->randomElement($descriptions);
    }

    /**
     * Get Vietnamese income descriptions
     */
    private function getVietnameseIncomeDescription(): string
    {
        $descriptions = [
            'Thu tiền bán hàng',
            'Thu từ dịch vụ',
            'Thu lãi ngân hàng',
            'Thu từ khách hàng',
            'Thu từ thanh lý tài sản',
            'Thu từ cho thuê',
            'Thu từ hoa hồng',
            'Thu từ bảo hiểm',
            'Thu từ hoàn thuế',
            'Thu khác'
        ];
        
        return $this->faker->randomElement($descriptions);
    }

    /**
     * Get Vietnamese expense descriptions
     */
    private function getVietnameseExpenseDescription(): string
    {
        $descriptions = [
            'Chi phí nhập hàng',
            'Chi phí vận chuyển',
            'Chi lương nhân viên',
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
        
        return $this->faker->randomElement($descriptions);
    }

    /**
     * Get Vietnamese transfer descriptions
     */
    private function getVietnameseTransferDescription(): string
    {
        $descriptions = [
            'Chuyển khoản ngân hàng',
            'Rút tiền mặt',
            'Nộp tiền vào ngân hàng',
            'Chuyển tiền giữa các tài khoản',
            'Chuyển tiền cho nhà cung cấp',
            'Chuyển tiền lương nhân viên',
            'Chuyển tiền thuế'
        ];
        
        return $this->faker->randomElement($descriptions);
    }
}