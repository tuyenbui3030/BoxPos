<?php

namespace Packages\Customer\Database\Seeders;

use Database\Seeders\BasePackageSeeder;
use Packages\Customer\Models\Customer;
use Packages\User\Models\User;
use Packages\Store\Models\Store;
use Faker\Factory as Faker;

class CustomerSeeder extends BasePackageSeeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->ensureSeedingAllowed();
        
        $this->executeWithTransaction(function () {
            $this->seedCustomers();
        });
    }

    /**
     * Seed customers for all stores
     */
    private function seedCustomers(): void
    {
        $this->logSeedingProgress('customer_seeding_started');

        $stores = $this->getStores();
        $customersPerStore = $this->getConfigValue('customers_per_store', 50);

        foreach ($stores as $store) {
            $this->seedCustomersForStore($store, $customersPerStore);
        }

        $this->logSeedingProgress('customer_seeding_completed', [
            'total_stores' => $stores->count(),
            'customers_per_store' => $customersPerStore
        ]);
    }

    /**
     * Seed customers for a specific store
     */
    private function seedCustomersForStore(Store $store, int $count): void
    {
        $this->logSeedingProgress('seeding_customers_for_store', [
            'store_id' => $store->id,
            'store_name' => $store->name,
            'customer_count' => $count
        ]);

        $faker = Faker::create('vi_VN'); // Vietnamese locale
        $users = User::all();
        
        // Get Vietnamese data for realistic seeding
        $vietnameseNames = $this->getVietnameseNames();
        $vietnameseAddresses = $this->getVietnameseAddresses();
        $vietnameseCompanyNames = $this->getVietnameseCompanyNames();
        
        // Customer groups with Vietnamese context
        $customerGroups = ['VIP', 'Thường', 'Mới', 'Sỉ', 'Lẻ', 'Đại lý', 'Nhà thầu'];
        $customerTypes = ['individual', 'company'];

        // Create diverse customer mix
        $individualCount = intval($count * 0.7); // 70% individuals
        $companyCount = $count - $individualCount; // 30% companies

        // Create individual customers
        for ($i = 0; $i < $individualCount; $i++) {
            $this->createIndividualCustomer($store, $faker, $users, $vietnameseNames, $vietnameseAddresses, $customerGroups);
        }

        // Create company customers
        for ($i = 0; $i < $companyCount; $i++) {
            $this->createCompanyCustomer($store, $faker, $users, $vietnameseCompanyNames, $vietnameseAddresses, $customerGroups);
        }
    }

    /**
     * Create an individual customer
     */
    private function createIndividualCustomer(Store $store, $faker, $users, array $names, array $addresses, array $groups): void
    {
        $totalSales = $faker->randomFloat(2, 0, 50000);
        $returns = $faker->randomFloat(2, 0, $totalSales * 0.1);
        $debt = $faker->boolean(30) ? $faker->randomFloat(2, 0, 5000) : 0;

        Customer::create([
            'store_id' => $store->id,
            'customer_code' => Customer::generateCustomerCode(),
            'customer_name' => $faker->randomElement($names),
            'phone_number' => $this->generateVietnamesePhoneNumber($faker),
            'email' => $faker->unique()->safeEmail(),
            'address' => $faker->randomElement($addresses),
            'customer_type' => 'individual',
            'gender' => $faker->randomElement(['male', 'female']),
            'birthday' => $faker->dateTimeBetween('-80 years', '-18 years'),
            'customer_group' => $faker->randomElement($groups),
            'current_debt' => $debt,
            'total_sales' => $totalSales,
            'total_sales_minus_returns' => $totalSales - $returns,
            'last_transaction_at' => $faker->dateTimeBetween('-2 years', 'now'),
            'created_by' => $users->isNotEmpty() ? $users->random()->id : null,
            'created_at' => $faker->dateTimeBetween('-1 year', 'now'),
            'updated_at' => now(),
        ]);
    }

    /**
     * Create a company customer
     */
    private function createCompanyCustomer(Store $store, $faker, $users, array $companyNames, array $addresses, array $groups): void
    {
        $totalSales = $faker->randomFloat(2, 10000, 200000); // Companies typically have higher sales
        $returns = $faker->randomFloat(2, 0, $totalSales * 0.05); // Lower return rate for companies
        $debt = $faker->boolean(40) ? $faker->randomFloat(2, 1000, 20000) : 0; // Higher debt amounts for companies

        Customer::create([
            'store_id' => $store->id,
            'customer_code' => Customer::generateCustomerCode(),
            'customer_name' => $faker->randomElement($companyNames),
            'phone_number' => $this->generateVietnamesePhoneNumber($faker),
            'email' => $faker->unique()->companyEmail(),
            'address' => $faker->randomElement($addresses),
            'customer_type' => 'company',
            'gender' => null, // Companies don't have gender
            'birthday' => null, // Companies don't have birthdays
            'customer_group' => $faker->randomElement(['VIP', 'Sỉ', 'Đại lý', 'Nhà thầu']), // Company-appropriate groups
            'current_debt' => $debt,
            'total_sales' => $totalSales,
            'total_sales_minus_returns' => $totalSales - $returns,
            'last_transaction_at' => $faker->dateTimeBetween('-2 years', 'now'),
            'created_by' => $users->isNotEmpty() ? $users->random()->id : null,
            'created_at' => $faker->dateTimeBetween('-1 year', 'now'),
            'updated_at' => now(),
        ]);
    }

    /**
     * Generate realistic Vietnamese phone numbers
     */
    private function generateVietnamesePhoneNumber($faker): string
    {
        $prefixes = ['090', '091', '094', '083', '084', '085', '081', '082', '032', '033', '034', '035', '036', '037', '038', '039'];
        $prefix = $faker->randomElement($prefixes);
        $suffix = $faker->numerify('#######');
        
        return $prefix . $suffix;
    }
}
