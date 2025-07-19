<?php

namespace Database\Seeders;

use Packages\User\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            // Core seeders
            \Packages\Store\Database\Seeders\StoreSeeder::class,
            \Packages\User\Database\Seeders\UserSeeder::class,
            \Packages\User\Database\Seeders\UserStoreSeeder::class, // Assign users to stores
            \Packages\Customer\Database\Seeders\CustomerSeeder::class,

            // Material Management seeders (in dependency order)
            \Packages\MaterialCatalog\Database\Seeders\MaterialUnitsSeeder::class,
            \Packages\MaterialCatalog\Database\Seeders\MaterialCategoriesSeeder::class,
            \Packages\MaterialSuppliers\Database\Seeders\MaterialSuppliersSeeder::class,
            \Packages\MaterialCatalog\Database\Seeders\BuildingMaterialsSeeder::class,
            \Packages\MaterialCatalog\Database\Seeders\MaterialSpecificationsSeeder::class,
            \Packages\MaterialSuppliers\Database\Seeders\SupplierContactsSeeder::class,
            \Packages\MaterialInventory\Database\Seeders\MaterialInventorySeeder::class,
            \Packages\MaterialPurchasing\Database\Seeders\MaterialPurchaseOrdersSeeder::class,
            \Packages\MaterialPricing\Database\Seeders\MaterialPricingSeeder::class,
        ]);
    }
}
