<?php

namespace Packages\Payments\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Packages\Payments\Models\PaymentMethod;
use Packages\Store\Models\Store;
use Packages\User\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Packages\Payments\Models\PaymentMethod>
 */
class PaymentMethodFactory extends Factory
{
    protected $model = PaymentMethod::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $types = ['cash', 'bank_transfer', 'credit_card', 'debit_card', 'e_wallet', 'payment_gateway'];
        $type = $this->faker->randomElement($types);
        
        return [
            'store_id' => Store::factory(),
            'code' => strtoupper($this->faker->unique()->lexify('???')),
            'name' => $this->getNameForType($type),
            'description' => $this->getDescriptionForType($type),
            'type' => $type,
            'category' => $this->getCategoryForType($type),
            'requires_verification' => $this->getRequiresVerificationForType($type),
            'provider' => $this->getProviderForType($type),
            'provider_code' => strtoupper($this->faker->lexify('???')),
            'processing_fee_percent' => $this->getProcessingFeePercentForType($type),
            'processing_fee_fixed' => $this->getProcessingFeeFixedForType($type),
            'min_amount' => $this->getMinAmountForType($type),
            'max_amount' => $this->getMaxAmountForType($type),
            'currency' => 'VND',
            'settlement_days' => $this->getSettlementDaysForType($type),
            'processing_days' => $this->getProcessingDaysForType($type),
            'icon' => $this->getIconForType($type),
            'color' => $this->faker->hexColor(),
            'show_on_pos' => $this->faker->boolean(80),
            'show_on_website' => $this->faker->boolean(90),
            'show_on_mobile' => $this->faker->boolean(85),
            'sort_order' => $this->faker->numberBetween(1, 10),
            'is_active' => $this->faker->boolean(90),
            'is_default' => false,
            'requires_pin' => $this->getRequiresPinForType($type),
            'requires_signature' => $this->getRequiresSignatureForType($type),
            'supports_refund' => $this->faker->boolean(80),
            'supports_partial_refund' => $this->faker->boolean(70),
            'refund_days_limit' => $this->faker->numberBetween(7, 30),
            'created_by' => User::factory(),
        ];
    }

    /**
     * Configure the model factory for cash payment method.
     */
    public function cash(): static
    {
        return $this->state(fn (array $attributes) => [
            'code' => 'CASH',
            'name' => 'Tiền mặt',
            'description' => 'Thanh toán bằng tiền mặt',
            'type' => 'cash',
            'category' => 'offline',
            'requires_verification' => false,
            'provider' => 'internal',
            'processing_fee_percent' => 0.00,
            'processing_fee_fixed' => 0.00,
            'min_amount' => 1000.00,
            'max_amount' => 50000000.00,
            'settlement_days' => 0,
            'icon' => 'cash',
            'color' => '#28a745',
            'is_default' => true,
            'requires_pin' => false,
            'requires_signature' => false,
        ]);
    }

    /**
     * Configure the model factory for bank transfer payment method.
     */
    public function bankTransfer(): static
    {
        return $this->state(fn (array $attributes) => [
            'code' => 'BANK_TRANSFER',
            'name' => 'Chuyển khoản ngân hàng',
            'description' => 'Thanh toán qua chuyển khoản ngân hàng',
            'type' => 'bank_transfer',
            'category' => 'offline',
            'requires_verification' => true,
            'provider' => 'bank',
            'processing_fee_percent' => 0.00,
            'processing_fee_fixed' => 0.00,
            'min_amount' => 10000.00,
            'max_amount' => null,
            'settlement_days' => 1,
            'icon' => 'bank',
            'color' => '#007bff',
            'requires_pin' => false,
            'requires_signature' => true,
        ]);
    }

    /**
     * Configure the model factory for credit card payment method.
     */
    public function creditCard(): static
    {
        return $this->state(fn (array $attributes) => [
            'code' => 'CREDIT_CARD',
            'name' => 'Thẻ tín dụng',
            'description' => 'Thanh toán bằng thẻ tín dụng',
            'type' => 'credit_card',
            'category' => 'card',
            'requires_verification' => true,
            'provider' => 'visa_mastercard',
            'processing_fee_percent' => 2.50,
            'processing_fee_fixed' => 5000.00,
            'min_amount' => 50000.00,
            'max_amount' => 100000000.00,
            'settlement_days' => 2,
            'icon' => 'credit-card',
            'color' => '#6f42c1',
            'requires_pin' => true,
            'requires_signature' => true,
        ]);
    }

    /**
     * Configure the model factory for e-wallet payment method.
     */
    public function eWallet(): static
    {
        return $this->state(fn (array $attributes) => [
            'code' => 'MOMO',
            'name' => 'Ví MoMo',
            'description' => 'Thanh toán qua ví điện tử MoMo',
            'type' => 'e_wallet',
            'category' => 'digital',
            'requires_verification' => true,
            'provider' => 'momo',
            'processing_fee_percent' => 1.00,
            'processing_fee_fixed' => 0.00,
            'min_amount' => 10000.00,
            'max_amount' => 20000000.00,
            'settlement_days' => 1,
            'icon' => 'mobile-alt',
            'color' => '#d63384',
            'requires_pin' => false,
            'requires_signature' => false,
        ]);
    }

    /**
     * Configure the model factory for active payment methods.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * Configure the model factory for default payment method.
     */
    public function default(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_default' => true,
            'is_active' => true,
        ]);
    }

    /**
     * Get name for payment method type
     */
    private function getNameForType(string $type): string
    {
        return match($type) {
            'cash' => 'Tiền mặt',
            'bank_transfer' => 'Chuyển khoản ngân hàng',
            'credit_card' => 'Thẻ tín dụng',
            'debit_card' => 'Thẻ ghi nợ',
            'e_wallet' => 'Ví điện tử',
            'payment_gateway' => 'Cổng thanh toán',
            default => $this->faker->words(2, true),
        };
    }

    /**
     * Get description for payment method type
     */
    private function getDescriptionForType(string $type): string
    {
        return match($type) {
            'cash' => 'Thanh toán bằng tiền mặt tại quầy',
            'bank_transfer' => 'Thanh toán qua chuyển khoản ngân hàng',
            'credit_card' => 'Thanh toán bằng thẻ tín dụng Visa/MasterCard',
            'debit_card' => 'Thanh toán bằng thẻ ghi nợ ATM',
            'e_wallet' => 'Thanh toán qua ví điện tử',
            'payment_gateway' => 'Thanh toán qua cổng thanh toán trực tuyến',
            default => $this->faker->sentence(),
        };
    }

    /**
     * Get category for payment method type
     */
    private function getCategoryForType(string $type): string
    {
        return match($type) {
            'cash' => 'offline',
            'bank_transfer' => 'offline',
            'credit_card', 'debit_card' => 'card',
            'e_wallet', 'payment_gateway' => 'digital',
            default => 'offline',
        };
    }

    /**
     * Get requires verification for payment method type
     */
    private function getRequiresVerificationForType(string $type): bool
    {
        return match($type) {
            'cash' => false,
            default => true,
        };
    }

    /**
     * Get provider for payment method type
     */
    private function getProviderForType(string $type): string
    {
        return match($type) {
            'cash' => 'internal',
            'bank_transfer' => 'bank',
            'credit_card' => 'visa_mastercard',
            'debit_card' => 'napas',
            'e_wallet' => $this->faker->randomElement(['momo', 'zalopay', 'vnpay']),
            'payment_gateway' => $this->faker->randomElement(['vnpay', 'paypal', 'stripe']),
            default => 'internal',
        };
    }

    /**
     * Get processing fee percent for payment method type
     */
    private function getProcessingFeePercentForType(string $type): float
    {
        return match($type) {
            'cash', 'bank_transfer' => 0.00,
            'credit_card' => $this->faker->randomFloat(2, 2.0, 3.5),
            'debit_card' => $this->faker->randomFloat(2, 1.0, 2.0),
            'e_wallet' => $this->faker->randomFloat(2, 0.5, 1.5),
            'payment_gateway' => $this->faker->randomFloat(2, 1.5, 3.0),
            default => 0.00,
        };
    }

    /**
     * Get processing fee fixed for payment method type
     */
    private function getProcessingFeeFixedForType(string $type): float
    {
        return match($type) {
            'cash', 'bank_transfer', 'e_wallet' => 0.00,
            'credit_card' => $this->faker->randomFloat(2, 3000, 10000),
            'debit_card' => $this->faker->randomFloat(2, 2000, 5000),
            'payment_gateway' => $this->faker->randomFloat(2, 1000, 5000),
            default => 0.00,
        };
    }

    /**
     * Get minimum amount for payment method type
     */
    private function getMinAmountForType(string $type): float
    {
        return match($type) {
            'cash' => 1000.00,
            'bank_transfer' => 10000.00,
            'credit_card' => 50000.00,
            'debit_card' => 20000.00,
            'e_wallet' => 5000.00,
            'payment_gateway' => 5000.00,
            default => 1000.00,
        };
    }

    /**
     * Get maximum amount for payment method type
     */
    private function getMaxAmountForType(string $type): ?float
    {
        return match($type) {
            'cash' => 50000000.00,
            'bank_transfer' => null, // No limit
            'credit_card' => 100000000.00,
            'debit_card' => 50000000.00,
            'e_wallet' => 20000000.00,
            'payment_gateway' => 500000000.00,
            default => 10000000.00,
        };
    }

    /**
     * Get settlement days for payment method type
     */
    private function getSettlementDaysForType(string $type): int
    {
        return match($type) {
            'cash' => 0,
            'bank_transfer', 'debit_card', 'e_wallet' => 1,
            'credit_card' => 2,
            'payment_gateway' => $this->faker->numberBetween(1, 3),
            default => 1,
        };
    }

    /**
     * Get processing days for payment method type
     */
    private function getProcessingDaysForType(string $type): array
    {
        return match($type) {
            'cash', 'e_wallet' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'],
            default => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
        };
    }

    /**
     * Get icon for payment method type
     */
    private function getIconForType(string $type): string
    {
        return match($type) {
            'cash' => 'cash',
            'bank_transfer' => 'bank',
            'credit_card', 'debit_card' => 'credit-card',
            'e_wallet' => 'mobile-alt',
            'payment_gateway' => 'credit-card',
            default => 'money-bill',
        };
    }

    /**
     * Get requires PIN for payment method type
     */
    private function getRequiresPinForType(string $type): bool
    {
        return match($type) {
            'credit_card', 'debit_card' => true,
            default => false,
        };
    }

    /**
     * Get requires signature for payment method type
     */
    private function getRequiresSignatureForType(string $type): bool
    {
        return match($type) {
            'bank_transfer', 'credit_card' => true,
            default => false,
        };
    }
}