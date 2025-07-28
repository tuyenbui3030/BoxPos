<?php

namespace Packages\Payments\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Packages\Payments\Models\Payment;
use Packages\Payments\Models\PaymentMethod;
use Packages\SalesOrders\Models\Invoice;
use Packages\Customer\Models\Customer;
use Packages\Store\Models\Store;
use Packages\User\Models\User;
use Carbon\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Packages\Payments\Models\Payment>
 */
class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $amount = $this->faker->randomFloat(2, 10000, 10000000); // 10K - 10M VND
        $processingFeePercent = $this->faker->randomFloat(2, 0, 3);
        $processingFeeFixed = $this->faker->randomFloat(2, 0, 10000);
        $processingFee = ($amount * $processingFeePercent / 100) + $processingFeeFixed;
        $netAmount = $amount - $processingFee;

        $paymentDate = $this->faker->dateTimeBetween('-30 days', 'now');
        $processedAt = Carbon::parse($paymentDate)->addMinutes($this->faker->numberBetween(1, 60));

        return [
            'store_id' => Store::factory(),
            'payment_method_id' => PaymentMethod::factory(),
            'payment_number' => $this->generatePaymentNumber(),
            'payable_type' => Invoice::class,
            'payable_id' => Invoice::factory(),
            'payer_type' => Customer::class,
            'payer_id' => Customer::factory(),
            'amount' => $amount,
            'currency' => 'VND',
            'exchange_rate' => 1.0,
            'processing_fee' => $processingFee,
            'net_amount' => $netAmount,
            'status' => $this->faker->randomElement(['pending', 'completed', 'failed', 'cancelled', 'refunded']),
            'verification_status' => $this->faker->randomElement(['pending', 'verified', 'failed', 'not_required']),
            'processed_at' => $processedAt,
            'verified_at' => $this->faker->boolean(80) ? $processedAt->addMinutes($this->faker->numberBetween(5, 30)) : null,
            'verified_by' => $this->faker->boolean(80) ? User::factory() : null,
            'payment_date' => $paymentDate,
            'due_date' => $this->faker->boolean(30) ? $this->faker->dateTimeBetween($paymentDate, '+30 days') : null,
            'reference_number' => $this->generateReferenceNumber(),
            'external_transaction_id' => $this->faker->boolean(60) ? $this->generateExternalTransactionId() : null,
            'gateway_response' => $this->faker->boolean(40) ? $this->generateGatewayResponse() : null,
            'failure_reason' => null,
            'notes' => $this->faker->boolean(70) ? $this->faker->sentence() : null,
            'metadata' => $this->generateMetadata(),
            'created_by' => User::factory(),
        ];
    }

    /**
     * Configure the model factory for completed payments.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'verification_status' => 'verified',
            'processed_at' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'verified_at' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'verified_by' => User::factory(),
        ]);
    }

    /**
     * Configure the model factory for pending payments.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'verification_status' => 'pending',
            'processed_at' => null,
            'verified_at' => null,
            'verified_by' => null,
        ]);
    }

    /**
     * Configure the model factory for failed payments.
     */
    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'failed',
            'verification_status' => 'failed',
            'processed_at' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'failure_reason' => $this->faker->randomElement([
                'Insufficient funds',
                'Card declined',
                'Network timeout',
                'Invalid card details',
                'Transaction limit exceeded'
            ]),
        ]);
    }

    /**
     * Configure the model factory for cash payments.
     */
    public function cash(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'verification_status' => 'not_required',
            'processing_fee' => 0.00,
            'net_amount' => $attributes['amount'] ?? $this->faker->randomFloat(2, 10000, 1000000),
            'reference_number' => null,
            'external_transaction_id' => null,
            'gateway_response' => null,
            'notes' => 'Thanh toán tiền mặt tại quầy',
        ]);
    }

    /**
     * Configure the model factory for bank transfer payments.
     */
    public function bankTransfer(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'verification_status' => 'verified',
            'processing_fee' => 0.00,
            'net_amount' => $attributes['amount'] ?? $this->faker->randomFloat(2, 100000, 10000000),
            'reference_number' => 'REF' . $this->faker->numerify('##########'),
            'notes' => 'Chuyển khoản ngân hàng',
        ]);
    }

    /**
     * Configure the model factory for credit card payments.
     */
    public function creditCard(): static
    {
        $amount = $this->faker->randomFloat(2, 50000, 5000000);
        $processingFee = $amount * 0.025 + 5000; // 2.5% + 5K VND
        
        return $this->state(fn (array $attributes) => [
            'amount' => $amount,
            'processing_fee' => $processingFee,
            'net_amount' => $amount - $processingFee,
            'status' => 'completed',
            'verification_status' => 'verified',
            'reference_number' => 'TXN' . $this->faker->numerify('############'),
            'external_transaction_id' => 'VISA_' . $this->faker->numerify('############'),
            'gateway_response' => [
                'response_code' => '00',
                'response_message' => 'Success',
                'authorization_code' => 'AUTH' . $this->faker->numerify('######'),
            ],
            'notes' => 'Thanh toán thẻ tín dụng',
        ]);
    }

    /**
     * Configure the model factory for e-wallet payments.
     */
    public function eWallet(): static
    {
        $amount = $this->faker->randomFloat(2, 10000, 2000000);
        $processingFee = $amount * 0.01; // 1%
        
        return $this->state(fn (array $attributes) => [
            'amount' => $amount,
            'processing_fee' => $processingFee,
            'net_amount' => $amount - $processingFee,
            'status' => 'completed',
            'verification_status' => 'verified',
            'reference_number' => 'MOMO' . $this->faker->numerify('##########'),
            'external_transaction_id' => 'MOMO_' . $this->faker->numerify('############'),
            'gateway_response' => [
                'response_code' => '0',
                'response_message' => 'Successful',
                'transaction_id' => $this->faker->numerify('############'),
            ],
            'notes' => 'Thanh toán ví điện tử',
        ]);
    }

    /**
     * Configure the model factory for large amounts.
     */
    public function largeAmount(): static
    {
        return $this->state(fn (array $attributes) => [
            'amount' => $this->faker->randomFloat(2, 5000000, 50000000), // 5M - 50M VND
        ]);
    }

    /**
     * Configure the model factory for small amounts.
     */
    public function smallAmount(): static
    {
        return $this->state(fn (array $attributes) => [
            'amount' => $this->faker->randomFloat(2, 10000, 500000), // 10K - 500K VND
        ]);
    }

    /**
     * Configure the model factory for recent payments.
     */
    public function recent(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_date' => $this->faker->dateTimeBetween('-7 days', 'now'),
            'processed_at' => $this->faker->dateTimeBetween('-7 days', 'now'),
        ]);
    }

    /**
     * Generate payment number
     */
    private function generatePaymentNumber(): string
    {
        return 'PAY' . $this->faker->numerify('########');
    }

    /**
     * Generate reference number
     */
    private function generateReferenceNumber(): string
    {
        return 'REF' . $this->faker->numerify('############');
    }

    /**
     * Generate external transaction ID
     */
    private function generateExternalTransactionId(): string
    {
        $providers = ['VISA', 'MOMO', 'ZALOPAY', 'VNPAY', 'BANK'];
        $provider = $this->faker->randomElement($providers);
        
        return $provider . '_' . $this->faker->numerify('############');
    }

    /**
     * Generate gateway response
     */
    private function generateGatewayResponse(): array
    {
        return [
            'response_code' => $this->faker->randomElement(['00', '0', 'SUCCESS']),
            'response_message' => $this->faker->randomElement(['Success', 'Successful', 'Transaction completed']),
            'transaction_id' => $this->faker->numerify('############'),
            'authorization_code' => 'AUTH' . $this->faker->numerify('######'),
            'gateway_fee' => $this->faker->randomFloat(2, 1000, 10000),
            'processed_at' => now()->toISOString(),
        ];
    }

    /**
     * Generate metadata
     */
    private function generateMetadata(): array
    {
        return [
            'created_via' => $this->faker->randomElement(['pos', 'website', 'mobile', 'api']),
            'ip_address' => $this->faker->ipv4(),
            'user_agent' => $this->faker->userAgent(),
            'device_type' => $this->faker->randomElement(['desktop', 'mobile', 'tablet']),
        ];
    }
}