<?php

namespace Packages\Customer\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Packages\User\Models\User;
use Packages\Customer\Models\Customer;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Packages\Customer\Models\Customer>
 */
class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $totalSales = $this->faker->randomFloat(2, 0, 50000);
        $returns = $this->faker->randomFloat(2, 0, $totalSales * 0.1);
        $debt = $this->faker->boolean(30) ? $this->faker->randomFloat(2, 0, 5000) : 0;
        
        return [
            'customer_code' => $this->faker->unique()->numerify('CUS######'),
            'customer_name' => $this->faker->name(),
            'phone_number' => $this->faker->phoneNumber(),
            'email' => $this->faker->email(),
            'address' => $this->faker->address(),
            'customer_type' => $this->faker->randomElement(['individual', 'company']),
            'gender' => $this->faker->randomElement(['male', 'female', 'other']),
            'birthday' => $this->faker->dateTimeBetween('-80 years', '-18 years'),
            'customer_group' => $this->faker->randomElement(['VIP', 'Regular', 'New', 'Wholesale', 'Retail']),
            'current_debt' => $debt,
            'total_sales' => $totalSales,
            'total_sales_minus_returns' => $totalSales - $returns,
            'last_transaction_at' => $this->faker->dateTimeBetween('-2 years', 'now'),
            'created_by' => User::factory(),
            'created_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'updated_at' => now(),
        ];
    }

    /**
     * Indicate that the customer has debt.
     */
    public function withDebt(): static
    {
        return $this->state(fn (array $attributes) => [
            'current_debt' => $this->faker->randomFloat(2, 100, 5000),
        ]);
    }

    /**
     * Indicate that the customer is a company.
     */
    public function company(): static
    {
        return $this->state(fn (array $attributes) => [
            'customer_type' => 'company',
            'gender' => null,
        ]);
    }

    /**
     * Indicate that the customer is VIP.
     */
    public function vip(): static
    {
        return $this->state(fn (array $attributes) => [
            'customer_group' => 'VIP',
            'total_sales' => $this->faker->randomFloat(2, 10000, 100000),
        ]);
    }
}
