<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Customer;
use App\Models\User;
use Faker\Factory as Faker;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        $faker = Faker::create();
        $users = User::all();
        
        // Create sample customer groups
        $customerGroups = ['VIP', 'Regular', 'New', 'Wholesale', 'Retail'];
        
        for ($i = 1; $i <= 100; $i++) {
            $totalSales = $faker->randomFloat(2, 0, 50000);
            $returns = $faker->randomFloat(2, 0, $totalSales * 0.1); // Max 10% returns
            $debt = $faker->boolean(30) ? $faker->randomFloat(2, 0, 5000) : 0; // 30% chance of having debt
            
            Customer::create([
                'customer_code' => 'CUS' . str_pad($i, 6, '0', STR_PAD_LEFT),
                'customer_name' => $faker->name(),
                'phone_number' => $faker->phoneNumber(),
                'email' => $faker->email(),
                'address' => $faker->address(),
                'customer_type' => $faker->randomElement(['individual', 'company']),
                'gender' => $faker->randomElement(['male', 'female', 'other']),
                'birthday' => $faker->dateTimeBetween('-80 years', '-18 years')->format('Y-m-d'),
                'customer_group' => $faker->randomElement($customerGroups),
                'current_debt' => $debt,
                'total_sales' => $totalSales,
                'total_sales_minus_returns' => $totalSales - $returns,
                'last_transaction_at' => $faker->dateTimeBetween('-2 years', 'now'),
                'created_by' => $users->isNotEmpty() ? $users->random()->id : null,
                'created_at' => $faker->dateTimeBetween('-1 year', 'now'),
                'updated_at' => now(),
            ]);
        }
    }
}
