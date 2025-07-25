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
        $this->command->info('🚀 Starting comprehensive database seeding...');

        $this->call([
            // 1. Core Foundation Data (Must be first)
            StoreSeeder::class,
            UserSeeder::class,
            UserStoreSeeder::class,

            // 2. Material Management System
            MaterialSeeder::class,
            MaterialInventorySeeder::class,
            MaterialPricingSeeder::class,
            MaterialPurchasingSeeder::class,

            // 3. Product & Service Management
            ProductSeeder::class,

            // 4. Customer Management
            CustomerSeeder::class,

            // 5. Employee Management
            EmployeeSeeder::class,
            EmployeeTimesheetSeeder::class,
            EmployeePayrollSeeder::class,

            // 6. Sales & Orders
            SalesSeeder::class,
            InvoiceSeeder::class,
            SalesReturnSeeder::class,

            // 7. Warehouse Management
            WarehouseSeeder::class,
            StockTakeSeeder::class,
            InventoryDisposalSeeder::class,

            // 8. Cash Management
            CashManagementSeeder::class,

            // 9. Payment System
            PaymentSeeder::class,

            // 10. Advanced Features
            PromotionSeeder::class,
            \Packages\Loyalty\Database\Seeders\LoyaltySeeder::class,
            \Packages\Reports\Database\Seeders\ReportSeeder::class,
            \Packages\Notifications\Database\Seeders\NotificationSeeder::class,

            // 11. Final Advanced Features
            AdvancedFeatureSeeder::class,
        ]);

        $this->displaySeedingResults();
    }

    private function displaySeedingResults(): void
    {
        $this->command->info('');
        $this->command->info('🎉 COMPREHENSIVE DATABASE SEEDING COMPLETED!');
        $this->command->info('');
        $this->command->info('📊 Sample data created for ALL modules:');
        $this->command->info('   ✅ Multi-tenant Stores & Users');
        $this->command->info('   ✅ Material Catalog & Inventory');
        $this->command->info('   ✅ Suppliers & Purchasing');
        $this->command->info('   ✅ Products & Services');
        $this->command->info('   ✅ Customer Management');
        $this->command->info('   ✅ Employee Management');
        $this->command->info('   ✅ Sales Orders & Invoicing');
        $this->command->info('   ✅ Warehouse & Stock Management');
        $this->command->info('   ✅ Cash Management');
        $this->command->info('   ✅ Payment Processing');
        $this->command->info('   ✅ Promotions & Loyalty');
        $this->command->info('   ✅ Reports & Analytics');
        $this->command->info('   ✅ Notifications');
        $this->command->info('');
        $this->command->info('🔑 Login credentials:');
        $this->command->info('   Admin: admin@demo001.boxpos.vn / password');
        $this->command->info('   Manager: manager@demo001.boxpos.vn / password');
        $this->command->info('   Cashier: cashier1@demo001.boxpos.vn / password');
        $this->command->info('   Staff: staff1@demo001.boxpos.vn / password');
        $this->command->info('');
        $this->command->info('🏪 Demo stores available:');
        $this->command->info('   - Demo Store 001 (demo001.boxpos.vn)');
        $this->command->info('   - Demo Store 002 (demo002.boxpos.vn)');
        $this->command->info('   - Demo Store 003 (demo003.boxpos.vn)');
    }
}
